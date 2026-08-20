<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'The current password you entered is incorrect.',
            'password.confirmed' => 'The new password and confirmation do not match.',
            'password.min' => 'The new password must be at least 8 characters long.',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->back()->with('success', 'Your password has been updated successfully!');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.exists' => 'No user account was found with that email address.',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        // Generate 6-digit OTP
        $otp = sprintf('%06d', mt_rand(100000, 999999));

        // Save OTP to password_reset_tokens
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => \Illuminate\Support\Facades\Hash::make($otp),
                'created_at' => now(),
            ]
        );

        // Send Email
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\PasswordResetOtpMail($otp, $user));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Password reset OTP email failed: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Unable to send OTP email at this time. Please check your mail settings.'])->withInput();
        }

        return redirect()->route('password.otp', ['email' => $request->email])
            ->with('success', 'A 6-digit verification code (OTP) has been sent to your email address.');
    }

    public function showOtpForm(Request $request)
    {
        return view('auth.reset-password-otp', [
            'email' => $request->query('email', '')
        ]);
    }

    public function resetPasswordWithOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'otp' => ['required', 'numeric', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.exists' => 'No user account found with this email.',
            'otp.required' => 'Please enter the 6-digit OTP code sent to your email.',
            'otp.digits' => 'The OTP code must be exactly 6 digits.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'Your new password must be at least 8 characters long.',
        ]);

        $tokenRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$tokenRecord) {
            return back()->withErrors(['otp' => 'No password reset request found or the request has expired. Please request a new OTP.'])->withInput();
        }

        // Check 15 minute expiration
        if (now()->diffInMinutes($tokenRecord->created_at) > 15) {
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['otp' => 'The OTP code has expired. Please request a new OTP.'])->withInput();
        }

        // Check OTP match
        if (!\Illuminate\Support\Facades\Hash::check($request->otp, $tokenRecord->token) && $request->otp !== $tokenRecord->token) {
            return back()->withErrors(['otp' => 'The 6-digit OTP code entered is incorrect.'])->withInput();
        }

        // Update User Password
        $user = \App\Models\User::where('email', $request->email)->first();
        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $user->save();

        // Delete token record
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Your password has been reset successfully! You can now log in with your new password.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
