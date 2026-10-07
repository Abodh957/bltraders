<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
    /**
     * POST /api/account/delete
     *
     * Soft-deletes the customer's account. The row stays in the database, so
     * past orders and their invoices still resolve, but the user disappears
     * from every query (SoftDeletes) and can no longer log in.
     *
     * Deliberately kept: orders, order items and delivery addresses — they are
     * business records. Removed: active carts (nothing to buy any more) and
     * every API token (so the app is logged out immediately).
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user) {
            // Carts are throwaway state, not a record — clear them.
            $cartIds = Cart::where('user_id', $user->id)->pluck('id');
            CartItem::whereIn('cart_id', $cartIds)->delete();
            Cart::whereIn('id', $cartIds)->delete();

            // The shop goes with the account; its row stays for old orders.
            Shop::where('user_id', $user->id)->update(['status' => 'rejected']);

            // Log out everywhere.
            $user->tokens()->delete();

            $user->selected_store_id = null;
            $user->save();
            $user->delete();   // soft delete
        });

        Log::info('[account] deleted', ['user_id' => $user->id, 'reason' => $request->reason]);

        return response()->json([
            'status'  => true,
            'message' => 'Your account has been deleted.',
        ]);
    }
}
