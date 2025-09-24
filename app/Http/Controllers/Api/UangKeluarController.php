<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UangKeluar;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UangKeluarController extends Controller
{
    public function index()
    {
        $data = UangKeluar::with('keuangan')->get();
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function show($id)
    {
        $pengeluaran = UangKeluar::with('keuangan')->find($id);
        if (!$pengeluaran) return response()->json(['success' => false, 'message' => 'Data pengeluaran tidak ditemukan'], 404);
        return response()->json(['success' => true, 'data' => $pengeluaran]);
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

        $data = $validator->validated();

        // Upload foto bukti
        if ($request->hasFile('bukti_pengeluaran')) {
            $file = $request->file('bukti_pengeluaran');
            $path = $file->store('bukti_pengeluaran', 'public');
            $data['bukti_pengeluaran'] = $path;
        }

        $pengeluaran = UangKeluar::create($data);

        return response()->json(['success' => true, 'message' => 'Pengeluaran berhasil dibuat', 'data' => $pengeluaran], 201);
    }

    public function update(Request $request, $id)
    {
        $pengeluaran = UangKeluar::find($id);
        if (!$pengeluaran) return response()->json(['success' => false, 'message' => 'Data pengeluaran tidak ditemukan'], 404);

        $validator = Validator::make($request->all(), [
            'jumlah_pengeluaran' => 'sometimes|integer',
            'alasan_pengeluaran' => 'sometimes|string|max:255',
            'tanggal_pengeluaran' => 'sometimes|date',
            'bukti_pengeluaran' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048', // foto opsional
            'keuangan_id' => 'sometimes|exists:keuangan,id',
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);

        $data = $validator->validated();

        // Upload foto baru jika ada
        if ($request->hasFile('bukti_pengeluaran')) {
            // hapus file lama
            if ($pengeluaran->bukti_pengeluaran && Storage::disk('public')->exists($pengeluaran->bukti_pengeluaran)) {
                Storage::disk('public')->delete($pengeluaran->bukti_pengeluaran);
            }

            $file = $request->file('bukti_pengeluaran');
            $path = $file->store('bukti_pengeluaran', 'public');
            $data['bukti_pengeluaran'] = $path;
        }

        $pengeluaran->update($data);

        return response()->json(['success' => true, 'message' => 'Pengeluaran berhasil diperbarui', 'data' => $pengeluaran]);
    }

    public function destroy($id)
    {
        $pengeluaran = UangKeluar::find($id);
        if (!$pengeluaran) return response()->json(['success' => false, 'message' => 'Data pengeluaran tidak ditemukan'], 404);

        // hapus file bukti
        if ($pengeluaran->bukti_pengeluaran && Storage::disk('public')->exists($pengeluaran->bukti_pengeluaran)) {
            Storage::disk('public')->delete($pengeluaran->bukti_pengeluaran);
        }

        $pengeluaran->delete();

        return response()->json(['success' => true, 'message' => 'Pengeluaran berhasil dihapus']);
    }
}
