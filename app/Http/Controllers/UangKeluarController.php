<?php

namespace App\Http\Controllers;

use App\Models\UangKeluar;
use App\Models\Keuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UangKeluarController extends Controller
{
    public function Store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah_pengeluaran' => 'required|numeric|min:1',
            'alasan_pengeluaran'    => 'required|string|max:255',
            'tanggal_pengeluaran' => 'required|date',
            'bukti_pengeluaran' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'keuangan_id'       => 'required|exists:keuangan,id',            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()
            ], 422);
        }

        $payload = $validator->validated();

        if ($request->hasFile('bukti_pengeluaran')) {
            $file = $request->file('bukti_pengeluaran');
        }

        try {

            $result = DB::transaction(function () use ($payload, $file) {

                $uangKeluar = UangKeluar::create($payload);

                $keuanganId = $payload['keuangan_id'];
                $jumlah = (float) $payload['jumlah_pengeluaran'];

                $keuangan = Keuangan::where('id', $keuanganId)->lockForUpdate()->first();   

                if (!$keuangan) {
                    throw new \Exception('Keuangan tidak ditemukan');
                }
                if ($keuangan->saldo < $jumlah) {
                    throw new \Exception('Saldo tidak mencukupi');
                }

                $keuangan->decrement('saldo', $jumlah);  
                
                if ($file) {
                    $path = $file->store('bukti_pengeluaran', 'public');
                    $uangKeluar->update(['bukti_pengeluaran' => $path]);
                }

                return $uangKeluar;
            });

            return response()->json([
                'success' => true,
                'message' => 'Record uang keluar berhasil disimpan',
                'data' => $result
            ], 201);

        } catch (\Throwable $e) {

            Log::error('UangKeluar Store error: '.$e->getMessage(), [
                'payload' => $payload,
                'exception' => $e
            ]);

            if (strpos($e->getMessage(), 'Saldo tidak mencukupi') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo tidak mencukupi untuk melakukan operasi ini'
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan record uang keluar.'
            ], 500);
        }
    }

    public function Index()
    {
        try {

            $uangKeluars = UangKeluar::all();

            return response()->json([
                'success' => true,
                'data' => $uangKeluars
            ], 200);

        } catch (\Throwable $e) {

            Log::error('UangKeluar Index error: '.$e->getMessage(), [
                'exception' => $e
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat mengambil record uang keluar'
            ], 500);
        }        
    }

    public function Show($id)
    {
        try {

            $uangKeluar = UangKeluar::find($id);

            if(!$uangKeluar){
                return response()->json([
                    'success' => false,
                    'message' => 'Record uang keluar tidak ditemukan'
                ], 404);
            }

            if ($uangKeluar->bukti_pengeluaran) {
                $uangKeluar->bukti_pengeluaran = asset('storage/' . $uangKeluar->bukti_pengeluaran);
            }

            return response()->json([
                'success' => true,
                'data' => $uangKeluar
            ], 200);

        } catch (\Throwable $e) {

            Log::error('UangKeluar Show error: '.$e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat mengambil record uang keluar'
            ], 500);
        }
    }

    public function Update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'jumlah_pengeluaran' => 'required|numeric|min:1',
            'alasan_pengeluaran'    => 'required|string|max:255',
            'tanggal_pengeluaran' => 'required|date',
            'bukti_pengeluaran' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'keuangan_id'       => 'required|exists:keuangan,id',  
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()
            ], 422);
        }

        $payload = $validator->validated();

        try {

            $uangKeluar = UangKeluar::find($id);

            if (!$uangKeluar) {
                return response()->json([
                    'success' => false,
                    'message' => 'Record uang keluar tidak ditemukan'
                ], 404);
            }

            $file = $request->file('bukti_pengeluaran') ?? null;

            $result = DB::transaction(function () use ($uangKeluar, $payload, $file) {
                $originalKeuanganId = $uangKeluar->keuangan_id;
                $originalJumlah = (float) $uangKeluar->jumlah_pengeluaran;

                $oldFile = $uangKeluar->bukti_pengeluaran;
                $uangKeluar->update($payload);

                $newKeuanganId = $payload['keuangan_id'];
                $newJumlah = (float) $payload['jumlah_pengeluaran'];

                if ($originalKeuanganId && $originalKeuanganId != $newKeuanganId) {

                    Keuangan::where('id', $originalKeuanganId)
                        ->lockForUpdate()
                        ->increment('saldo', $originalJumlah);
                }

                if ($newKeuanganId) {

                    if ($originalKeuanganId && $originalKeuanganId != $newKeuanganId) {

                        $keuanganBaru = Keuangan::where('id', $newKeuanganId)->lockForUpdate()->first();

                        if (!$keuanganBaru) {
                            throw new \Exception('Keuangan tidak ditemukan');
                        }
                        if ($keuanganBaru->saldo < $newJumlah) {
                            throw new \Exception('Saldo tidak mencukupi');
                        }

                        $keuanganBaru->decrement('saldo', $newJumlah);

                    } else {

                        $delta = $newJumlah - $originalJumlah;

                        if ($delta > 0) {

                            $keuangan = Keuangan::where('id', $newKeuanganId)->lockForUpdate()->first();

                            if (!$keuangan) {
                            throw new \Exception('Keuangan tidak ditemukan');
                            }
                            if ($keuangan->saldo < $delta) {
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

                if($file){
                    if (!empty($oldFile)) {
                        Storage::disk('public')->delete($oldFile);
                    }   

                    $path = $file->store('bukti_pengeluaran', 'public');
                    $uangKeluar->update(['bukti_pengeluaran' => $path]);
                }

                return $uangKeluar;
            });            

            return response()->json([
                'success' => true,
                'message' => 'Uang Keluar berhasil diperbarui',
                'data' => $result
            ], 200);

        } catch (\Throwable $e) {

            Log::error('UangKeluar Update error: '.$e->getMessage(), [
                'payload' => $payload,
                'exception' => $e
            ]);

            if (strpos($e->getMessage(), 'Saldo tidak mencukupi') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo tidak mencukupi untuk melakukan operasi ini'
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat memperbarui record uang keluar'
            ], 500);
        }        
    }

    public function Destroy($id)
    {
        try {
            $uangKeluar = UangKeluar::find($id);

            if(!$uangKeluar){
                return response()->json([
                    'success' => false,
                    'message' => 'Record uang keluar tidak ditemukan'
                ], 404);
            }

            DB::transaction(function () use ($uangKeluar) {
                $keuanganId = $uangKeluar->keuangan_id;
                $jumlah = (float) $uangKeluar->jumlah_pengeluaran;

                if (!empty($uangKeluar->bukti_pengeluaran)) {
                    Storage::disk('public')->delete($uangKeluar->bukti_pengeluaran);
                }

                $uangKeluar->delete();

                if ($keuanganId) {
                    $keuangan = Keuangan::where('id', $keuanganId)->lockForUpdate()->first();
                    if (!$keuangan) {
                        throw new \Exception('Keuangan tidak ditemukan');
                    }
                    $keuangan->increment('saldo', $jumlah);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Record uang keluar berhasil dihapus'
            ], 200);

        } catch (\Throwable $e) {

            Log::error('UangKeluar Destroy error: '.$e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat menghapus record uang keluar'
            ], 500);
        }               
    }   
}
