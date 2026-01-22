@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto animate-enter">
        <div class="card-premium p-6">
            <div class="text-center mb-6">
                <div class="w-16 h-16 mx-auto bg-gradient-to-tr from-amber-400 to-amber-600 rounded-2xl flex items-center justify-center text-white text-3xl shadow-lg mb-4">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <h2 class="text-2xl font-heading font-bold text-text-main" data-i18n="editTransactionTitle">تعديل معاملة</h2>
                <p class="text-text-muted mt-2 text-sm" data-i18n="updateDataInfo">حدث البيانات مع المحافظة على نوع الفئة.</p>
            </div>

            @php
                $categories = $categories ?? collect();
                $catColors = [
                    'طعام' => '#fb923c', 'تسوق' => '#a855f7', 'فواتير' => '#ef4444', 'ترفيه' => '#f472b6',
                    'هاتف' => '#38bdf8', 'رياضة' => '#4ade80', 'تجميل' => '#ec4899', 'تعليم' => '#6366f1',
                    'اجتماعي' => '#f59e0b', 'راتب' => '#10b981', 'مكافأة' => '#34d399', 'استثمار' => '#059669',
                    'تحويل' => '#6366f1', 'مواصلات' => '#06b6d4', 'صحة' => '#f43f5e', 'هدايا' => '#f59e0b',
                    'غير مصنف' => '#94a3b8'
                ];

                $expenseTiles = $expenseTiles ?? [];
                $incomeTiles = $incomeTiles ?? [];

                if (empty($expenseTiles) && empty($incomeTiles)) {
                    foreach ($categories as $c) {
                        $type = strtolower(trim($c->type ?? 'expense'));
                        $target = $type === 'income' ? 'incomeTiles' : 'expenseTiles';
                        ${$target}[] = [
                            'id' => $c->id,
                            'name' => $c->name,
                            'icon' => $c->icon ?: 'bi-basket',
                            'color' => $c->color ?: ($catColors[$c->name] ?? ($target === 'incomeTiles' ? '#10b981' : '#ef4444')),
                        ];
                    }
                }

                $selectedType = old('type', $transaction->type);
                $selectedCategoryId = old('category_id', $transaction->category_id);
                $selectedGoalId = old('goal_id', $transaction->goal_id);
            @endphp
            
                    <input type="hidden" name="type" value="{{ $selectedType }}" id="type-input">
                </div>
                @error('type')<p class="text-red-500 text-xs text-center mt-1">{{ $message }}</p>@enderror

                <!-- Amount & Date -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                         <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="amount">المبلغ</label>
                         <div class="relative">
                            <input type="number" step="0.01" name="amount" class="input-premium text-center text-2xl font-bold pl-12 @error('amount') input-invalid @enderror" placeholder="0.00" value="{{ old('amount', $transaction->amount) }}" required min="0.01" max="99999999">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold" data-i18n="lydSymbol">د.ل</span>
                         </div>
                         @error('amount')
                            <div class="invalid-feedback-premium">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span>{{ $message }}</span>
                            </div>
                         @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="date">التاريخ</label>
                        <input type="date" name="occurred_at" class="input-premium text-center @error('occurred_at') input-invalid @enderror" value="{{ old('occurred_at', optional($transaction->occurred_at)->toDateString()) }}" required max="{{ date('Y-m-d') }}">
                        @error('occurred_at')
                            <div class="invalid-feedback-premium">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Fixed Expense Toggle (Expense only) -->
                <div id="fixed-expense-container" class="{{ $selectedType === 'expense' ? '' : 'hidden' }} glass-panel p-4 rounded-2xl border-rose-500/10">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/20 text-rose-500 flex items-center justify-center text-lg shadow-inner">
                                <i class="bi bi-pin-angle-fill"></i>
                            </div>
                            <div>
                                <h6 class="text-sm font-bold text-slate-800 dark:text-slate-200" data-i18n="fixedExpense">مصروف ثابت؟</h6>
                                <p class="text-[10px] text-slate-500" data-i18n="fixedExpenseDesc">التزامات حتمية مثل الإيجار والفواتير.</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_fixed" value="1" class="sr-only peer" {{ old('is_fixed', $transaction->is_fixed) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-[var(--gold-500)]"></div>
                        </label>
                    </div>
                </div>

                <!-- Goal Selection (Income only) -->
                <div id="goal-selection" class="{{ $selectedType === 'income' ? '' : 'hidden' }} glass-panel p-4 rounded-2xl border-emerald-500/20">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                        <i class="bi bi-bullseye text-emerald-500"></i> <span data-i18n="savingDestination">وجهة الادخار (اختياري)</span>
                    </label>
                    <select name="goal_id" class="input-premium py-2 text-sm">
                        <option value="">-- <span data-i18n="noGoal">بدون هدف</span> --</option>
                        @foreach($goals ?? [] as $goal)
                            @php
                                $remaining = max(0, $goal->target_amount - $goal->current_amount);
                            @endphp
                            <option value="{{ $goal->id }}" {{ $selectedGoalId == $goal->id ? 'selected' : '' }}>
                                {{ $goal->name }} - (المتبقي: {{ number_format($remaining) }} د.ل)
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-slate-500 mt-2" data-i18n="savingDestinationNote">سيتم تحديث رصيد الهدف تلقائياً عند تغيير هذا الحقل.</p>
                </div>

                <!-- Categories -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                         <label class="block text-sm font-bold text-slate-700 dark:text-slate-300" data-i18n="category">الفئة</label>
                         <button type="button" id="btn-add-cat" class="text-xs font-bold text-[var(--gold-600)] hover:underline flex items-center gap-1">
                            <i class="bi bi-plus-circle"></i> <span data-i18n="newCategory">فئة جديدة</span>
                        </button>
                    </div>
                    <input type="hidden" name="category_id" id="category_id_hidden" value="{{ $selectedCategoryId }}">

                    <!-- Expense Grid -->
                    <div id="expense-grid" class="category-grid {{ $selectedType === 'expense' ? '' : 'hidden' }}">
                        @foreach($expenseCats as $cat)
                            <div class="flex flex-col items-center">
                                <button type="button" class="category-btn {{ (string)$selectedCategoryId === (string)$cat->id ? 'active' : '' }}" 
                                        data-type="expense" data-id="{{ $cat->id }}" data-name="{{ $cat->name }}" style="--cat-color: {{ $cat->color ?: ($catColors[$cat->name] ?? '#ef4444') }};">
                                    <i class="bi {{ $cat->icon ?: 'bi-basket' }}"></i>
                                </button>
                                <span class="cat-label" data-i18n="{{ $catMap[$cat->name] ?? 'uncategorized' }}">{{ $cat->name }}</span>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Income Grid -->
                    <div id="income-grid" class="category-grid {{ $selectedType === 'income' ? '' : 'hidden' }}">
                        @foreach($incomeCats as $cat)
                            <div class="flex flex-col items-center">
                                <button type="button" class="category-btn {{ (string)$selectedCategoryId === (string)$cat->id ? 'active' : '' }}" 
                                        data-type="income" data-id="{{ $cat->id }}" data-name="{{ $cat->name }}" style="--cat-color: {{ $cat->color ?: ($catColors[$cat->name] ?? '#10b981') }};">
                                    <i class="bi {{ $cat->icon ?: 'bi-cash-coin' }}"></i>
                                </button>
                                <span class="cat-label" data-i18n="{{ $catMap[$cat->name] ?? 'uncategorized' }}">{{ $cat->name }}</span>
                            </div>
                        @endforeach
                    </div>
                     @error('category_id')
                        <div class="invalid-feedback-premium">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <span>{{ $message }}</span>
                        </div>
                     @enderror
                </div>
                
                 <!-- Quick Add Category (Hidden) -->
                <div id="quick-cat" class="glass-panel p-4 rounded-xl hidden">
                     <h6 class="font-bold text-sm mb-3 text-slate-700" data-i18n="quickNewCategory">فئة جديدة سريعة</h6>
                     <div class="space-y-3">
                        <input type="text" id="qc-name" class="input-premium py-2" data-i18n-placeholder="categoryName" placeholder="اسم الفئة">
                        <div class="flex flex-wrap gap-2">
                             @php($icons = ['bi-egg-fried','bi-bag','bi-car-front','bi-gift','bi-receipt','bi-mortarboard','bi-heart','bi-basket','bi-cash-coin','bi-wallet2'])
                             @foreach($icons as $ic)
                                <button type="button" class="w-8 h-8 rounded-lg flex items-center justify-center border border-slate-200 hover:border-amber-400 icon-pick" data-icon="{{ $ic }}">
                                    <i class="bi {{ $ic }}"></i>
                                </button>
                            @endforeach
                            <input type="hidden" id="qc-icon">
                        </div>
                         <div class="flex gap-2">
                            <button type="button" id="qc-save" class="btn-gold flex-1 py-1.5 text-sm" data-i18n="save">حفظ</button>
                            <button type="button" id="qc-cancel" class="btn-soft flex-1 py-1.5 text-sm" data-i18n="cancel">إلغاء</button>
                        </div>
                     </div>
                </div>

                <!-- Note -->
                <div>
                     <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="note">ملاحظة</label>
                     <textarea name="note" class="input-premium @error('note') input-invalid @enderror" rows="2" data-i18n-placeholder="notePlaceholder" placeholder="تفاصيل إضافية (اختياري)" maxlength="255">{{ old('note', $transaction->note) }}</textarea>
                     @error('note')
                        <div class="invalid-feedback-premium">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <span>{{ $message }}</span>
                        </div>
                     @enderror
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="submit" class="btn-gold flex-1 text-center justify-center py-3 text-lg shadow-md" data-i18n="updateTransaction">تحديث المعاملة</button>
                    <a href="{{ route('transactions.index') }}" class="btn-soft px-6" data-i18n="cancel">إلغاء</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
             // Type Toggle
            const btnExpense = document.getElementById('btn-expense');
            const btnIncome = document.getElementById('btn-income');
            const slider = document.getElementById('slider');
            const typeInput = document.getElementById('type-input');
            const expenseGrid = document.getElementById('expense-grid');
            const incomeGrid = document.getElementById('income-grid');
            
            // Initial State for Slider
            const setType = (type) => {
                if(type === 'expense') {
                     // For RTL context, we want expense (right) to be selected.
                     // Slider needs to be at right: 0 or left: unset?
                     // Let's rely on simple class switching for transforms if possible but our CSS handles dir?
                     // We manually adjust slider position.
                     if (document.dir === 'rtl') {
                        slider.style.transform = 'translateX(0)';
                     } else {
                        slider.style.transform = 'translateX(0)'; // This assumes left-to-right logic for transform.
                     }
                }
            };
            
            const updateSlider = (type) => {
                 const oldType = typeInput.value;
                 // We will just use 'right' / 'left' styles to be clearer than translateX in mixed envs
                 if (type === 'expense') {
                     slider.style.right = '0.375rem'; 
                     slider.style.left = '50%';
                     btnExpense.classList.replace('text-text-muted', 'text-red-600');
                     btnIncome.classList.replace('text-green-600', 'text-text-muted');
                     expenseGrid.classList.remove('hidden');
                     incomeGrid.classList.add('hidden');
                 } else {
                     slider.style.right = '50%';
                     slider.style.left = '0.375rem';
                     btnIncome.classList.replace('text-text-muted', 'text-green-600');
                     btnExpense.classList.replace('text-red-600', 'text-text-muted');
                     incomeGrid.classList.remove('hidden');
                     expenseGrid.classList.add('hidden');
                 }
                 typeInput.value = type;

                 // Reset category selection if type actually changes
                 if (oldType !== type) {
                    document.querySelectorAll('.category-btn').forEach(t => t.classList.remove('active'));
                    secretInput.value = '';
                 }
            };

             updateSlider(typeInput.value);
            btnExpense.addEventListener('click', () => {
                updateSlider('expense');
                document.getElementById('goal-selection').classList.add('hidden');
            });
            btnIncome.addEventListener('click', () => {
                updateSlider('income');
                document.getElementById('goal-selection').classList.remove('hidden');
            });

            // Category Selection
            const secretInput = document.getElementById('category_id_hidden');
            const tiles = document.querySelectorAll('.category-btn');
            
             tiles.forEach(tile => {
                tile.addEventListener('click', (e) => {
                    e.preventDefault();
                    tiles.forEach(t => t.classList.remove('active'));
                    tile.classList.add('active');
                    secretInput.value = tile.dataset.id;
                });
            });
            
            // Quick Cat
            const btnAdd = document.getElementById('btn-add-cat');
            const quickPanel = document.getElementById('quick-cat');
            const qcCancel = document.getElementById('qc-cancel');
            const qcSave = document.getElementById('qc-save');
            const qcName = document.getElementById('qc-name');
            const qcIcon = document.getElementById('qc-icon');
            
            if(btnAdd) btnAdd.addEventListener('click', () => quickPanel.classList.remove('hidden'));
            if(qcCancel) qcCancel.addEventListener('click', () => {
                quickPanel.classList.add('hidden');
                qcName.value = '';
                document.querySelectorAll('.icon-pick').forEach(b => b.classList.remove('border-amber-400', 'bg-amber-50'));
            });
            
             document.querySelectorAll('.icon-pick').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.icon-pick').forEach(b => b.classList.remove('border-amber-400', 'bg-amber-50'));
                    btn.classList.add('border-amber-400', 'bg-amber-50');
                    qcIcon.value = btn.dataset.icon;
                });
            });
            
             if(qcSave) {
                qcSave.addEventListener('click', async () => {
                    const name = qcName.value.trim();
                    const icon = qcIcon.value;
                    const type = typeInput.value;
                    if(!name) return;
                    
                     const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                     try {
                        const res = await fetch('{{ route('categories.quickStore') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                            body: JSON.stringify({ name, icon, type })
                        });
                        
                        if(res.ok) {
                             const cat = await res.json();
                             const targetGrid = type === 'expense' ? expenseGrid : incomeGrid;
                             const div = document.createElement('div');
                             div.className = 'flex flex-col items-center animate-enter';
                             div.innerHTML = `
                                <button type="button" class="category-btn active" data-id="${cat.id}" style="--cat-color: ${type==='income'?'#10b981':'#ef4444'}">
                                    <i class="bi ${cat.icon || 'bi-basket'}"></i>
                                </button>
                                <span class="cat-label">${cat.name}</span>
                             `;
                             tiles.forEach(t => t.classList.remove('active'));
                             targetGrid.prepend(div);
                             secretInput.value = cat.id;
                             
                             // Bind click
                             div.querySelector('button').addEventListener('click', function(e) {
                                  e.preventDefault();
                                  document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                                  this.classList.add('active');
                                  secretInput.value = this.dataset.id;
                             });
                             
                             quickPanel.classList.add('hidden');
                             qcName.value = '';
                        }
                     } catch(e) {}
                });
            }

            // --- AI Auto-Classify Logic ---
            const noteInput = document.querySelector('textarea[name="note"]');
            let classifyTimer;
            if (noteInput) {
                noteInput.addEventListener('input', () => {
                    clearTimeout(classifyTimer);
                    classifyTimer = setTimeout(() => {
                        const desc = noteInput.value.trim();
                        if (desc.length < 3) return;

                        fetch('{{ route("ai.classify") }}', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ description: desc })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success' && data.category_id) {
                            const btn = document.querySelector(`.category-btn[data-id="${data.category_id}"]`);
                            if (btn && !btn.classList.contains('active')) {
                                // Auto-select with animation
                                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                                btn.classList.add('active');
                                secretInput.value = btn.dataset.id;
                                
                                // Visual Cue (Magic Effect)
                                const originalTransform = btn.style.transform;
                                btn.style.transition = 'all 0.5s ease';
                                btn.style.transform = 'scale(1.2) rotate(10deg)';
                                btn.style.boxShadow = '0 0 20px var(--gold-500)';
                                
                                const icon = btn.querySelector('i');
                                const originalIconClass = icon.className;
                                icon.className = 'bi bi-stars text-white'; 
                                
                                setTimeout(() => {
                                    btn.style.transform = originalTransform || 'scale(1.05)';
                                    btn.style.boxShadow = '';
                                    icon.className = originalIconClass;
                                }, 800);
                            }
                        }
                    })
                    .catch(err => console.log('AI Classify silent fail'));
                    }, 500);
                });
            }
        });
    </script>
    @endpush
@endsection