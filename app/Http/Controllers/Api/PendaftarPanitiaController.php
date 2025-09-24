<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\PendaftarPanitia;
use App\Models\PenerimaanPanitia;
use App\Models\Event;

class PendaftarPanitiaController extends Controller
{
    /**
     * Daftar semua panitia
     */
    public function index()
    {
        $panitia = PendaftarPanitia::with('event')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar semua panitia',
            'data'    => $panitia
        ], 200);
    }

    /**
     * Tambah panitia baru
     */
    public function store(Request $request , $kode_event)
    {

        $event = Event::where('kode_event', $kode_event)->first();
        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event dengan kode ' . $kode_event . ' tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama'               => 'required|string|max:255',
            'NIM'                => 'required|string|max:16',
            'email'              => 'required|email',
            'nomor_whatapp'      => 'required|string|max:14',
            'angkatan'           => 'required|string|max:4',
            'kelas'              => 'required|string|max:50',
            'tanggal_lahir'      => 'required|date',
            'ukuran_kaos'        => 'required|string|max:10',
            'nomor_darurat'      => 'required|string|max:14',
            'tipe_nomor_darurat' => 'required|string|max:50',
            'riwayat_penyakit'   => 'nullable|string|max:255',
            'divisi'             => 'required|string|max:100',
            'komitmen1'          => 'required|in:ya,tidak',
            'komitmen2'          => 'required|in:ya,tidak',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::transaction(function () use ($validator, &$panitia , $request, $event) {
                $data = $validator->validated();

                // Cek unik NIM/email per event
                if (PendaftarPanitia::where('event_id', $event->id)->where('NIM', $data['NIM'])->exists()) {
                    throw new \Exception('NIM sudah terdaftar di event ini');
                }
                if (PendaftarPanitia::where('event_id', $event->id)->where('email', $data['email'])->exists()) {
                    throw new \Exception('Email sudah terdaftar di event ini');
                }

                // Buat panitia tanpa auto kode_panitia
                $panitia = PendaftarPanitia::create(array_merge($data, [
                    'event_id' => $event->id,
                ]));
                // Buat penerimaan otomatis
                PenerimaanPanitia::create([
                    'pendaftaran_panitia_id' => $panitia->id,
                    'tanggal_penerimaan'     => null,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Panitia berhasil dibuat & penerimaan otomatis dibuat',
                'data'    => $panitia
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi error saat menyimpan data panitia',
                'error'   => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Update data panitia
     */
    public function update(Request $request, $id)
    {
        $panitia = PendaftarPanitia::with('event')->find($id);

        if (!$panitia) {
            return response()->json([
                'success' => false,
                'message' => 'Panitia tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'event_id'           => 'sometimes|exists:event,id',
            'kode_panitia'       => 'sometimes|string|max:50',
            'nama'               => 'sometimes|string|max:255',
            'NIM'                => 'sometimes|string|max:16',
            'email'              => 'sometimes|email',
            'nomor_whatapp'      => 'sometimes|string|max:14',
            'angkatan'           => 'sometimes|string|max:4',
            'kelas'              => 'sometimes|string|max:50',
            'tanggal_lahir'      => 'sometimes|date',
            'ukuran_kaos'        => 'sometimes|string|max:10',
            'nomor_darurat'      => 'sometimes|string|max:14',
            'tipe_nomor_darurat' => 'sometimes|string|max:50',
            'riwayat_penyakit'   => 'nullable|string|max:255',
            'divisi'             => 'sometimes|string|max:100',
            'komitmen1'          => 'sometimes|in:ya,tidak',
            'komitmen2'          => 'sometimes|in:ya,tidak',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // ✅ Cek unik NIM hanya jika berubah
        if (isset($data['NIM']) && $data['NIM'] !== $panitia->NIM) {
            $cekNIM = PendaftarPanitia::where('event_id', $panitia->event_id)
                        ->where('NIM', $data['NIM'])
                        ->where('id', '!=', $panitia->id)
                        ->exists();
            if ($cekNIM) {
                return response()->json([
                    'success' => false,
                    'message' => 'NIM sudah digunakan panitia lain pada event ini'
                ], 422);
            }
        }

        // ✅ Cek unik email hanya jika berubah
        if (isset($data['email']) && $data['email'] !== $panitia->email) {
            $cekEmail = PendaftarPanitia::where('event_id', $panitia->event_id)
                        ->where('email', $data['email'])
                        ->where('id', '!=', $panitia->id)
                        ->exists();
            if ($cekEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email sudah digunakan panitia lain pada event ini'
                ], 422);
            }
        }

        $panitia->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Panitia berhasil diperbarui',
            'data'    => $panitia
        ], 200);
    }


    /**
     * Hapus panitia
     */
    public function destroy($id)
    {
        $panitia = PendaftarPanitia::find($id);

        if (!$panitia) {
            return response()->json([
                'success' => false,
                'message' => 'Panitia tidak ditemukan'
            ], 404);
        }

        $panitia->delete();

        return response()->json([
            'success' => true,
            'message' => 'Panitia berhasil dihapus'
        ], 200);
    }
}
