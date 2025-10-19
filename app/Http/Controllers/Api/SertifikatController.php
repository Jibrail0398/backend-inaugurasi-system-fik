<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\sertifikat as SertifikatModel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Exception;

class SertifikatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $data = SertifikatModel::with(['event', 'creator', 'updater'])->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'nama_sertifikat' => 'required|string|max:255',
            'jenis_sertifikat' => 'required|in:peserta,panitia',
            'link_drive' => 'nullable|string',
            'event_id' => 'required|exists:event,id',
            'create_by' => 'nullable|exists:users,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $payload = $validator->validated();
            $sertifikat = SertifikatModel::create($payload);

            return response()->json([
                'success' => true,
                'data' => $sertifikat
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $sertifikat = SertifikatModel::with(['event', 'creator', 'updater'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $sertifikat
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Not used for API resource; kept for compatibility.
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rules = [
            'nama_sertifikat' => 'sometimes|required|string|max:255',
            'jenis_sertifikat' => 'sometimes|required|in:peserta,panitia',
            'link_drive' => 'nullable|string',
            'event_id' => 'sometimes|required|exists:event,id',
            'update_by' => 'nullable|exists:users,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sertifikat = SertifikatModel::findOrFail($id);
            $sertifikat->update($validator->validated());

            return response()->json([
                'success' => true,
                'data' => $sertifikat
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $sertifikat = SertifikatModel::findOrFail($id);
            $sertifikat->delete();

            return response()->json([
                'success' => true,
                'message' => 'Deleted successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
