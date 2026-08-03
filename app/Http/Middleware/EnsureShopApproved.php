<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only customers with an approved shop may use the cart / place orders.
 *
 * verifyOtp() hands a short-lived "temp_token" to users who have not created a
 * shop yet, so an authenticated request is not by itself proof of an approved
 * account. This middleware closes that gap.
 */
class EnsureShopApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $shop = Shop::where('user_id', $user->id)->first();

        if (!$shop) {
            return response()->json([
                'status'     => false,
                'shopStatus' => null,
                'message'    => 'Please create your shop first.',
            ], 403);
        }

        if ($shop->status !== 'approved') {
            return response()->json([
                'status'     => false,
                'shopStatus' => $shop->status,
                'message'    => $shop->status === 'rejected'
                    ? 'Your shop request was rejected.'
                    : 'Your shop request is under review.',
            ], 403);
        }

        return $next($request);
    }
}
