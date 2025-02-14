<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Email tidak terdaftar dalam sistem.'
            ], 404);
        }

        try {
            $otpCode = sprintf("%06d", mt_rand(1, 999999));

            $user->otp = $otpCode;
            $user->token_expired_at = now()->addMinutes(3);
            $user->save();

            Mail::send([], [], function ($message) use ($request, $otpCode) {
                $message
                    ->to($request->email)
                    ->subject('Kode OTP Reset Password')
                    ->html("
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
                    </html>");
            });

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
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $validatedData = $validator->validated();


        if (User::where('email', $validatedData['email'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email telah digunakan'
            ], 400);
        }

        if (User::where('phone', $validatedData['phone'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor telepon telah digunakan'
            ], 400);
        }

        if (User::where('email', $request->email)
            ->where('is_verified', true)
            ->orWhere('phone', $request->phone)
            ->where('is_verified', true)
            ->exists()
        ) {
            return response()->json(['status' => false, 'message' => 'Email atau nomor telepon telah terdaftar'], 422);
        }

        User::where('email', $request->email)
            ->where('is_verified', false)
            ->delete();

        $otpCode = sprintf("%06d", mt_rand(1, 999999));

        User::create([
            'uuid' => Str::uuid(),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => bcrypt($request->password),
            'otp' => $otpCode,
            'is_verified' => false,
            'token_expired_at' => now()->addMinutes(15)
        ]);
        Mail::send([], [], function ($message) use ($request, $otpCode) {
            $message->to($request->email);
            $message->subject('Kode OTP registrasi');
            $message->html("<html>
                        <body>
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
                        </html>");
        });

        return response()->json([
            'status' => true,
            'message' => 'Kode OTP telah dikirim ke email Anda'
        ], 200);
    }

    public function resendUserOtpRegist(Request $request)
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

        $user = User::where('email', $request->email)
            ->where('is_verified', false)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Data registrasi tidak ditemukan, silahkan untuk melakukan registrasi ulang'
            ], 404);
        }

        $otpCode = sprintf("%06d", mt_rand(1, 999999));
        $user->otp = $otpCode;
        $user->token_expired_at = now()->addMinutes(15);
        $user->save();


        Mail::send([], [], function ($message) use ($user, $otpCode) {
            $message->to($user->email);
            $message->subject('Kode OTP registrasi');
            $message->html("<html>
                        <body>
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
                        </html>");
        });
        return response()->json([
            'status' => true,
            'message' => 'Kode OTP telah dikirim ulang ke email Anda'
        ], 200);
    }

    public function verifyUserOtpRegist(Request $request)
    {
        $user = User::where('email', $request->email)
            ->where('is_verified', false)
            ->first();

        if (!$user || $user->otp != $request->otp || $user->token_expired_at < now()) {
            if ($user && $user->token_expired_at < now()) {
                $user->delete();
            }
            return response()->json(['status' => false, 'message' => 'Kode OTP tidak valid atau telah kadaluarsa'], 422);
        }

        $user->is_verified = true;
        $user->otp = null;
        $user->token_expired_at = null;
        $user->save();

        return response()->json(['status' => true, 'message' => 'Registrasi berhasil', 'user' => $user]);
    }
}
