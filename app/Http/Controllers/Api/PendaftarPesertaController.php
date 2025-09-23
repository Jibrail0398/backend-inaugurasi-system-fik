<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\PendaftarPeserta;
use App\Models\PenerimaanPeserta;
use App\Models\Event;

class PendaftarPesertaController extends Controller
{

    public function index()
    {
        try {
            $peserta = PendaftarPeserta::with('event')->get();

            if ($peserta->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data peserta tidak ditemukan',
                    'data'    => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Daftar semua peserta',
                'data'    => $peserta
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request, $kode_event)
    {
        // Cari event berdasarkan kode_event di URL
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
            'bukti_pembayaran'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::transaction(function () use ($validator, &$peserta, $request, $event) {
                $data = $validator->validated();

                // Cek unik NIM & email di event ini
                if (PendaftarPeserta::where('event_id', $event->id)->where('NIM', $data['NIM'])->exists()) {
                    throw new \Exception('NIM sudah terdaftar di event ini');
                }
                if (PendaftarPeserta::where('event_id', $event->id)->where('email', $data['email'])->exists()) {
                    throw new \Exception('Email sudah terdaftar di event ini');
                }

                // Upload bukti pembayaran
                if ($request->hasFile('bukti_pembayaran')) {
                    $file = $request->file('bukti_pembayaran');
                    $dir = 'bukti_pembayaran/' . $event->kode_event;
                    $path = $file->store($dir, 'public');
                    $data['bukti_pembayaran'] = $path;
                }

                // Buat peserta tanpa auto kode_peserta
                $peserta = PendaftarPeserta::create(array_merge($data, [
                    'event_id' => $event->id,
                ]));

                // Buat penerimaan otomatis
                PenerimaanPeserta::create([
                    'pendaptar_peserta_id' => $peserta->id,
                    'status_pembayaran'    => 'belum lunas',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Peserta berhasil dibuat & penerimaan otomatis dibuat',
                'data'    => $peserta
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi error saat menyimpan data peserta',
                'error'   => $e->getMessage()
            ], 500);
        }
    }



    public function update(Request $request, $id)
    {
        $peserta = PendaftarPeserta::find($id);
        if (!$peserta) {
            return response()->json([
                'success' => false,
                'message' => 'Peserta tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'event_id'          => 'sometimes|exists:event,id',
            'kode_peserta'      => 'sometimes|string|unique:pendaptar_peserta,kode_peserta,' . $id,
            'nama'              => 'sometimes|string|max:255',
            'NIM'               => 'sometimes|string|max:16|unique:pendaptar_peserta,NIM,' . $id,
            'email'             => 'sometimes|email|unique:pendaptar_peserta,email,' . $id,
            'nomor_whatapp'     => 'sometimes|string|max:14',
            'angkatan'          => 'sometimes|string|max:4',
            'kelas'             => 'sometimes|string|max:50',
            'tanggal_lahir'     => 'sometimes|date',
            'ukuran_kaos'       => 'sometimes|string|max:10',
            'nomor_darurat'     => 'sometimes|string|max:14',
            'tipe_nomor_darurat'=> 'sometimes|string|max:50',
            'riwayat_penyakit'  => 'nullable|string|max:255',
            'divisi'            => 'sometimes|string|max:100',
            'bukti_pembayaran'  => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Upload bukti pembayaran baru jika ada
        if ($request->hasFile('bukti_pembayaran')) {
            // Hapus file lama jika ada
            if ($peserta->bukti_pembayaran && \Storage::disk('public')->exists($peserta->bukti_pembayaran)) {
                \Storage::disk('public')->delete($peserta->bukti_pembayaran);
            }

            $file = $request->file('bukti_pembayaran');

            // Folder berdasarkan kode event
            $kodeEvent = $peserta->event->kode_event ?? 'umum';
            $dir = 'bukti_pembayaran/' . $kodeEvent;

            $path = $file->store($dir, 'public');
            $data['bukti_pembayaran'] = $path;
        }

        $peserta->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Peserta berhasil diperbarui',
            'data'    => $peserta
        ], 200);
    }


    public function destroy($id)
    {
        $peserta = PendaftarPeserta::find($id);

        if (!$peserta) {
            return response()->json([
                'success' => false,
                'message' => 'Peserta tidak ditemukan'
            ], 404);
        }

        $peserta->delete();

        return response()->json([
            'success' => true,
            'message' => 'Peserta berhasil dihapus'
        ], 200);
    }
}