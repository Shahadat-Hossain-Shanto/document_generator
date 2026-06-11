<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordOtpMail;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        $otp = rand(1000, 9999);

        DB::table('password_otps')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'otp' => $otp,
                'expires_at' => Carbon::now()->addMinutes(3),
                'verified_at' => null,
                'updated_at' => Carbon::now(),
            ]
        );

        // Queue email
        Mail::to($user->email)->queue(new PasswordOtpMail($otp));

        return response()->json([
            'status' => true,
            'message' => 'OTP sent to your email'
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        $otpRecord = DB::table('password_otps')
                        ->where('user_id', $user->id)
                        ->first();

        if (! $otpRecord || $otpRecord->otp != $request->otp) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP'
            ], 400);
        }

        if (Carbon::now()->gt(Carbon::parse($otpRecord->expires_at))) {
            return response()->json([
                'status' => false,
                'message' => 'OTP expired'
            ], 400);
        }

        DB::table('password_otps')
            ->where('user_id', $user->id)
            ->update([
                'verified_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

        return response()->json([
            'status' => true,
            'message' => 'OTP verified successfully'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        $otpRecord = DB::table('password_otps')
                        ->where('user_id', $user->id)
                        ->first();

        if (! $otpRecord || ! $otpRecord->verified_at || Carbon::now()->gt(Carbon::parse($otpRecord->expires_at))) {
            return response()->json([
                'status' => false,
                'message' => 'OTP not verified or expired'
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        DB::table('password_otps')->where('user_id', $user->id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully'
        ]);
    }

}
