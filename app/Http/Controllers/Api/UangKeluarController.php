<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UangKeluar;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Keuangan;

class UangKeluarController extends Controller
{
    public function index()
    {
        $data = UangKeluar::with('keuangan')->get();
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function show($id)
    {
        $keluar = UangKeluar::with('keuangan')->find($id);
        if (!$keluar) return response()->json([
            'success' => false, 
            'message' => 'Data pengeluaran tidak ditemukan'
        ], 404);

        if ($keluar->bukti_pengeluaran) {
            $keluar->bukti_pengeluaran = asset('storage/' . $keluar->bukti_pengeluaran);
        }
        return response()->json(['success' => true, 'data' => $keluar]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah_pengeluaran' => 'required|integer',
            'alasan_pengeluaran' => 'required|string|max:255',
            'tanggal_pengeluaran' => 'required|date',
            'bukti_pengeluaran' => 'required|image|mimes:jpeg,png,jpg|max:5000',
            'keuangan_id' => 'required|exists:keuangan,id',
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);

        $payload = $validator->validated();

        if ($request->hasFile('bukti_pengeluaran')) {
            $file = $request->file('bukti_pengeluaran');
        }

        try {
            $result = DB::transaction(function () use($payload, $file) {
                $keluar = UangKeluar::create($payload);

                $keuanganId = $payload['keuangan_id'];
                $jumlah = (float) $payload['jumlah_pengeluaran'];

                //kurangi saldo pada keuangan saat record uang keluar dibuat
                $keuangan = Keuangan::where('id', $keuanganId)->lockForUpdate()->first();
                if ($keuangan->saldo < $jumlah) {
                    //throw e jika saldo tidak cukup
                    throw new \Exception('Saldo tidak mencukupi');
                }
                $keuangan->decrement('saldo', $jumlah);  

                if($file){
                    $path = $file->store('bukti_pengeluaran', 'public');
                    $keluar->update(['bukti_pengeluaran' => $path]);
                }

                return $keluar;
            });

            return response()->json(['success' => true, 'message' => 'Pengeluaran berhasil dibuat', 'data' => $result], 201); 

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

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'jumlah_pengeluaran' => 'sometimes|integer',
            'alasan_pengeluaran' => 'sometimes|string|max:255',
            'tanggal_pengeluaran' => 'sometimes|date',
            'bukti_pengeluaran' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048', // foto opsional
            'keuangan_id' => 'sometimes|exists:keuangan,id',
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);

        $payload = $validator->validated();

        $keluar = UangKeluar::find($id);
        if (!$keluar) return response()->json(['success' => false, 'message' => 'Data pengeluaran tidak ditemukan'], 404);

        $file = $request->file('bukti_pengeluaran') ?? null;

        try {
            $result = DB::transaction(function () use($payload, $keluar, $file) {
                $originalKeuanganId = $keluar->keuangan_id;
                $originalJumlah = (float) $keluar->jumlah_pengeluaran;
                $oldFile = $keluar->bukti_pengeluaran;

                $keluar->update($payload);

                $newKeuanganId = $payload['keuangan_id'];
                $newJumlah = (float) $payload['jumlah_pengeluaran'];


                //jika keuangan diganti, tambah saldo di keuangan lama
                if ($originalKeuanganId && $originalKeuanganId != $newKeuanganId) {
                    Keuangan::where('id', $originalKeuanganId)
                        ->lockForUpdate()
                        ->increment('saldo', $originalJumlah);
                }

                //kurangi saldo di keuangan baru
                if ($newKeuanganId) {
                    if ($originalKeuanganId && $originalKeuanganId != $newKeuanganId) {
                        $keuanganBaru = Keuangan::where('id', $newKeuanganId)->lockForUpdate()->first();
                        if ($keuanganBaru->saldo < $newJumlah) {
                            //throw e jika saldo tidak cukup
                            throw new \Exception('Saldo tidak mencukupi');
                        }
                        $keuanganBaru->decrement('saldo', $newJumlah);

                    //jika keuangan masih sama, kurangi saldo jika jumlah baru lebih besar dari jumlah lama, begitu pula sebaliknya
                    } else {
                        $delta = $newJumlah - $originalJumlah;
                        if ($delta > 0) {
                            $keuangan = Keuangan::where('id', $newKeuanganId)->lockForUpdate()->first();
                            if ($keuangan->saldo < $delta) {
                                //throw e jika saldo tidak cukup
                                throw new \Exception('Saldo tidak mencukupi');
                            }
                            $keuangan->decrement('saldo', $delta);

                        } elseif ($delta < 0) {
                            Keuangan::where('id', $newKeuanganId)
                                ->lockForUpdate()
                                ->increment('saldo', abs($delta));
                        }
                    }
                }

                //jika file diganti, hapus file lama. jika file tidak diganti, file lama dibiarkan
                if($file){
                    if (!empty($oldFile)) {
                        Storage::disk('public')->delete($oldFile);
                    }   
                    $path = $file->store('bukti_pengeluaran', 'public');
                    $keluar->update(['bukti_pengeluaran' => $path]);
                }

                return $keluar;
            });

            return response()->json(['success' => true, 'message' => 'Pengeluaran berhasil diperbarui', 'data' => $result]);

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
        $keluar = UangKeluar::find($id);
        if (!$keluar) return response()->json(['success' => false, 'message' => 'Data pengeluaran tidak ditemukan'], 404);

        try {
            DB::transaction(function () use($keluar) {
                $keuanganId = $keluar->keuangan_id;
                $jumlah = (float) $keluar->jumlah_pengeluaran;

                $keluar->delete();

                //tambah saldo pada keuangan saat record uang keluar dihapus
                $keuangan = Keuangan::where('id', $keuanganId)->lockForUpdate()->first();
                if ($keuanganId) {
                    $keuangan->increment('saldo', $jumlah);
                }

                if (!empty($keluar->bukti_pengeluaran)) {
                    Storage::disk('public')->delete($keluar->bukti_pengeluaran);
                }
            });

            return response()->json(['success' => true, 'message' => 'Pengeluaran berhasil dihapus'], 200);

        } catch (\Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }
}
