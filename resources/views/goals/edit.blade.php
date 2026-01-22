@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto animate-enter">
        <div class="card-premium p-6">
            <div class="text-center mb-6">
                <div class="w-16 h-16 mx-auto bg-gradient-to-tr from-amber-400 to-amber-600 rounded-2xl flex items-center justify-center text-white text-3xl shadow-lg mb-4">
                    <i class="bi bi-bullseye"></i>
                </div>
                <h2 class="text-2xl font-heading font-bold text-text-main" data-i18n="editGoalTitle">تعديل هدف</h2>
                <p class="text-text-muted mt-2 text-sm" data-i18n="goalSettingInfo">عدّل أرقام الهدف وتاريخ الانتهاء بسهولة.</p>
            </div>

            <form action="{{ route('goals.update', $goal) }}" method="POST" class="space-y-6" novalidate>
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column: Inputs -->
                    <div class="space-y-4">
                        <div>
                             <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="goalName">اسم الهدف</label>
                             <input type="text" name="name" class="input-premium @error('name') input-invalid @enderror" value="{{ old('name', $goal->name) }}" required minlength="3" maxlength="120" data-i18n-placeholder="goalNamePlaceholder" placeholder="مثلاً: شراء سيارة">
                             @error('name')
                                <div class="invalid-feedback-premium">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    <span>{{ $message }}</span>
                                </div>
                             @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="targetAmount">المبلغ المستهدف</label>
                            <div class="relative">
                                <input type="number" step="0.01" name="target_amount" class="input-premium pl-12 @error('target_amount') input-invalid @enderror" value="{{ old('target_amount', $goal->target_amount) }}" required min="1" max="99999999" placeholder="0.00">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold" data-i18n="lydSymbol">د.ل</span>
                            </div>
                            @error('target_amount')
                                <div class="invalid-feedback-premium">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
 
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="currentAmount">المبلغ الحالي</label>
                             <div class="relative">
                                <input type="number" step="0.01" name="current_amount" class="input-premium pl-12 @error('current_amount') input-invalid @enderror" value="{{ old('current_amount', $goal->current_amount) }}" required min="0" max="99999999" placeholder="0.00">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold" data-i18n="lydSymbol">د.ل</span>
                             </div>
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
                    
                    <!-- Right Column: Visual & Dates -->
                    <div class="space-y-4 flex flex-col justify-center">
                         <!-- Visual Ring -->
                        <div class="py-4">
                             @php 
                                $t = old('target_amount', $goal->target_amount); 
                                $c = old('current_amount', $goal->current_amount); 
                                $pct = $t>0 ? max(0,min(100, ($c/$t)*100)) : 0; 
                            @endphp
                            <div class="goal-ring-premium transition-all duration-700 ease-out" style="--pct: {{ round($pct) }}%">
                                <div class="goal-ring-premium-inner">
                                    <span class="text-2xl font-bold text-slate-800 dark:text-white" id="goalPct">{{ round($pct) }}%</span>
                                    <span class="text-xs text-text-muted font-bold" data-i18n="achievement">منجز</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4">
                             <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="deadline">الموعد النهائي</label>
                                <input type="date" name="deadline" class="input-premium @error('deadline') input-invalid @enderror" value="{{ old('deadline', optional($goal->deadline)->toDateString()) }}" min="{{ date('Y-m-d') }}">
                                @error('deadline')
                                    <div class="invalid-feedback-premium">
                                        <i class="bi bi-exclamation-circle-fill"></i>
                                        <span>{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                    <button type="submit" id="submit-btn" class="btn-gold flex-1 text-center justify-center py-3 text-lg shadow-md" data-i18n="saveGoal">تحديث الهدف</button>
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
            const submitBtn = document.getElementById('submit-btn');
            
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
            
            if(tgt && cur && ring) {
                tgt.addEventListener('input', update); 
                cur.addEventListener('input', update);
                update();
            }
        });
    </script>
    @endpush
@endsection