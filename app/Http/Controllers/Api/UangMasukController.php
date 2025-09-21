<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UangMasuk;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UangMasukController extends Controller
{
    public function index()
    {
        $data = UangMasuk::with('keuangan')->get();
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function show($id)
    {
        $masuk = UangMasuk::with('keuangan')->find($id);
        if (!$masuk) return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        return response()->json(['success' => true, 'data' => $masuk]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jumlah_uang_masuk' => 'required|integer',
            'asal_pemasukan' => 'required|string|max:255',
            'tanggal_pemasukan' => 'required|date',
            'bukti_pemasukan' => 'required|image|mimes:jpeg,png,jpg|max:2048', // wajib foto
            'keuangan_id' => 'required|exists:keuangan,id',
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);

        $data = $validator->validated();

        // Upload foto bukti
        if ($request->hasFile('bukti_pemasukan')) {
            $file = $request->file('bukti_pemasukan');
            $path = $file->store('bukti_pemasukan', 'public'); // disimpan di storage/app/public/bukti_pemasukan
            $data['bukti_pemasukan'] = $path;
        }

        $masuk = UangMasuk::create($data);

        return response()->json(['success' => true, 'message' => 'Pemasukan berhasil dibuat', 'data' => $masuk], 201);
    }

    public function update(Request $request, $id)
    {
        $masuk = UangMasuk::find($id);
        if (!$masuk) return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);

        $validator = Validator::make($request->all(), [
            'jumlah_uang_masuk' => 'sometimes|integer',
            'asal_pemasukan' => 'sometimes|string|max:255',
            'tanggal_pemasukan' => 'sometimes|date',
            'bukti_pemasukan' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048', // foto opsional
            'keuangan_id' => 'sometimes|exists:keuangan,id',
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);

        $data = $validator->validated();

        // Upload foto baru jika ada
        if ($request->hasFile('bukti_pemasukan')) {
            // hapus file lama
            if ($masuk->bukti_pemasukan && Storage::disk('public')->exists($masuk->bukti_pemasukan)) {
                Storage::disk('public')->delete($masuk->bukti_pemasukan);
            }

            $file = $request->file('bukti_pemasukan');
            $path = $file->store('bukti_pemasukan', 'public');
            $data['bukti_pemasukan'] = $path;
        }

        $masuk->update($data);

        return response()->json(['success' => true, 'message' => 'Pemasukan berhasil diperbarui', 'data' => $masuk]);
    }

    public function destroy($id)
    {
        $masuk = UangMasuk::find($id);
        if (!$masuk) return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);

        // hapus file bukti
        if ($masuk->bukti_pemasukan && Storage::disk('public')->exists($masuk->bukti_pemasukan)) {
            Storage::disk('public')->delete($masuk->bukti_pemasukan);
        }

        $masuk->delete();
        return response()->json(['success' => true, 'message' => 'Pemasukan berhasil dihapus']);
    }
}
