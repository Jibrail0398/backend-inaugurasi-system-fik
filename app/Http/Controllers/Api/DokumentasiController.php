<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dokumentasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DokumentasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dokumentasi = Dokumentasi::with(['event', 'creator', 'updater'])->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $dokumentasi
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_dokumentasi' => 'required|string|max:255',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'link_drive' => 'nullable|string|max:255',
            'event_id' => 'required|exists:event,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $dokumentasi = Dokumentasi::create([
            'nama_dokumentasi' => $request->nama_dokumentasi,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'link_drive' => $request->link_drive,
            'event_id' => $request->event_id,
            'create_by' => Auth::id(),
            'update_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Dokumentasi created successfully',
            'data' => $dokumentasi->load(['event', 'creator', 'updater'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $dokumentasi = Dokumentasi::with(['event', 'creator', 'updater'])->find($id);
        
        if (!$dokumentasi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dokumentasi not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $dokumentasi
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $dokumentasi = Dokumentasi::find($id);
        
        if (!$dokumentasi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dokumentasi not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_dokumentasi' => 'required|string|max:255',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'link_drive' => 'nullable|string|max:255',
            'event_id' => 'required|exists:event,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $dokumentasi->update([
            'nama_dokumentasi' => $request->nama_dokumentasi,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'link_drive' => $request->link_drive,
            'event_id' => $request->event_id,
            'update_by' => Auth::id(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Dokumentasi updated successfully',
            'data' => $dokumentasi->load(['event', 'creator', 'updater'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $dokumentasi = Dokumentasi::find($id);
        
        if (!$dokumentasi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dokumentasi not found'
            ], 404);
        }

        $dokumentasi->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Dokumentasi deleted successfully'
        ]);
    }

    /**
     * Get dokumentasi by event.
     */
    public function getByEvent($eventId)
    {
        $dokumentasi = Dokumentasi::with(['event', 'creator', 'updater'])
            ->where('event_id', $eventId)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $dokumentasi
        ]);
    }
}