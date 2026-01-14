<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Category;
use App\Models\LoginAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'users' => User::query()->count(),
            'categories' => Category::query()->count(),
        ];

        $latestUsers = User::query()->latest()->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'latestUsers'));
    }

    public function users()
    {
        $users = User::query()->latest()->paginate(15);
        return view('admin.users', compact('users'));
    }

    public function toggleActive(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->withErrors(['action' => 'لا يمكنك تعطيل حسابك الشخصي.']);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with('status', $user->is_active ? 'تم تفعيل المستخدم.' : 'تم تعطيل المستخدم.');
    }

    public function toggleAdmin(User $user)
    {
        if (auth()->id() === $user->id && $user->is_admin) {
            return back()->withErrors(['action' => 'لا يمكنك إزالة صلاحيات المدير عن نفسك.']);
        }

        $user->is_admin = ! $user->is_admin;
        $user->save();

        return back()->with('status', $user->is_admin ? 'تم منح صلاحية مدير النظام.' : 'تم إزالة صلاحية مدير النظام.');
    }

    public function sendReset(User $user)
    {
        Password::sendResetLink(['email' => $user->email]);
        return back()->with('status', 'تم إرسال رابط إعادة التعيين إلى بريد المستخدم.');
    }

    public function loginAttempts()
    {
        $attempts = LoginAttempt::query()->latest('created_at')->paginate(25);
        return view('admin.login-attempts', compact('attempts'));
    }

    public function categories()
    {
        $categories = Category::query()->latest()->paginate(20);
        return view('admin.categories', compact('categories'));
    }
}
