@extends('layouts.app')

@section('content')
    <style>
        /* primary-gradient button now provided globally via app.scss */
        .goal-ring { width: 140px; height: 140px; border-radius: 50%; display: grid; place-items: center; background: conic-gradient(var(--gold,#c9a227) var(--pct,0%), #e5e7eb 0); margin-inline: auto; }
        .goal-ring-inner { width: 105px; height: 105px; border-radius: 50%; background: var(--card-bg,#fff); display: grid; place-items: center; box-shadow: inset 0 0 0 2px rgba(201,162,39,0.25); }
    </style>

    <div class="form-hero" dir="rtl">
        <div class="form-card">
            <div class="accent-bar"></div>
            <div class="card-body space-y-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="p-2 rounded-circle" style="background: rgba(201,162,39,0.15); color: #c9a227;">
                        <i class="bi bi-bullseye fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">تعديل هدف</h5>
                        <div class="form-sub">عدّل أرقام الهدف وتاريخ الانتهاء بسهولة.</div>
                    </div>
                </div>

    <form action="{{ route('goals.update', $goal) }}" method="POST" class="space-y-4" novalidate>
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">اسم الهدف</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $goal->name) }}" required>
                @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">المبلغ المستهدف</label>
                <input type="number" step="0.01" name="target_amount" class="form-control" value="{{ old('target_amount', $goal->target_amount) }}" required>
                @error('target_amount')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">المبلغ الحالي</label>
                <input type="number" step="0.01" name="current_amount" class="form-control" value="{{ old('current_amount', $goal->current_amount) }}" required>
                @error('current_amount')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">الموعد النهائي</label>
                <input type="date" name="deadline" class="form-control" value="{{ old('deadline', optional($goal->deadline)->toDateString()) }}">
                @error('deadline')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">الحالة</label>
                <input type="text" name="status" class="form-control" value="{{ old('status', $goal->status) }}" placeholder="مثال: جارٍ التقدم">
                @error('status')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 d-flex align-items-center justify-content-center">
                @php $t = old('target_amount', $goal->target_amount); $c = old('current_amount', $goal->current_amount); $pct = $t>0 ? max(0,min(100, ($c/$t)*100)) : 0; @endphp
                <div class="goal-ring" style="--pct: {{ round($pct) }}%">
                    <div class="goal-ring-inner">
                        <div class="text-center">
                            <div class="fw-bold">%<span id="goalPct">{{ round($pct) }}</span></div>
                            <small class="text-muted">نسبة التقدم</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2 pt-2">
            <a href="{{ route('goals.index') }}" class="btn btn-light flex-1">إلغاء</a>
            <button class="btn primary-gradient flex-1">تحديث الهدف</button>
        </div>
    </form>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tgt = document.querySelector('input[name="target_amount"]');
            const cur = document.querySelector('input[name="current_amount"]');
            const ring = document.querySelector('.goal-ring');
            const pctEl = document.getElementById('goalPct');
            const update = () => {
                const t = parseFloat(tgt.value || 0); const c = parseFloat(cur.value || 0);
                const pct = t > 0 ? Math.max(0, Math.min(100, (c / t) * 100)) : 0;
                ring.style.setProperty('--pct', pct + '%');
                pctEl.textContent = Math.round(pct);
            };
            tgt.addEventListener('input', update); cur.addEventListener('input', update);
            update();
        });
    </script>
    @endpush
@endsection