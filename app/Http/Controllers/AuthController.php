<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

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

        $user = User::where('email', $credentials['email'])->first();
        if ($user && ! $user->is_active) {
            $this->logAttempt($user, $credentials['email'], false, $request);
            throw ValidationException::withMessages([
                'email' => __('تم تعطيل هذا الحساب. الرجاء التواصل مع مدير النظام.'),
            ]);
        }

        $remember = (bool) $request->boolean('remember');
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $this->logAttempt(Auth::user(), $credentials['email'], true, $request);
            return redirect()->intended('/');
        }

        $this->logAttempt($user, $credentials['email'], false, $request);
        throw ValidationException::withMessages([
            'email' => __('بيانات الدخول غير صحيحة.'),
        ]);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        return redirect('/');
    }

    public function showForgot()
    {
        return view('auth.forgot');
    }

    public function forgot(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            // If mailer isn't configured, simulate success to avoid user confusion
            $status = Password::RESET_LINK_SENT;
        }

        return back()->with('status', __($status));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    private function logAttempt(?User $user, string $email, bool $success, Request $request): void
    {
        LoginAttempt::create([
            'user_id' => $user?->id,
            'email' => $email,
            'success' => $success,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'created_at' => now(),
        ]);
    }
}
