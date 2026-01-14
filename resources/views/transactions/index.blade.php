@extends('layouts.app')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h3 class="text-xl font-bold text-slate-900 dark:text-slate-50">المعاملات</h3>
        <a href="{{ route('transactions.create') }}" class="inline-flex items-center gap-2 btn btn-primary shadow-sm">
            <i class="bi bi-plus-circle"></i>
            <span>معاملة جديدة</span>
        </a>
    </div>

    <form method="GET" action="{{ route('transactions.index') }}" class="space-y-3">
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative flex-1 min-w-[260px] max-w-xl">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><i class="bi bi-search"></i></span>
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 pr-3 pl-10 py-2 text-sm text-slate-800 dark:text-slate-100 shadow-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    placeholder="بحث في الوصف أو الفئة"
                >
            </div>
            @if(request('q') || request('type'))
                <a href="{{ route('transactions.index') }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm font-semibold text-slate-700 dark:text-slate-100 shadow-sm transition hover:bg-slate-50 dark:hover:bg-slate-800">
                    <i class="bi bi-x-circle"></i>
                    <span>مسح</span>
                </a>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            @php $q = request('q'); @endphp
            <a href="{{ route('transactions.index') }}"
               class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition {{ request('type') ? 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-100 hover:border-slate-300 dark:hover:border-slate-600' : 'border-[var(--brand-start)]/30 bg-[var(--brand-soft)] text-[var(--text-primary)] shadow-sm' }}">
                <i class="bi bi-funnel"></i> الكل
            </a>
            <a href="{{ route('transactions.index', ['type' => 'income'] + ($q ? ['q' => $q] : [])) }}"
               class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition {{ request('type')==='income' ? 'border-[var(--brand-start)]/30 bg-[var(--brand-soft)] text-[var(--text-primary)] shadow-sm' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-100 hover:border-slate-300 dark:hover:border-slate-600' }}">
                <i class="bi bi-graph-up-arrow"></i> الدخل
            </a>
            <a href="{{ route('transactions.index', ['type' => 'expense'] + ($q ? ['q' => $q] : [])) }}"
               class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition {{ request('type')==='expense' ? 'border-[var(--danger)]/30 bg-[var(--danger-soft)] text-[var(--text-primary)] shadow-sm' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-100 hover:border-slate-300 dark:hover:border-slate-600' }}">
                <i class="bi bi-graph-down"></i> المصروفات
            </a>
        </div>
    </form>

    @if($transactions->isEmpty())
        <div class="flex flex-col items-center gap-2 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-slate-900/80 px-6 py-8 text-center shadow-md">
            <i class="bi bi-stars text-2xl" style="color: var(--brand-start);"></i>
            <h5 class="text-lg font-bold text-slate-900 dark:text-slate-100">لا توجد معاملات بعد</h5>
            <p class="mb-2 text-sm text-slate-500 dark:text-slate-400">ابدأ بإضافة الفئات الأساسية ثم سجّل أول عملية دخل أو مصروف.</p>
            <div class="flex flex-wrap justify-center gap-2">
                <a href="{{ route('transactions.create') }}" class="inline-flex items-center gap-2 btn btn-primary shadow-sm">إضافة معاملة</a>
                <a href="{{ route('budgets.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-100 shadow-sm transition hover:bg-slate-50 dark:hover:bg-slate-800">عرض الميزانيات</a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            @foreach($transactions as $transaction)
                @php
                    $cat = $transaction->categoryRef;
                    $icon = $cat?->icon;
                    $isIncome = $transaction->type === 'income';
                    $avatarBg = $isIncome
                        ? 'linear-gradient(135deg, var(--brand-start), var(--brand-mid))'
                        : 'linear-gradient(135deg, var(--danger), #b94500)';
                @endphp
                <div>
                    <div class="list-card flex flex-col gap-3 sm:flex-row sm:items-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                        <div class="avatar-icon" style="background: {{ $avatarBg }};">
                            <i class="bi {{ $icon ?? ($isIncome ? 'bi-cash-coin' : 'bi-receipt') }}"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-base font-bold text-slate-900 dark:text-slate-100">{{ $cat?->name ?? $transaction->category }}</div>
                                <div class="pill-badge {{ $isIncome ? 'pill-income' : 'pill-expense' }}">
                                    {{ $isIncome ? 'دخل' : 'مصروف' }}
                                </div>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2 text-sm text-slate-700 dark:text-slate-300">
                                <span class="chip"><i class="bi bi-calendar-event"></i> {{ optional($transaction->occurred_at)->toDateString() }}</span>
                                <span class="chip"><i class="bi bi-wallet2"></i> {{ number_format($transaction->amount, 2) }} د.ل</span>
                                @if($transaction->note)
                                    <span class="chip"><i class="bi bi-chat"></i> {{ $transaction->note }}</span>
                                @endif
                            </div>
                            @php $fb = $feedback[$transaction->id] ?? null; @endphp
                            @if($fb)
                                <div class="mt-2 rounded-lg border border-dashed px-3 py-2 text-sm {{ $fb['type']==='warning' ? 'border-red-300' : ($fb['type']==='success' ? 'border-green-300' : 'border-blue-300') }}">
                                    <i class="bi {{ $fb['type']==='warning' ? 'bi-exclamation-triangle' : ($fb['type']==='success' ? 'bi-check-circle' : 'bi-lightbulb') }}"></i>
                                    <span class="ms-2">{{ $fb['message'] }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('transactions.edit', $transaction) }}" class="inline-flex items-center rounded-md border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-sm font-semibold text-slate-700 dark:text-slate-100 shadow-sm transition hover:bg-slate-50 dark:hover:bg-slate-800">تعديل</a>
                            <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex items-center rounded-md border border-red-200 dark:border-red-500/40 px-3 py-1.5 text-sm font-semibold text-red-600 dark:text-red-300 shadow-sm transition hover:bg-red-50 dark:hover:bg-red-500/10" onclick="return confirm('تأكيد حذف المعاملة؟');">حذف</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    @endif
@endsection