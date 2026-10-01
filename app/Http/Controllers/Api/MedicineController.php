<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MedicineController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Medicine::with(['category', 'supplier']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('generic_name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 15);
        $medicines = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Medicines retrieved.',
            'data'    => $medicines,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $medicine = Medicine::with(['category', 'supplier'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Medicine retrieved.',
            'data'    => $medicine,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'code' => $request->input('code') ?? 'MED-' . Str::upper(Str::random(8)),
        ]);

        $validated = $request->validate([
            'code'           => 'required|string|max:20|unique:medicines,code',
            'name'           => 'required|string|max:150',
            'generic_name'   => 'nullable|string|max:150',
            'category_id'    => 'nullable|string|exists:categories,id',
            'manufacturer'   => 'nullable|string|max:150',
            'batch_no'       => 'nullable|string|max:50',
            'quantity'       => 'nullable|integer|min:0',
            'unit'           => 'nullable|string|max:20',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price'  => 'nullable|numeric|min:0',
            'expiry_date'    => 'nullable|date',
            'min_stock'      => 'nullable|integer|min:0',
            'supplier_id'    => 'nullable|string|exists:suppliers,id',
            'status'         => 'nullable|in:in_stock,low_stock,out_of_stock,expiring_soon,expired',
        ]);

        $medicine = Medicine::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Medicine created.',
            'data'    => $medicine,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $medicine = Medicine::findOrFail($id);

        $validated = $request->validate([
            'code'           => 'sometimes|required|string|max:20|unique:medicines,code,' . $id,
            'name'           => 'sometimes|required|string|max:150',
            'generic_name'   => 'nullable|string|max:150',
            'category_id'    => 'nullable|string|exists:categories,id',
            'manufacturer'   => 'nullable|string|max:150',
            'batch_no'       => 'nullable|string|max:50',
            'quantity'       => 'nullable|integer|min:0',
            'unit'           => 'nullable|string|max:20',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price'  => 'nullable|numeric|min:0',
            'expiry_date'    => 'nullable|date',
            'min_stock'      => 'nullable|integer|min:0',
            'supplier_id'    => 'nullable|string|exists:suppliers,id',
            'status'         => 'nullable|in:in_stock,low_stock,out_of_stock,expiring_soon,expired',
        ]);

        $medicine->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Medicine updated.',
            'data'    => $medicine,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $medicine = Medicine::findOrFail($id);
        $medicine->delete();

        return response()->json([
            'success' => true,
            'message' => 'Medicine deleted.',
        ]);
    }
}
