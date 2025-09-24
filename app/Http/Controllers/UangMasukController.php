<?php

namespace App\Http\Controllers;

use App\Models\UangMasuk;
use App\Models\Keuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class UangMasukController extends Controller
{
    public function Store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah_uang_masuk' => 'required|numeric|min:1',
            'asal_pemasukan'    => 'required|string|max:255',
            'tanggal_pemasukan' => 'required|date',
            'bukti_pemasukan'   => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'keuangan_id'       => 'required|exists:keuangan,id',            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()
            ], 422);
        }

        $payload = $validator->validated();

        if ($request->hasFile('bukti_pemasukan')) {
            $file = $request->file('bukti_pemasukan');
        }

        try {

            $result = DB::transaction(function () use ($payload, $file) {

                $uangMasuk = UangMasuk::create($payload);

                $keuangan = Keuangan::where('id', $payload['keuangan_id'])->lockForUpdate()->first();

                if (!$keuangan) {
                    throw new \Exception('Keuangan tidak ditemukan');
                }

                $keuangan->increment('saldo', $payload['jumlah_uang_masuk']);

                if($file){
                    $path = $file->store('bukti_pemasukan', 'public'); 
                    $uangMasuk->update(['bukti_pemasukan' => $path]); 
                }

                return $uangMasuk;
            });

            return response()->json([
                'success' => true,
                'message' => 'Record uang masuk berhasil disimpan',
                'data' => $result
            ], 201);

        } catch (\Throwable $e) {

            Log::error('UangMasuk Store error: '.$e->getMessage(), [
                'payload' => $payload,
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan record uang masuk.'
            ], 500);
        }
    }

    public function Index()
    {
        try {

            $uangMasuks = UangMasuk::all();

            return response()->json([
                'success' => true,
                'data' => $uangMasuks
            ], 200);

        } catch (\Throwable $e) {

            Log::error('UangMasuk Index error: '.$e->getMessage(), [
                'exception' => $e
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat mengambil record uang masuk'
            ], 500);
        }        
    }

    public function Show($id)
    {
        try {

            $uangMasuk = UangMasuk::find($id);

            if(!$uangMasuk){
                return response()->json([
                    'success' => false,
                    'message' => 'Record uang masuk tidak ditemukan'
                ], 404);
            }

            if ($uangMasuk->bukti_pemasukan) {
                $uangMasuk->bukti_pemasukan = asset('storage/' . $uangMasuk->bukti_pemasukan);
            }

            return response()->json([
                'success' => true,
                'data' => $uangMasuk
            ], 200);

        } catch (\Throwable $e) {

            Log::error('UangMasuk Show error: '.$e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat mengambil record uang masuk'
            ], 500);
        }
    }

    public function Update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'jumlah_uang_masuk' => 'required|numeric|min:1',
            'asal_pemasukan'    => 'required|string|max:255',
            'tanggal_pemasukan' => 'required|date',
            'bukti_pemasukan'   => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
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

            $uangMasuk = UangMasuk::find($id);

            if (!$uangMasuk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Record uang masuk tidak ditemukan'
                ], 404);
            }

            $file = $request->file('bukti_pemasukan') ?? null;

            $result = DB::transaction(function () use ($uangMasuk, $payload, $file) {

                $originalKeuanganId = $uangMasuk->keuangan_id;
                $originalJumlah = (float) $uangMasuk->jumlah_uang_masuk;

                $oldFile = $uangMasuk->bukti_pemasukan;
                $uangMasuk->update($payload);

                $newKeuanganId = $payload['keuangan_id'];
                $newJumlah = (float) $payload['jumlah_uang_masuk'];

                if ($originalKeuanganId && $originalKeuanganId != $newKeuanganId) {

                    $keuanganLama = Keuangan::where('id', $originalKeuanganId)->lockForUpdate()->first();

                    if (!$keuanganLama) {
                        throw new \Exception('Keuangan tidak ditemukan');
                    }
                    if ($keuanganLama->saldo < $originalJumlah) {
                        throw new \Exception('Saldo tidak mencukupi');
                    }

                    $keuanganLama->decrement('saldo', $originalJumlah);
                }

                if ($newKeuanganId) {

                    if ($originalKeuanganId && $originalKeuanganId != $newKeuanganId) {

                        Keuangan::where('id', $newKeuanganId)
                            ->lockForUpdate()
                            ->increment('saldo', $newJumlah); 

                    } else {

                        $delta = $newJumlah - $originalJumlah;

                        if ($delta > 0) {
                            Keuangan::where('id', $newKeuanganId)
                                ->lockForUpdate()
                                ->increment('saldo', $delta);

                        } elseif ($delta < 0) {
                            $keuangan = Keuangan::where('id', $newKeuanganId)->lockForUpdate()->first();   

                            if (!$keuangan) {
                            throw new \Exception('Keuangan tidak ditemukan');
                            }
                            if ($keuangan->saldo < abs($delta)) {
                                throw new \Exception('Saldo tidak mencukupi');
                            }

                            $keuangan->decrement('saldo', abs($delta));
                        }
                    }
                }

                if($file){
                    if (!empty($oldFile)) {
                        Storage::disk('public')->delete($oldFile);
                    }   

                    $path = $file->store('bukti_pemasukan', 'public');
                    $uangMasuk->update(['bukti_pemasukan' => $path]);
                }

                return $uangMasuk;
            });            

            return response()->json([
                'success' => true,
                'message' => 'Uang Masuk berhasil diperbarui',
                'data' => $result
            ], 200);

        } catch (\Throwable $e) {

            Log::error('UangMasuk Update error: '.$e->getMessage(), [
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
                'message' => 'Terjadi kesalahan server saat memperbarui record uang masuk'
            ], 500);
        }        
    }

    public function Destroy($id)
    {
        try {
            $uangMasuk = UangMasuk::find($id);

            if(!$uangMasuk){
                return response()->json([
                    'success' => false,
                    'message' => 'Record uang masuk tidak ditemukan'
                ], 404);
            }

            DB::transaction(function () use ($uangMasuk) {
                $keuanganId = $uangMasuk->keuangan_id;
                $jumlah = (float) $uangMasuk->jumlah_uang_masuk;

                if (!empty($uangMasuk->bukti_pemasukan)) {
                    Storage::disk('public')->delete($uangMasuk->bukti_pemasukan);
                }

                $uangMasuk->delete();

                $keuangan = Keuangan::where('id', $keuanganId)->lockForUpdate()->first();

                if (!$keuangan) {
                    throw new \Exception('Keuangan tidak ditemukan');
                }
                if ($keuangan->saldo < $jumlah) {
                    throw new \Exception('Saldo tidak mencukupi');
                }
                if ($keuanganId) {
                    $keuangan->decrement('saldo', $jumlah);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Record uang masuk berhasil dihapus'
            ], 200);

        } catch (\Throwable $e) {

            Log::error('UangMasuk Destroy error: '.$e->getMessage(), [
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
                'message' => 'Terjadi kesalahan server saat menghapus record uang masuk'
            ], 500);
        }               
    }            
}
