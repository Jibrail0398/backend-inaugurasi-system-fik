<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    public function Store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_event' => 'required|string|unique:event,kode_event',
            'nama_event' => 'required|string|max:255',
            'jenis' => 'required|string|max:255',
            'tema' => 'required|string|max:255',
            'tempat' => 'required|string|max:255',
            'harga_pendaftaran_peserta' => 'required|integer|min:0',
            'status_pendaftaran_panitia' => 'required|in:buka,tutup',
            'status_pendaftaran_peserta' => 'required|in:buka,tutup',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()
            ], 422);
        }

        $payload = $validator->validated();

        try {

            $event = Event::create($payload);

            return response()->json([
                'success' => true,
                'message' => 'Event berhasil disimpan',
                'data' => $event
            ], 201);

        } catch (\Throwable $e) {

            Log::error('Event Store error: '.$e->getMessage(), [
                'payload' => $payload,
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat menyimpan event'
            ], 500);
        }
    }

    public function Index()
    {
        try {

            $events = Event::all();

            return response()->json([
                'success' => true,
                'data' => $events
            ], 200);

        } catch (\Throwable $e) {

            Log::error('Event Index error: '.$e->getMessage(), [
                'exception' => $e
            ]); 
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat mengambil event'
            ], 500);           
        }
    }

    public function Show($id)
    {
        try {
            
            $event = Event::find($id);

            if(!$event){
                return response()->json([
                    'success' => false,
                    'message' => 'Event tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $event
            ], 200);

        } catch (\Throwable $e) {

            Log::error('Event Show error: '.$e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat mengambil event'
            ], 500);
        }
    }

    public function Update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'kode_event' => 'required|string|unique:event,kode_event,' . $id,
            'nama_event' => 'required|string|max:255',
            'jenis' => 'required|string|max:255',
            'tema' => 'required|string|max:255',
            'tempat' => 'required|string|max:255',
            'harga_pendaftaran_peserta' => 'required|integer|min:0',
            'status_pendaftaran_panitia' => 'required|in:buka,tutup',
            'status_pendaftaran_peserta' => 'required|in:buka,tutup',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()
            ], 422);
        }

        $payload = $validator->validated();

        try {

            $event = Event::find($id);

            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event tidak ditemukan'
                ], 404);
            }

            $event->update($payload);

            return response()->json([
                'success' => true,
                'message' => 'Event berhasil diperbarui',
                'data' => $event
            ], 200);

        } catch (\Throwable $e) {

            Log::error('Event Update error: '.$e->getMessage(), [
                'payload' => $payload,
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat memperbarui event'
            ], 500);
        }
    }

    public function Destroy($id)
    {
        try {

            $event = Event::find($id);

            if(!$event){
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

        } catch (\Throwable $e) {

            Log::error('Event Destory error: '.$e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat menghapus event'
            ], 500);
        }
    }
}
