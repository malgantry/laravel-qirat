@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0 text-slate-900 dark:text-slate-50">الميزانيات</h3>
        <a href="{{ route('budgets.create') }}" class="btn btn-primary">ميزانية جديدة</a>
    </div>

    @if($budgets->isEmpty())
        <div class="empty-state card-soft text-center p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <i class="bi bi-wallet2 mb-2" style="color: var(--brand-start);"></i>
            <h5 class="fw-bold text-slate-900 dark:text-slate-50">لا توجد ميزانيات بعد</h5>
            <p class="text-muted mb-3">أضف فئة ثم حدد حد شهري لمراقبة المصروف.</p>
            <a href="{{ route('budgets.create') }}" class="btn btn-primary">إنشاء ميزانية</a>
        </div>
    @else
        <div class="row g-3">
            @foreach($budgets as $budget)
                @php
                    $cat = $budget->category;
                    $icon = $cat?->icon;
                    $limit = (float) ($budget->limit_amount ?? 0);
                    $spent = (float) ($budget->spent_amount ?? 0);
                    $pct = $limit > 0 ? min(100, round(($spent / $limit) * 100)) : 0;
                @endphp
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card-soft p-3 h-100 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-icon" style="background: linear-gradient(135deg, var(--brand-start), var(--brand-mid));">
                                <i class="bi {{ $icon ?? 'bi-wallet2' }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-bold text-slate-900 dark:text-slate-50">{{ $cat?->name ?? '—' }}</div>
                                    <span class="chip"><i class="bi bi-flag"></i> الحد {{ number_format($limit, 2) }} د.ل</span>
                                </div>
                                <div class="text-muted mt-1"><i class="bi bi-calendar-range"></i> {{ optional($budget->period_start)->toDateString() }} → {{ optional($budget->period_end)->toDateString() }}</div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted">المصروف: {{ number_format($spent, 2) }} د.ل</small>
                                <small class="text-muted">{{ $pct }}%</small>
                            </div>
                            <div class="progress-soft">
                                <div style="height:10px; width: {{ $pct }}%; background: {{ $pct >= 90 ? 'var(--danger)' : 'var(--brand-start)' }}; border-radius:999px;"></div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <span class="badge-soft {{ ($budget->status === 'active') ? 'income' : 'expense' }}">{{ $budget->status ?? '—' }}</span>
                            <a href="{{ route('budgets.edit', $budget) }}" class="btn btn-sm btn-outline-secondary">تعديل</a>
                            <form action="{{ route('budgets.destroy', $budget) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('تأكيد حذف الميزانية؟');">حذف</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3">
            {{ $budgets->links() }}
        </div>
    @endif
@endsection
