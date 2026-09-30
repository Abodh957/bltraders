<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

/**
 * The logged-in customer's own shop.
 *
 * Creating a shop stays in AuthController::addShop (one shop per customer);
 * this controller only reads and edits the existing one.
 */
class ShopController extends Controller
{
    /** Where shop documents live, relative to public/. */
    private const DOC_PATH = 'uploads/shop_docs/';

    /** GET /api/shop */
    public function show(Request $request)
    {
        $shop = Shop::where('user_id', $request->user()->id)->first();

        if (!$shop) {
            return response()->json([
                'status'     => false,
                'shopStatus' => null,
                'message'    => 'Shop not found. Please create your shop first.',
            ], 404);
        }

        return response()->json(['status' => true, 'data' => $this->format($shop)]);
    }

    /**
     * POST /api/shop/update — partial; send only what changes.
     *
     * The approval status is deliberately left alone: flipping an approved shop
     * back to pending would instantly block the customer's cart and orders
     * (the shop.approved middleware), which an edit should not do.
     */
    public function update(Request $request)
    {
        $shop = Shop::where('user_id', $request->user()->id)->first();

        if (!$shop) {
            return response()->json([
                'status'     => false,
                'shopStatus' => null,
                'message'    => 'Shop not found. Please create your shop first.',
            ], 404);
        }

        $data = $request->validate([
            'shop_name'     => 'sometimes|required|string|max:255',
            'shop_address'  => 'sometimes|required|string|max:1000',
            'city'          => 'sometimes|required|string|max:100',
            'state'         => 'sometimes|required|string|max:100',
            'country'       => 'sometimes|required|string|max:100',
            'pincode'       => 'sometimes|required|digits_between:4,10',
            'shop_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        foreach (['shop_name', 'shop_address', 'city', 'state', 'country', 'pincode'] as $field) {
            if (array_key_exists($field, $data)) {
                $shop->{$field} = trim((string) $data[$field]);
            }
        }

        if ($request->hasFile('shop_document')) {
            $dir = public_path(self::DOC_PATH);
            if (!file_exists($dir)) {
                mkdir($dir, 0775, true);
            }

            $file = $request->file('shop_document');
            $name = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $name);

            // Remove the previous file only once the new one is safely in place.
            $old = $shop->shop_document;
            $shop->shop_document = $name;

            if ($old && is_file($dir . $old)) {
                @unlink($dir . $old);
            }
        }

        $shop->save();

        return response()->json([
            'status'  => true,
            'message' => 'Shop updated successfully.',
            'data'    => $this->format($shop->fresh()),
        ]);
    }

    private function format(Shop $shop): array
    {
        return [
            'id'            => $shop->id,
            'shop_name'     => $shop->shop_name,
            'shop_address'  => $shop->shop_address,
            'city'          => $shop->city,
            'state'         => $shop->state,
            'country'       => $shop->country,
            'pincode'       => $shop->pincode,
            // APP_URL points at the app root while files live under public/.
            'shop_document' => $shop->shop_document ? url('public/' . self::DOC_PATH . $shop->shop_document) : null,
            'status'        => $shop->status,
            'created_at'    => $shop->created_at,
            'updated_at'    => $shop->updated_at,
        ];
    }
}
