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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id'           => 'required|exists:event,id',
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
            DB::transaction(function () use ($validator, &$panitia) {
                $data = $validator->validated();
                $event = Event::findOrFail($data['event_id']);

                // Hitung nomor urut terakhir dari kode_panitia yang sesuai event
                $lastPanitia = PendaftarPanitia::where('event_id', $data['event_id'])
                    ->where('kode_panitia', 'like', $event->kode_event . '-PAN-%')
                    ->orderBy('id', 'desc')
                    ->first();

                if ($lastPanitia) {
                    // Ambil angka terakhir dari kode_panitia
                    preg_match('/(\d+)$/', $lastPanitia->kode_panitia, $matches);
                    $noUrut = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
                } else {
                    $noUrut = 1;
                }

                $kodePanitia = $event->kode_event . '-PAN-' . str_pad($noUrut, 3, '0', STR_PAD_LEFT);

                // Cek unik NIM/email per event saja
                if (PendaftarPanitia::where('event_id', $data['event_id'])->where('NIM', $data['NIM'])->exists()) {
                    throw new \Exception('NIM sudah terdaftar di event ini');
                }
                if (PendaftarPanitia::where('event_id', $data['event_id'])->where('email', $data['email'])->exists()) {
                    throw new \Exception('Email sudah terdaftar di event ini');
                }

                // Buat panitia
                $panitia = PendaftarPanitia::create(array_merge($data, [
                    'kode_panitia' => $kodePanitia
                ]));

                // Buat penerimaan otomatis untuk panitia
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
        $panitia = PendaftarPanitia::find($id);

        if (!$panitia) {
            return response()->json([
                'success' => false,
                'message' => 'Panitia tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'event_id'          => 'sometimes|exists:event,id',
            'kode_panitia'      => 'sometimes|string|unique:pendaptar_panitia,kode_panitia,' . $id,
            'nama'              => 'sometimes|string|max:255',
            'NIM'               => 'sometimes|string|max:16|unique:pendaptar_panitia,NIM,' . $id,
            'email'             => 'sometimes|email|unique:pendaptar_panitia,email,' . $id,
            'nomor_whatapp'     => 'sometimes|string|max:14',
            'angkatan'          => 'sometimes|string|max:4',
            'kelas'             => 'sometimes|string|max:50',
            'tanggal_lahir'     => 'sometimes|date',
            'ukuran_kaos'       => 'sometimes|string|max:10',
            'nomor_darurat'     => 'sometimes|string|max:14',
            'tipe_nomor_darurat'=> 'sometimes|string|max:50',
            'riwayat_penyakit'  => 'nullable|string|max:255',
            'divisi'            => 'sometimes|string|max:100',
            'komitmen1'         => 'sometimes|in:ya,tidak',
            'komitmen2'         => 'sometimes|in:ya,tidak',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $panitia->update($validator->validated());

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
