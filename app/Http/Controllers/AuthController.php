<?php

namespace App\Http\Controllers;

use App\Models\EmailVerification;
use App\Models\User;
use App\Helpers\AppHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function me()
    {
        return response()->json([
            'user' => auth()->user()
        ]);
    }

    public function loginWeb(Request $request)
    {
        $validator = Validator::make($request->post(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json([
                'status' => false,
                'message' => 'Email / Password salah!'
            ], 401);
        }

        if (!auth()->user()->hasRole('admin')) {
            // Logout jika bukan admin
            auth()->logout();

            return response()->json([
                'status' => false,
                'message' => 'Anda tidak memiliki akses untuk login!'
            ], 403);
        }

        return response()->json([
            'status' => true,
            'user' => auth()->user(),
            'token' => $token
        ]);
    }

    public function loginMobile(Request $request)
    {
        $validator = Validator::make($request->post(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json([
                'status' => false,
                'message' => 'Email / Password salah!'
            ], 401);
        }

        return response()->json([
            'status' => true,
            'user' => auth()->user(),
            'token' => $token
        ]);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['success' => true]);
    }

    public function sendUserOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Check if user exists
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Email tidak terdaftar dalam sistem.'
            ], 404);
        }

        try {

            // Generate OTP code (6 digits)
            $otpCode = sprintf("%06d", mt_rand(1, 999999));

            // Save OTP to user record
            $user->otp = $otpCode;
            $user->token_expired_at = now()->addMinutes(3);
            $user->save();

            // Send email using Brevo/Sendinblue
            $response = Http::withHeaders([
                'api-key' => env('SENDINBLUE_API_KEY'),
                "Content-Type" => "application/json"
            ])->post('https://api.brevo.com/v3/smtp/email', [
                "sender" => [
                    "name" => env('SENDINBLUE_SENDER_NAME'),
                    "email" => env('SENDINBLUE_SENDER_EMAIL'),
                ],
                'to' => [
                    ['email' => $request->email]
                ],
                "subject" => "Kode OTP Reset Password",
                "htmlContent" => "
            <html>
            <body>
                <h1>Kode OTP Reset Password</h1>
                <p>Anda telah meminta untuk mereset password akun Anda.</p>
                <p>Kode OTP Anda adalah:</p>
                <h2 style='font-size: 24px; 
                          background-color: #f0f0f0; 
                          padding: 10px; 
                          text-align: center; 
                          letter-spacing: 5px;'>
                    {$otpCode}
                </h2>
                <p>Kode OTP ini akan kadaluarsa dalam 15 menit.</p>
                <p>Jangan bagikan kode ini kepada siapapun.</p>
                <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
            </body>
            </html>",
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Kode OTP telah dikirim ke email Anda',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error sending OTP: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengirim kode OTP',
            ], 500);
        }
    }

    public function verifyUserOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = User::where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('token_expired_at', '>', now())
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Kode OTP tidak valid atau sudah kadaluarsa'
            ], 400);
        }

        $user->email_verified_at = now();
        $user->otp = null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Verifikasi OTP berhasil'
        ]);
    }

    public function resetUserPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak ditemukan.'
            ], 404);
        }

        // Check if email is verified
        if (!$user->email_verified_at) {
            return response()->json([
                'status' => false,
                'message' => 'Email belum diverifikasi. Silakan verifikasi OTP terlebih dahulu.'
            ], 403);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password berhasil direset.'
        ]);
    }

    public function sendUserOtpRegist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'name' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Generate OTP code (6 digits)
        $otpCode = sprintf("%06d", mt_rand(1, 999999));

        // Save OTP to user record
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            $user = new User();
            $user->email = $request->email;
            $user->name = $request->name;
            $user->phone = $request->phone;
            $user->address = $request->address;
            $user->password = bcrypt($request->password);
            $user->otp = $otpCode;
            $user->token_expired_at = now()->addMinutes(3);
            $user->save();
        } else {
            // Update existing user
            $user->name = $request->name;
            $user->phone = $request->phone;
            $user->address = $request->address;
            $user->password = bcrypt($request->password);
            $user->otp = $otpCode;
            $user->token_expired_at = now()->addMinutes(3);
            $user->save();
        }

        // Send email using Brevo/Sendinblue
        $response = Http::withHeaders([
            'api-key' => env('SENDINBLUE_API_KEY'),
            "Content-Type" => "application/json"
        ])->post('https://api.brevo.com/v3/smtp/email', [
            "sender" => [
                "name" => env('SENDINBLUE_SENDER_NAME'),
                "email" => env('SENDINBLUE_SENDER_EMAIL'),
            ],
            'to' => [
                ['email' => $request->email]
            ],
            "subject" => "Kode OTP Reset Password",
            "htmlContent" => "
            <html>
            <body>
                <h1>Kode OTP Reset Password</h1>
                <p>Anda telah meminta untuk mereset password akun Anda.</p>
                <p>Kode OTP Anda adalah:</p>
                <h2 style='font-size: 24px; 
                          background-color: #f0f0f0; 
                          padding: 10px; 
                          text-align: center; 
                          letter-spacing: 5px;'>
                    {$otpCode}
                </h2>
                <p>Kode OTP ini akan kadaluarsa dalam 15 menit.</p>
                <p>Jangan bagikan kode ini kepada siapapun.</p>
                <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
            </body>
            </html>",
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Kode OTP telah dikirim ke email Anda',
        ], 200);
    }

    public function verufyUserOtpRegist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak ditemukan.'
            ], 404);
        }

        if ($user->otp != $request->otp) {
            return response()->json([
                'status' => false,
                'message' => 'Kode OTP tidak sesuai.'
            ], 422);
        }

        if ($user->token_expired_at < now()) {
            return response()->json([
                'status' => false,
                'message' => 'Kode OTP telah kadaluarsa.'
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Kode OTP berhasil diverifikasi.',
            'user' => $user
        ], 200);
    }
}
