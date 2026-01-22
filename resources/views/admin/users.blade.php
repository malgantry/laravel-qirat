@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto space-y-8 animate-enter">
        <!-- Users Management Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 text-[var(--gold-500)] font-black text-[10px] uppercase tracking-[0.2em] mb-2" data-i18n="userAuthEngine">
                    <span class="w-8 h-px bg-[var(--gold-400)]"></span>
                    <span data-i18n="userAuthEngine">محرك التراخيص والوصول</span>
                </div>
                <h3 class="text-4xl font-heading font-black text-text-main tracking-tight" data-i18n="userRegistry">سجل المستخدمين</h3>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.dashboard') }}" class="btn-soft px-8 py-3 text-sm font-bold shadow-xl border border-slate-200/50 dark:border-slate-800/50">
                    <i class="bi bi-grid-fill me-2"></i> <span data-i18n="adminDashboard">لوحة الإدارة</span>
                </a>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" action="{{ route('admin.users') }}" class="card-premium p-5 border-none shadow-xl space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="col-span-1 md:col-span-2">
                    <label class="text-xs font-black text-slate-500 mb-1 block">بحث</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="input-premium" placeholder="الاسم أو البريد">
                </div>
                <div>
                    <label class="text-xs font-black text-slate-500 mb-1 block">الدور</label>
                    <select name="role" class="input-premium">
                        <option value="">الكل</option>
                        <option value="admin" @selected(request('role')==='admin')>مدير</option>
                        <option value="user" @selected(request('role')==='user')>مستخدم</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-black text-slate-500 mb-1 block">الحالة</label>
                    <select name="status" class="input-premium">
                        <option value="">الكل</option>
                        <option value="active" @selected(request('status')==='active')>مفعل</option>
                        <option value="inactive" @selected(request('status')==='inactive')>معطل</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-black text-slate-500 mb-1 block">من تاريخ</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="input-premium">
                </div>
                <div>
                    <label class="text-xs font-black text-slate-500 mb-1 block">إلى تاريخ</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="input-premium">
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="btn-gold px-6 py-2 text-sm font-black">تطبيق الفلاتر</button>
                <a href="{{ route('admin.users') }}" class="btn-soft text-sm">إعادة تعيين</a>
                <div class="flex flex-wrap gap-2 text-[10px] font-black text-slate-500">
                    <span class="px-2 py-1 bg-slate-100 rounded-lg">إجمالي: {{ $counts['total'] ?? 0 }}</span>
                    <span class="px-2 py-1 bg-emerald-50 text-emerald-700 rounded-lg">مفعل: {{ $counts['active'] ?? 0 }}</span>
                    <span class="px-2 py-1 bg-slate-200 text-slate-700 rounded-lg">معطل: {{ $counts['inactive'] ?? 0 }}</span>
                    <span class="px-2 py-1 bg-indigo-50 text-indigo-700 rounded-lg">مديرون: {{ $counts['admins'] ?? 0 }}</span>
                </div>
            </div>
        </form>

        <!-- Users Table Container -->
            <div class="overflow-x-auto">
                <table class="table-premium">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-950/20">
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-[var(--border-light)]" data-i18n="accountData">بيانات الحساب</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-[var(--border-light)]" data-i18n="roleAndRank">الرتبة والدور</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-[var(--border-light)]" data-i18n="operationStatus">الحالة التشغيلية</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-[var(--border-light)]" data-i18n="joinDate">تاريخ الانضمام</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-[var(--border-light)] text-center" data-i18n="sovereignOps">العمليات السيادية</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                            <tr class="group hover:bg-[var(--gold-50)]/30 dark:hover:bg-[var(--gold-900)]/5 transition-colors border-b border-[var(--border-light)] last:border-0">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-2xl bg-[var(--gold-50)] dark:bg-[var(--gold-900)]/20 text-[var(--gold-600)] flex items-center justify-center font-heading font-black text-sm shadow-inner border border-[var(--gold-100)]/30">
                                            {{ mb_substr($u->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-heading font-black text-slate-800 dark:text-white text-sm tracking-tight">{{ $u->name }}</div>
                                            <div class="text-xs text-slate-400 font-medium">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    @if($u->is_admin)
                                        <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-slate-900 dark:bg-white text-white dark:text-black text-[10px] font-black uppercase tracking-widest shadow-lg">
                                            <i class="bi bi-shield-check-fill me-2"></i> <span data-i18n="systemAdmin">مدير النظام</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-[10px] font-black uppercase tracking-widest border border-blue-100 dark:border-blue-900/30">
                                            <i class="bi bi-person-fill me-2"></i> <span data-i18n="clientUser">مستخدم عميل</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-8 py-6">
                                    @if($u->is_active)
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <span class="text-xs font-black text-emerald-600 uppercase tracking-widest" data-i18n="activeStatus">مفعل</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                            <span class="text-xs font-black text-slate-400 uppercase tracking-widest" data-i18n="disabledStatus">معطل</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-tighter">{{ optional($u->created_at)->format('Y/m/d') }}</div>
                                    <div class="text-[9px] text-slate-400 font-bold mt-0.5">{{ optional($u->created_at)->format('H:i') }}</div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <form method="POST" action="{{ route('admin.users.toggleActive', $u) }}" onsubmit="return confirm(i18n[currentLang()].deleteConfirm);">
                                            @csrf
                                            <button type="submit" class="btn-soft px-4 py-2 text-[10px] font-black uppercase tracking-widest {{ $u->is_active ? 'text-rose-600' : 'text-emerald-600' }}">
                                                <span data-i18n="{{ $u->is_active ? 'blockAccess' : 'grantAccess' }}">{{ $u->is_active ? 'حظر الوصول' : 'إطلاق الوصول' }}</span>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.toggleAdmin', $u) }}" onsubmit="return confirm(i18n[currentLang()].deleteConfirm);">
                                            @csrf
                                            <button type="submit" class="btn-soft px-4 py-2 text-[10px] font-black uppercase tracking-widest border-slate-200 dark:border-slate-700">
                                                <span data-i18n="{{ $u->is_admin ? 'stripAdmin' : 'promoteAdmin' }}">{{ $u->is_admin ? 'تجريد الإدارة' : 'ترقية لإداري' }}</span>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.reset', $u) }}" onsubmit="return confirm(i18n[currentLang()].deleteConfirm);">
                                            @csrf
                                            <button type="submit" class="btn-gold px-4 py-2 text-[10px] font-black uppercase tracking-widest">
                                                <span data-i18n="resetPin">تصفير الرقم</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center mt-12 pb-12">
            {{ $users->links() }}
        </div>
    </div>
@endsection
