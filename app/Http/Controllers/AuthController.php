<?php

namespace App\Http\Controllers;

use App\Models\EmailVerification;
use App\Models\User;
use App\Helpers\AppHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

    public function login(Request $request)
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

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors([
                'email' => __($status),
            ]);
    }

    public function forgotPasswordGetEmailOtp(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'nama' => 'required',
        ]);

        $email = $request->email;
        $check = User::where('email', $email)->first();
        if ($check) {
            return response()->json([
                'status' => false,
                'message' => 'Email Tidak Terdaftar.'
            ], 403);
        }

        $otp = AppHelper::generateOTP(6);

        try {
            Mail::to($email)->send(new \App\Mail\SendOTPMail($request->nama, $otp, $email));
        } catch (\Throwable $th) {
            Log::info("=== GAGAL KIRIM EMAIL ===");
            Log::info($th);

            $response = Http::withHeaders([
                'api-key' => env('BREVO_API_KEY'),
                'accept' => 'application/json',
                'content-type' => 'application/json'
            ])->post(env('BREVO_API_URL'), [
                'sender' => [
                    'name' => env('APP_NAME'),
                    'email' => env('MAIL_FROM_ADDRESS')
                ],
                'to' => [
                    ['name' => $request->nama, 'email' => $email]
                ],
                'subject' => 'Verifikasi Email - OTP',
                'htmlContent' => view('email.otp', ['nama' => $request->nama, 'email' => $email, 'otp' => $otp])->render()
            ]);
        }

        $verif = EmailVerification::where('email', $email)->first();

        if (!$verif) {
            $verif = EmailVerification::create([
                'email' => $email,
                'otp' => $otp,
                'otp_expired_at' => Carbon::now()->addMinutes(2),
            ]);
        } else {
            $verif->otp = $otp;
            $verif->otp_expired_at = Carbon::now()->addMinutes(2);
            $verif->update();
        }

        return response()->json([
            'status' => true,
            'resend_time' => 30,
            'email' => $email,
            'message' => 'Berhasil mengirim Kode OTP.',
        ], 200);
    }
}
