<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromoCodeController extends Controller
{
    public function index(Request $request)
    {
        $query = PromoCode::query();

        if ($request->filled('search')) {
            $query->where('code', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $perPage = (int) $request->input('per_page', 10);
        if ($perPage > 100) $perPage = 100;

        $promoCodes = $query->latest()->paginate($perPage);

        if ($promoCodes->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No promo codes found.',
                'data' => []
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Promo codes retrieved successfully.',
            'data' => $promoCodes
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code'     => 'required|string|unique:promo_codes,code',
            'discount' => 'required|numeric|min:0',
            'max_uses' => 'required|integer|min:1',
            'expires'  => 'required|date|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error occurred.',
                'errors' => $validator->errors()
            ], 422);
        }

        $promo = PromoCode::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Promo code created successfully.',
            'data' => $promo
        ], 201);
    }

    public function show($id)
    {
        $promo = PromoCode::find($id);

        if (!$promo) {
            return response()->json(['status' => 'error', 'message' => 'Promo code not found.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Promo code details retrieved.',
            'data' => $promo
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $promo = PromoCode::find($id);

        if (!$promo) {
            return response()->json(['status' => 'error', 'message' => 'Promo code not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'code'     => 'required|string|unique:promo_codes,code,' . $id,
            'discount' => 'required|numeric|min:0',
            'max_uses' => 'required|integer|min:1',
            'expires'  => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation error.', 'errors' => $validator->errors()], 422);
        }

        $promo->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Promo code updated successfully.',
            'data' => $promo
        ], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $promo = PromoCode::find($id);

        if (!$promo) {
            return response()->json(['status' => 'error', 'message' => 'Promo code not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Invalid status value.', 'errors' => $validator->errors()], 422);
        }

        $promo->update(['status' => $request->status]);

        return response()->json([
            'status' => 'success',
            'message' => 'Promo code status updated successfully.',
            'data' => $promo
        ], 200);
    }

    public function destroy($id)
    {
        $promo = PromoCode::find($id);

        if (!$promo) {
            return response()->json(['status' => 'error', 'message' => 'Promo code not found.'], 404);
        }

        $promo->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Promo code deleted successfully.'
        ], 200);
    }
}
