<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Keuangan;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with('keuangan')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar semua event',
            'data'    => $events
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_event' => 'required|string|unique:event,kode_event',
            'nama_event' => 'required|string|max:255',
            'jenis'      => 'required|string',
            'tema'       => 'required|string',
            'tempat'     => 'required|string|max:255',
            'harga_pendaftaran_peserta' => 'sometimes|integer|min:0',
            'status_pendaftaran_panitia' => 'required|in:buka,tutup',
            'status_pendaftaran_peserta' => 'required|in:buka,tutup',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Ambil user dari middleware AuthJWT
        $user = $request->user ?? null;
        $data['created_by'] = $user ? $user->id : null;
        $data['updated_by'] = $user ? $user->id : null;

        $event = Event::create($data);

        // Buat keuangan untuk event baru
        Keuangan::create([
            'event_id' => $event->id,
            'saldo' => 0,
            'created_by' => $user ? $user->id : null,
            'updated_by' => $user ? $user->id : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil dibuat',
            'data'    => $event
        ], 201);
    }

    public function show($id)
    {
        $event = Event::with('keuangan')->find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail event',
            'data'    => $event
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'kode_event' => 'sometimes|string|unique:event,kode_event,' . $id,
            'nama_event' => 'sometimes|string|max:255',
            'jenis'      => 'sometimes|string',
            'tema'       => 'sometimes|string',
            'tempat'     => 'sometimes|string|max:255',
            'harga_pendaftaran_peserta' => 'sometimes|integer|min:0',
            'status_pendaftaran_panitia' => 'sometimes|in:buka,tutup',
            'status_pendaftaran_peserta' => 'sometimes|in:buka,tutup',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Gunakan user dari middleware
        $user = $request->user ?? null;
        $data['updated_by'] = $user ? $user->id : null;

        $event->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil diperbarui',
            'data'    => $event
        ], 200);
    }

    public function destroy($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event tidak ditemukan'
            ], 404);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event berhasil dihapus'
        ], 200);
    }

    public function checkcode($code)
    {   
        try {
            $event = Event::where('kode_event', $code)->first();

            if(!$event){
                return response()->json([
                    'success' => false,
                    'message' => 'Kode tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Kode valid',
                'event' => [
                    'id' => $event->id,
                    'event' => $event->nama_event
                ]
            ], 200);

        } catch (\Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }
}
