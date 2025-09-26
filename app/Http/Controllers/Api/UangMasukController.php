<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UangMasuk;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Keuangan;

class UangMasukController extends Controller
{
    public function index()
    {
        $data = UangMasuk::with('keuangan')->get();
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function show($id)
    {
        $masuk = UangMasuk::with('keuangan')->find($id);
        if (!$masuk) return response()->json([
            'success' => false, 
            'message' => 'Data tidak ditemukan'
        ], 404);

        if ($masuk->bukti_pemasukan) {
            $masuk->bukti_pemasukan = asset('storage/' . $masuk->bukti_pemasukan);
        }
        return response()->json(['success' => true, 'data' => $masuk]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah_uang_masuk' => 'required|integer',
            'asal_pemasukan' => 'required|string|max:255',
            'tanggal_pemasukan' => 'required|date',
            'bukti_pemasukan' => 'required|image|mimes:jpeg,png,jpg|max:5000', // wajib foto
            'keuangan_id' => 'required|exists:keuangan,id',
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);

        $payload = $validator->validated();

        if ($request->hasFile('bukti_pemasukan')) {
            $file = $request->file('bukti_pemasukan');
        }

        try {
            $result = DB::transaction(function () use ($payload, $file) {
                $masuk = UangMasuk::create($payload);

                //tambah saldo pada keuangan saat record uang masuk dibuat
                $keuangan = Keuangan::where('id', $payload['keuangan_id'])->lockForUpdate()->first();
                $keuangan->increment('saldo', $payload['jumlah_uang_masuk']);

                if($file){
                    $path = $file->store('bukti_pemasukan', 'public');
                    $masuk->update(['bukti_pemasukan' => $path]);
                }

                return $masuk;
            });

            return response()->json(['success' => true, 'message' => 'Pemasukan berhasil dibuat', 'data' => $result], 201);

        } catch (\Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'jumlah_uang_masuk' => 'sometimes|integer',
            'asal_pemasukan' => 'sometimes|string|max:255',
            'tanggal_pemasukan' => 'sometimes|date',
            'bukti_pemasukan' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048', // foto opsional
            'keuangan_id' => 'sometimes|exists:keuangan,id',
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);

        $payload = $validator->validated();

        $masuk = UangMasuk::find($id);
        if (!$masuk) return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);

        $file = $request->file('bukti_pemasukan') ?? null;

        try {
            $result = DB::transaction(function () use($payload, $masuk, $file) {
                $originalKeuanganId = $masuk->keuangan_id;
                $originalJumlah = (float) $masuk->jumlah_uang_masuk;
                $oldFile = $masuk->bukti_pemasukan;

                $masuk->update($payload);

                $newKeuanganId = $payload['keuangan_id'];
                $newJumlah = (float) $payload['jumlah_uang_masuk'];

                //Jika keuangan diganti, kurangi saldo di keuangan lama
                if ($originalKeuanganId && $originalKeuanganId != $newKeuanganId) {
                    $keuanganLama = Keuangan::where('id', $originalKeuanganId)->lockForUpdate()->first();

                    if ($keuanganLama->saldo < $originalJumlah) {
                        //throw e jika saldo tidak cukup
                        throw new \Exception('Saldo tidak mencukupi');
                    }
                    $keuanganLama->decrement('saldo', $originalJumlah);
                }

                //tambah saldo di keuangan baru
                if ($newKeuanganId) {
                    if ($originalKeuanganId && $originalKeuanganId != $newKeuanganId) {
                        Keuangan::where('id', $newKeuanganId)
                            ->lockForUpdate()
                            ->increment('saldo', $newJumlah); 

                    //jika keuangan masih sama, tambah saldo jika jumlah baru lebih besar dari jumlah lama, begitu pula sebaliknya
                    } else {
                        $delta = $newJumlah - $originalJumlah;
                        if ($delta > 0) {
                            Keuangan::where('id', $newKeuanganId)
                                ->lockForUpdate()
                                ->increment('saldo', $delta);
                        } elseif ($delta < 0) {
                            $keuangan = Keuangan::where('id', $newKeuanganId)->lockForUpdate()->first();   
                            if ($keuangan->saldo < abs($delta)) {
                                //throw e jika saldo tidak cukup
                                throw new \Exception('Saldo tidak mencukupi');
                            }
                            $keuangan->decrement('saldo', abs($delta));
                        }
                    }
                }

                //jika file diganti, hapus file lama. jika file tidak diganti, file lama dibiarkan
                if($file){
                    if (!empty($oldFile)) {
                        Storage::disk('public')->delete($oldFile);
                    }   

                    $path = $file->store('bukti_pemasukan', 'public');
                    $masuk->update(['bukti_pemasukan' => $path]);
                }

                return $masuk;
            });
            
            return response()->json(['success' => true, 'message' => 'Pemasukan berhasil diperbarui', 'data' => $result]);

        } catch (\Throwable $e) {
            if (strpos($e->getMessage(), 'Saldo tidak mencukupi') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo tidak mencukupi untuk melakukan operasi ini'
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }        
    }

    public function destroy($id)
    {
        $masuk = UangMasuk::find($id);
        if (!$masuk) return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);

        try {
            DB::transaction(function () use($masuk) {
                $keuanganId = $masuk->keuangan_id;
                $jumlah = (float) $masuk->jumlah_uang_masuk;

                $masuk->delete();

                //kurangi saldo pada keuangan saat record uang masuk dihapus
                $keuangan = Keuangan::where('id', $keuanganId)->lockForUpdate()->first();
                if ($keuangan->saldo < $jumlah) {
                    //throw e jika saldo tidak cukup
                    throw new \Exception('Saldo tidak mencukupi');
                }
                if($keuanganId){
                    $keuangan->decrement('saldo', $jumlah);
                }

                if (!empty($masuk->bukti_pemasukan)) {
                    Storage::disk('public')->delete($masuk->bukti_pemasukan);
                }
            });   
            
            return response()->json(['success' => true, 'message' => 'Pemasukan berhasil dihapus'], 200);

        } catch (\Throwable $e) {
            if (strpos($e->getMessage(), 'Saldo tidak mencukupi') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo tidak mencukupi untuk melakukan operasi ini'
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }  
    }
}
