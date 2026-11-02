<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponApiController extends Controller
{
    /**
     * Validate a coupon code and return the computed discount amount.
     *
     * Expected request body: { "code": "REAKRMX" }
     *
     * Success response:
     * {
     *   "status": true,
     *   "message": "Coupon applied successfully!",
     *   "data": { "discount_amount": 5.00, "type": "fixed" }
     * }
     *
     * Failure response:
     * {
     *   "status": false,
     *   "message": "Invalid coupon code."
     * }
     */
    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $coupon = Coupon::where('code', trim($request->code))->first();

        if (! $coupon) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid coupon code.',
            ], 422);
        }

        if ($coupon->isExpired()) {
            return response()->json([
                'status' => false,
                'message' => 'This coupon has expired.',
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Coupon applied successfully!',
            'data' => [
                'discount_amount' => (float) $coupon->discount,
                'type' => $coupon->type, // 'fixed' or 'percentage'
                'code' => $coupon->code,
            ],
        ]);
    }
}
