@extends('layouts.app')

@section('content')
    <div class="max-w-xl mx-auto animate-enter">
        <div class="card-premium p-8">
            <div class="text-center mb-8">
                <div class="w-16 h-16 mx-auto bg-gradient-to-tr from-amber-400 to-amber-600 rounded-2xl flex items-center justify-center text-white text-3xl shadow-lg mb-4">
                    <i class="bi bi-bullseye"></i>
                </div>
                <h2 class="text-2xl font-heading font-bold text-text-main" data-i18n="addNewGoal">إضافة هدف جديد</h2>
                <p class="text-text-muted mt-2 text-sm" data-i18n="goalSettingInfo">حدد هدفاً مالياً واضحاً لتسعى لتحقيقه.</p>
            </div>

            <form action="{{ route('goals.store') }}" method="POST" class="space-y-6" novalidate>
                @csrf
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="goalName">اسم الهدف</label>
                    <input type="text" name="name" class="input-premium @error('name') input-invalid @enderror" data-i18n-placeholder="goalNamePlaceholder" placeholder="مثلاً: شراء سيارة" value="{{ old('name') }}" required minlength="3" maxlength="120">
                    @error('name')
                        <div class="invalid-feedback-premium">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="targetAmount">المبلغ المستهدف</label>
                        <input type="number" step="0.01" name="target_amount" class="input-premium @error('target_amount') input-invalid @enderror" value="{{ old('target_amount') }}" required min="1" max="99999999" placeholder="0.00">
                        @error('target_amount')
                            <div class="invalid-feedback-premium">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="currentAmount">المبلغ الحالي</label>
                        <input type="number" step="0.01" name="current_amount" class="input-premium @error('current_amount') input-invalid @enderror" value="{{ old('current_amount', 0) }}" required min="0" max="99999999" placeholder="0.00">
                        <div id="current-amount-error" class="invalid-feedback-premium hidden">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <span data-i18n="currentAmountError">المبلغ الحالي لا يمكن أن يتجاوز المبلغ المستهدف</span>
                        </div>
                        @error('current_amount')
                            <div class="invalid-feedback-premium">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="deadline">الموعد النهائي</label>
                        <input type="date" name="deadline" class="input-premium @error('deadline') input-invalid @enderror" value="{{ old('deadline') }}" min="{{ date('Y-m-d') }}">
                        @error('deadline')
                            <div class="invalid-feedback-premium">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Preview Ring -->
                <div class="flex justify-center py-4">
                     <div class="goal-ring-premium transition-all duration-700 ease-out" style="--pct: 0%">
                        <div class="goal-ring-premium-inner">
                            <span class="text-2xl font-bold font-heading text-[var(--gold-600)]" id="goalPct">0%</span>
                            <span class="text-[10px] text-muted font-bold uppercase" data-i18n="achievement">إنجاز</span>
                        </div>
                     </div>
                </div>

                <div class="flex gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="submit" class="btn-gold flex-1 text-center justify-center py-3 text-lg shadow-md" data-i18n="saveGoal">حفظ الهدف</button>
                    <a href="{{ route('goals.index') }}" class="btn-soft px-6" data-i18n="cancel">إلغاء</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tgt = document.querySelector('input[name="target_amount"]');
            const cur = document.querySelector('input[name="current_amount"]');
            const ring = document.querySelector('.goal-ring-premium');
            const pctEl = document.getElementById('goalPct');
            const errorEl = document.getElementById('current-amount-error');
            const submitBtn = document.querySelector('button[type="submit"]');

            const update = () => {
                const t = parseFloat(tgt.value || 0); 
                const c = parseFloat(cur.value || 0);
                
                if (c > t && t > 0) {
                    errorEl.classList.remove('hidden');
                    errorEl.classList.add('is-visible');
                    cur.classList.add('input-invalid');
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.5';
                } else {
                    errorEl.classList.add('hidden');
                    errorEl.classList.remove('is-visible');
                    cur.classList.remove('input-invalid');
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                }

                const pct = t > 0 ? Math.max(0, Math.min(100, (c / t) * 100)) : 0;
                ring.style.setProperty('--pct', pct + '%');
                pctEl.textContent = Math.round(pct) + '%';
            };
            tgt.addEventListener('input', update); 
            cur.addEventListener('input', update);
            update();
        });
    </script>
    @endpush
@endsection
