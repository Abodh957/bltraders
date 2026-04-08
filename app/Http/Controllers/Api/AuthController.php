<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Shop;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone_no' => 'required|digits:10'
        ]);

        // $otp = rand(100000, 999999);
        $otp=000000;
        $user = User::updateOrCreate(
            ['phone_no' => $request->phone_no],
            [
                'otp' => $otp,
                'expires_at' => Carbon::now()->addMinutes(5)
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully',
            'otp' => $otp
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone_no' => 'required|digits:10',
            'otp' => 'required|digits:6'
        ]);

        $user = User::where('phone_no', $request->phone_no)->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        if ($user->otp != $request->otp) {
            return response()->json(['status' => false, 'message' => 'Invalid OTP'], 400);
        }

        if (Carbon::now()->gt($user->expires_at)) {
            return response()->json(['status' => false, 'message' => 'OTP expired'], 400);
        }

        $user->update([
            'otp' => null,
            'expires_at' => null
        ]);

        $shop = Shop::where('user_id', $user->id)->first();

        if (!$shop) {
            $token = $user->createToken('temp_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'type' => 'new_user',
                'message' => 'Please create shop first',
                'token' => $token
            ]);
        }

        if ($shop->status == 'pending') {
            return response()->json([
                'status' => false,
                'type' => 'pending',
                'message' => 'Your request is under review'
            ]);
        }

        if ($shop->status == 'rejected') {
            return response()->json([
                'status' => false,
                'type' => 'rejected',
                'message' => 'Your shop request was rejected'
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'type' => 'login',
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
            'shop' => $shop
        ]);
    }

    public function addShop(Request $request)
    {
        $request->validate([
            'shop_name' => 'required|string|max:255',
            'shop_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'state' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'pincode' => 'required|digits_between:4,10',
            'shop_address' => 'required|string|max:1000',
        ]);

        $user = auth()->user();

        $existing = Shop::where('user_id', $user->id)->first();
        if ($existing) {
            return response()->json([
                'status' => false,
                'message' => 'Shop already exists'
            ], 409);
        }

        $filename = null;
        if ($request->hasFile('shop_document')) {
            $file = $request->file('shop_document');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/shop_docs'), $filename);
        }

        // create shop
        $shop = Shop::create([
            'user_id' => $user->id,
            'shop_name' => trim($request->shop_name),
            'shop_document' => $filename,
            'state' => $request->state,
            'city' => $request->city,
            'country' => $request->country,
            'pincode' => $request->pincode,
            'shop_address' => trim($request->shop_address),
            'status' => 'pending'
        ]);
        $user->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Your request submitted successfully. Please wait for admin approval.',
            'data' => $shop
        ], 201);
    }
    public function logout(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logout successful'
        ]);
    }
}