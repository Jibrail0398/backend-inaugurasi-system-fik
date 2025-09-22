<?php

namespace App\Http\Controllers;

use App\Models\Keuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class KeuanganController extends Controller
{
    public function Store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'saldo' => 'nullable|integer',
            'event_id' => 'required|exists:event,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()
            ], 422);
        }

        $payload = $validator->validated();

        try {

            $keuangan = Keuangan::create($payload);

            return response()->json([
                'success' => true,
                'message' => 'Keuangan berhasil disimpan',
                'data' => $keuangan
            ], 201);

        } catch (\Throwable $e) {     
            
            Log::error('Keuangan Store error: '.$e->getMessage(), [
                'payload' => $payload,
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan keuangan.'
            ], 500);
        }
    }

    public function Index()
    {
        try {
            $keuangans = Keuangan::all();

            return response()->json([
                'success' => true,
                'data' => $keuangans
            ], 200);

        } catch (\Throwable $e) {

            Log::error('Keuangan Index error: '.$e->getMessage(), [
                'exception' => $e
            ]); 
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat mengambil keuangan'
            ], 500);           
        }
    }

    public function Show($id)
    {
        try {

            $keuangan = Keuangan::find($id);

            if(!$keuangan){
                return response()->json([
                    'success' => false,
                    'message' => 'Keuangan tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $keuangan
            ], 200);

        } catch (\Throwable $e) {

            Log::error('Keuangan Show error: '.$e->getMessage(), [
                'exception' => $e
            ]); 

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat mengambil keuangan'
            ], 500);           
        }
    }

    public function Update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'saldo' => 'nullable|integer',
            'event_id' => 'required|exists:event,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->messages()
            ], 422);
        }

        $payload = $validator->validated();

        try {

            $keuangan = Keuangan::find($id);

            if (!$keuangan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Keuangan tidak ditemukan'
                ], 404);
            }

            $keuangan->update($payload);

            return response()->json([
                'success' => true,
                'message' => 'Keuangan berhasil diperbarui',
                'data' => $keuangan
            ], 200);

        } catch (\Throwable $e) {

            Log::error('Keuangan Update error: '.$e->getMessage(), [
                'payload' => $payload,
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat memperbarui keuangan'
            ], 500);
        }        
    }

    public function Destroy($id)
    {
        try {

            $keuangan = Keuangan::find($id);

            if(!$keuangan){
                return response()->json([
                    'success' => false,
                    'message' => 'Keuangan tidak ditemukan'
                ], 404);
            }

            $keuangan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Keuangan berhasil dihapus'
            ], 200);

        } catch (\Throwable $e) {

            Log::error('Keuangan Destroy error: '.$e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat menghapus keuangan'
            ], 500);
        }               
    }

    public function TotalSaldo()
    {
        try {

            $totalSaldo = Keuangan::sum('saldo');

            return response()->json([
                'success' => true,
                'total_saldo' => $totalSaldo 
            ], 200);

        } catch (\Throwable $e) {

            Log::error('Keuangan TotalSaldo error: '.$e->getMessage(), [
                'exception' => $e
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat mengambil total saldo'
            ], 500);
        }
    }
}
