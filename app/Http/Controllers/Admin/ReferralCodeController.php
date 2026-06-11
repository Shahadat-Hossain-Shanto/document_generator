<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReferralCodeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('limit', 10);
        $page = $request->get('current_page', 1);

        $query = ReferralCode::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%");
            });
        }

        $statsQuery = clone $query;

        $referralCodes = $query->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $totalCodes = $statsQuery->count();
        $totalSalesGenerated = $statsQuery->sum('used_count');

        return response()->json([
            'success' => true,
            'message' => 'Referral codes retrieved successfully',
            'stats' => [
                'total_codes'           => $totalCodes,
                'total_sales_generated' => (int) $totalSalesGenerated,
            ],
            'data'         => $referralCodes->items(),
            'total'        => $referralCodes->total(),
            'limit'        => $referralCodes->perPage(),
            'current_page' => $referralCodes->currentPage(),
            'last_page'    => $referralCodes->lastPage(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'  => 'required|string|max:255',
            'code'   => 'nullable|string|unique:referral_codes,code|max:50',
            'type'   => 'required|in:fixed,percentage,none',
            'amount' => 'required_if:type,fixed,percentage|numeric|min:0',
        ]);

        $code = $request->code ? strtoupper($request->code) : Str::upper(Str::random(8));

        $amount = $request->type === 'none' ? 0 : $request->amount;

        $referralCode = ReferralCode::create([
            'title'     => $request->title,
            'code'      => $code,
            'type'      => $request->type,
            'amount'    => $amount,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Referral code generated successfully.',
            'data'    => $referralCode
        ], 201);
    }

    public function show($id)
    {
        $referralCode = ReferralCode::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $referralCode
        ], 200);
    }

    /**
     * 3. Update code (Only allowed if used_count == 0).
     */
    public function update(Request $request, $id)
    {
        $referralCode = ReferralCode::findOrFail($id);

        if ($referralCode->used_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'This referral code has already been used and cannot be updated.'
            ], 422);
        }

        $request->validate([
            'title'  => 'required|string|max:255',
            'code'   => 'nullable|string|max:50|unique:referral_codes,code,' . $id,
            'type'   => 'required|in:fixed,percentage,none',
            'amount' => 'required_if:type,fixed,percentage|numeric|min:0',
        ]);

        $code = $request->code ? strtoupper($request->code) : $referralCode->code;
        $amount = $request->type === 'none' ? 0 : $request->amount;

        $referralCode->update([
            'title'  => $request->title,
            'code'   => $code,
            'type'   => $request->type,
            'amount' => $amount,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Referral code updated successfully.',
            'data'    => $referralCode
        ], 200);
    }

    public function destroy($id)
    {
        $referralCode = ReferralCode::findOrFail($id);

        if ($referralCode->used_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'This referral code has already been used and cannot be deleted.'
            ], 422);
        }

        $referralCode->delete();

        return response()->json([
            'success' => true,
            'message' => 'Referral code deleted successfully.'
        ], 200);
    }

    public function toggleStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|boolean'
        ]);

        $referralCode = ReferralCode::findOrFail($id);

        $referralCode->update([
            'is_active' => $request->status
        ]);

        $statusText = $request->status ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Referral code {$statusText} successfully.",
            'data'    => $referralCode
        ], 200);
    }
}
