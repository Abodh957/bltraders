<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Otp;
use Carbon\Carbon;
class AuthController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone_no' => 'required|digits:10'
        ]);
        $otp = rand(100000, 999999);

        User::updateOrCreate(
            ['phone_no' => $request->phone_no],
            [
                'otp' => $otp,
                'expires_at' => Carbon::now()->addMinutes(5)
            ]
        );

        // TODO: SMS send karo (Fast2SMS / MSG91)

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully',
            'otp' => $otp 
        ]);
    }
}
