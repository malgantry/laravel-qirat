@extends('layouts.app')

@section('content')
    <style>
        .goal-shell { background: var(--card-bg); box-shadow: var(--shadow-soft); border-radius: 24px; }
        .goal-inner { border: 1px solid var(--card-border); border-radius: 18px; padding: 24px; background: var(--card-bg); box-shadow: inset 0 1px 0 rgba(255,255,255,0.06); }
        .goal-ring { position: relative; width: 140px; height: 140px; border-radius: 9999px; background: conic-gradient(var(--brand-start) calc(var(--p) * 1%), var(--card-border) 0deg); display: grid; place-items: center; }
        .goal-ring::after { content: ""; position: absolute; inset: 16px; border-radius: 9999px; background: var(--card-bg); box-shadow: inset 0 0 0 1px var(--card-border); z-index: 0; }
        .goal-ring .ring-value { position: relative; z-index: 1; font-weight: 800; color: var(--brand-start); font-size: 1.35rem; display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; }
        .goal-label { color: var(--text-secondary); font-weight: 600; }
        .goal-value { color: var(--text-primary); font-weight: 700; }
        .goal-remaining { color: #f97316; font-weight: 800; }
        .ai-feedback { border: 1px dashed var(--card-border); border-radius: 12px; padding: 10px; background: var(--card-bg); text-align: start; }
        .ai-feedback.warning { border-color: #ef4444; }
        .ai-feedback.info { border-color: #3b82f6; }
        .ai-feedback.success { border-color: #10b981; }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ url()->previous() }}" class="text-primary text-decoration-none d-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i>
            <span>عرض الكل</span>
        </a>
        <div class="d-flex align-items-center gap-2">
            <h3 class="mb-0 text-slate-900 dark:text-slate-50">الأهداف النشطة</h3>
            <a href="{{ route('goals.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1"><i class="bi bi-plus-circle"></i> هدف جديد</a>
        </div>
    </div>

    @if($goals->isEmpty())
        <div class="empty-state card-soft text-center p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <div class="mb-2"><i class="bi bi-bullseye" style="color: var(--brand-start);"></i></div>
            <div class="fw-bold mb-1 text-slate-900 dark:text-slate-100">لا توجد أهداف بعد</div>
            <div class="text-muted mb-3">ابدأ بإضافة هدف ادخار أو شراء وسيظهر تقدمك هنا بشكل أنيق.</div>
            <a href="{{ route('goals.create') }}" class="btn btn-primary">إنشاء هدف</a>
        </div>
    @else
        <div class="row g-3">
            @foreach($goals as $goal)
                @php
                    $progress = max(0, min(100, (int)$goal->progress));
                    $remaining = max(0, ($goal->target_amount ?? 0) - ($goal->current_amount ?? 0));
                @endphp
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="goal-shell h-100 p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                        <div class="goal-inner text-center">
                            <div class="fw-bold text-lg text-slate-900 dark:text-slate-100 mb-3">{{ $goal->name }}</div>
                            <div class="d-flex flex-column align-items-center gap-3">
                                <div class="goal-ring" style="--p: {{ $progress }};">
                                    <div class="ring-value">{{ $progress }}%</div>
                                </div>
                                <div class="w-100" dir="rtl">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="goal-label">المبلغ الحالي:</span>
                                        <span class="goal-value">${{ number_format($goal->current_amount, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="goal-label">المبلغ المستهدف:</span>
                                        <span class="goal-value">${{ number_format($goal->target_amount, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="goal-label">المتبقي:</span>
                                        <span class="goal-remaining">${{ number_format($remaining, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                @php $items = $feedback[$goal->id] ?? []; @endphp
                                @if(!empty($items))
                                    @php $item = $items[0]; @endphp
                                    <div class="ai-feedback {{ $item['type'] }} mb-2 d-flex align-items-center gap-2">
                                        <i class="bi {{ $item['type']==='warning' ? 'bi-exclamation-triangle' : ($item['type']==='success' ? 'bi-check-circle' : 'bi-lightbulb') }}" style="color: var(--brand-start);"></i>
                                        <div class="flex-1">{{ $item['message'] }}</div>
                                        @if(!empty($item['action']))
                                            <a href="{{ route('goals.edit', $goal) }}" class="btn btn-sm btn-outline-primary">{{ $item['action'] }}</a>
                                        @endif
                                    </div>
                                @endif
                                <a href="{{ route('goals.edit', $goal) }}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                                    تعديل <i class="bi bi-pencil-square"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3">
            {{ $goals->links() }}
        </div>
    @endif
@endsection