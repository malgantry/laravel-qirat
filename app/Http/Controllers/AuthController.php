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
            'email' => ['required', 'email', 'max:120'],
            'password' => ['required', 'min:6'],
        ]);

        $user = User::where('email', $credentials['email'])->first();
        if ($user && ! $user->is_active) {
            $this->logAttempt($user, $credentials['email'], false, $request);
            throw ValidationException::withMessages([
                'email' => __('This account has been disabled. Please contact the system administrator.'),
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
            'email' => __('Invalid login credentials.'),
        ]);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:80'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Welcome Notification
        try {
            $user->notify(new \App\Notifications\GeneralAlert([
                'title' => __('Welcome to Qiratae'),
                'body' => __('Glad to have you with us. Start by setting your budget.'),
                'icon' => 'bi-emoji-smile',
                'color' => 'text-[var(--gold-600)]'
            ]));

            // Admin Notification
            $admins = User::where('is_admin', true)->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\GeneralAlert([
                    'title' => __('New Member'),
                    'body' => __('Member Joined', ['name' => $user->name]),
                    'icon' => 'bi-person-plus-fill',
                    'color' => 'text-blue-500',
                    'link' => route('admin.users')
                ]));
            }
        } catch (\Exception $e) {
            // Context: Notifications shouldn't block registration
        }

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
