<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Category;
use App\Models\LoginAttempt;
use App\Models\Transaction;
use App\Models\Goal;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class AdminController extends Controller
{
    public function __construct()
    {
        // Defense-in-depth: ensure all admin actions require authenticated admins
        $this->middleware(['auth', 'can:admin']);
    }

    public function index()
    {
        $stats = [
            'users' => User::query()->count(),
            'categories' => Category::query()->count(),
            'transactions' => Transaction::query()->count(),
            'goals' => Goal::query()->count(),
        ];

        $health = [
            'activeUsers7d' => LoginAttempt::query()->where('success', true)->whereDate('created_at', '>=', now()->subDays(7))->distinct('user_id')->count('user_id'),
            'failedLogins24h' => LoginAttempt::query()->where('success', false)->where('created_at', '>=', now()->subDay())->count(),
            'newUsers24h' => User::query()->where('created_at', '>=', now()->subDay())->count(),
            'tx24h' => Transaction::query()->where('created_at', '>=', now()->subDay())->count(),
        ];

        $latestUsers = User::query()->latest()->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'health', 'latestUsers'));
    }

    public function users(Request $request)
    {
        $query = User::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $role = $request->get('role');
            if ($role === 'admin') {
                $query->where('is_admin', true);
            } elseif ($role === 'user') {
                $query->where('is_admin', false);
            }
        }

        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->get('to'));
        }

        $counts = [
            'total' => User::query()->count(),
            'active' => User::query()->where('is_active', true)->count(),
            'inactive' => User::query()->where('is_active', false)->count(),
            'admins' => User::query()->where('is_admin', true)->count(),
        ];

        $users = $query->latest()->paginate(15)->withQueryString();
        return view('admin.users', compact('users', 'counts'));
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

    public function categories(Request $request)
    {
        $query = Category::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        $categories = $query->latest()->paginate(20)->withQueryString();
        return view('admin.categories', compact('categories'));
    }

    public function destroyCategory(Category $category)
    {
        // Safety check: Don't delete if it has transactions? 
        // Or just allow deletion and let DB handle foreign key constraints (restrict).
        try {
            $category->delete();
            return back()->with('status', 'تم حذف الفئة بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', 'لا يمكن حذف هذه الفئة لأنها مرتبطة بمعاملات موجودة.');
        }
    }
}
