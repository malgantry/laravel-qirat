@extends('layouts.app')

@section('content')
    @php
        $selectedType = old('type', request('type', 'expense'));
        if (!in_array($selectedType, ['income','expense'], true)) { $selectedType = 'expense'; }
        $selectedCategoryId = old('category_id');
        $selectedCategoryName = old('category');
        $categories = $categories ?? collect();

        // Define preset palettes and icons for a premium feel (Unified)
        $catColors = [
            'طعام' => '#fb923c', 'تسوق' => '#a855f7', 'فواتير' => '#ef4444', 'ترفيه' => '#f472b6',
            'هاتف' => '#38bdf8', 'رياضة' => '#4ade80', 'تجميل' => '#ec4899', 'تعليم' => '#6366f1',
            'اجتماعي' => '#f59e0b', 'راتب' => '#10b981', 'مكافأة' => '#34d399', 'استثمار' => '#059669',
            'تحويل' => '#6366f1', 'مواصلات' => '#06b6d4', 'صحة' => '#f43f5e', 'هدايا' => '#f59e0b',
            'غير مصنف' => '#94a3b8'
        ];

        $catMap = [
            'طعام' => 'food', 'تسوق' => 'shopping', 'فواتير' => 'bills', 'ترفيه' => 'entertainment',
            'هاتف' => 'phone', 'رياضة' => 'sports', 'تجميل' => 'beauty', 'تعليم' => 'education',
            'اجتماعي' => 'social', 'راتب' => 'salary', 'مكافأة' => 'bonus', 'استثمار' => 'investment',
            'تحويل' => 'transfer', 'صحة' => 'health', 'مواصلات' => 'transport', 'هدايا' => 'gifts',
            'غير مصنف' => 'uncategorized'
        ];

         $fallbackIcons = [
            'طعام' => 'bi-egg-fried', 'تسوق' => 'bi-cart2', 'فواتير' => 'bi-receipt', 'ترفيه' => 'bi-controller',
            'هاتف' => 'bi-phone', 'رياضة' => 'bi-activity', 'تجميل' => 'bi-person-hearts', 'تعليم' => 'bi-journal-text',
            'اجتماعي' => 'bi-people', 'راتب' => 'bi-cash-coin', 'مكافأة' => 'bi-gift', 'استثمار' => 'bi-graph-up-arrow',
            'تحويل' => 'bi-arrow-left-right', 'مواصلات' => 'bi-bus-front', 'صحة' => 'bi-bandaid', 'هدايا' => 'bi-gift',
            'غير مصنف' => 'bi-question-circle'
        ];
        
        $expenseTiles = [];
        $incomeTiles = [];
        foreach($categories as $c) {
            $type = strtolower(trim($c->type ?? 'expense'));
            $target = $type === 'income' ? 'incomeTiles' : 'expenseTiles';
            ${$target}[] = [
                'id' => $c->id,
                'name' => $c->name,
                'icon' => $c->icon ?: ($fallbackIcons[$c->name] ?? ($target === 'incomeTiles' ? 'bi-cash-coin' : 'bi-basket')),
                'color' => $c->color ?: ($catColors[$c->name] ?? ($target === 'incomeTiles' ? '#10b981' : '#ef4444')),
            ];
        }
    @endphp

    <div class="max-w-2xl mx-auto animate-enter">
        <div class="card-premium p-6">
            <div class="text-center mb-6">
                <div class="w-16 h-16 mx-auto bg-gradient-to-tr from-amber-400 to-amber-600 rounded-2xl flex items-center justify-center text-white text-3xl shadow-lg mb-4">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <h2 class="text-2xl font-heading font-bold text-text-main" data-i18n="addNewTransaction">إضافة معاملة جديدة</h2>
            </div>
            
            <form action="{{ route('transactions.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Type Toggle -->
                <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl relative">
                    <button type="button" id="btn-expense" class="py-2.5 rounded-lg text-sm font-bold transition-all flex items-center justify-center gap-2 {{ $selectedType === 'expense' ? 'bg-white dark:bg-slate-700 shadow text-red-500' : 'text-text-muted hover:text-slate-700' }}">
                        <i class="bi bi-graph-down"></i> <span data-i18n="expense">مصروف</span>
                    </button>
                    <button type="button" id="btn-income" class="py-2.5 rounded-lg text-sm font-bold transition-all flex items-center justify-center gap-2 {{ $selectedType === 'income' ? 'bg-white dark:bg-slate-700 shadow text-green-500' : 'text-text-muted hover:text-slate-700' }}">
                         <i class="bi bi-graph-up"></i> <span data-i18n="income">دخل</span>
                    </button>
                    <input type="hidden" name="type" id="type" value="{{ $selectedType }}">
                </div>

                <!-- Amount -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="amount">المبلغ</label>
                    <div class="relative">
                        <input type="number" step="0.01" name="amount" class="input-premium text-center text-2xl font-bold pl-12 @error('amount') input-invalid @enderror" placeholder="0.00" value="{{ old('amount') }}" required min="0.01" max="99999999">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold" data-i18n="lydSymbol">د.ل</span>
                    </div>
                    @error('amount')
                        <div class="invalid-feedback-premium">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
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
                            <input type="checkbox" name="is_fixed" value="1" class="sr-only peer" {{ old('is_fixed') ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-[var(--gold-500)]"></div>
                        </label>
                    </div>
                </div>

                <!-- Savings Amount (Income only) -->
                <div id="savings-amount-container" class="{{ $selectedType === 'income' ? '' : 'hidden' }} mb-6">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="savingsAmount">مبلغ الادخار (اختياري)</label>
                    <div class="relative">
                        <input type="number" step="0.01" name="savings_amount" class="input-premium text-center text-xl font-bold pl-12 border-emerald-500/30 focus:border-emerald-500 @error('savings_amount') input-invalid @enderror" placeholder="0.00" value="{{ old('savings_amount') }}" min="0.01" max="99999999">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold" data-i18n="lydSymbol">د.ل</span>
                    </div>
                    @error('savings_amount')
                        <div class="invalid-feedback-premium">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                    <p class="text-[10px] text-slate-500 mt-1" data-i18n="savingsAmountNote">المبلغ الذي سيتم تحويله للهدف المختار. اتركه فارغاً لتحويل كامل المبلغ.</p>
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
                            <option value="{{ $goal->id }}" {{ old('goal_id') == $goal->id ? 'selected' : '' }}>
                                {{ $goal->name }} - (المتبقي: {{ number_format($remaining) }} د.ل)
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Selection -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300" data-i18n="category">الفئة</label>
                        <button type="button" id="btn-add-cat" class="text-xs font-bold text-[var(--gold-600)] hover:underline flex items-center gap-1">
                            <i class="bi bi-plus-circle"></i> <span data-i18n="newCategory">فئة جديدة</span>
                        </button>
                    </div>
                    
                    <input type="hidden" name="category_id" id="category_id_hidden" value="{{ $selectedCategoryId }}">
                    <input type="hidden" name="category" id="category_name_hidden" value="{{ $selectedCategoryName }}">

                    <div id="expense-grid" class="category-grid {{ $selectedType === 'expense' ? '' : 'hidden' }}">
                        @foreach($expenseTiles as $tile)
                            <div class="flex flex-col items-center">
                                <button type="button" class="category-btn {{ (string)$selectedCategoryId === (string)$tile['id'] ? 'active' : '' }}" 
                                        data-type="expense" data-id="{{ $tile['id'] }}" data-name="{{ $tile['name'] }}" style="--cat-color: {{ $tile['color'] }};">
                                    <i class="bi {{ $tile['icon'] }}"></i>
                                </button>
                                <span class="cat-label" data-i18n="{{ $catMap[$tile['name']] ?? 'uncategorized' }}">{{ $tile['name'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div id="income-grid" class="category-grid {{ $selectedType === 'income' ? '' : 'hidden' }}">
                        @foreach($incomeTiles as $tile)
                            <div class="flex flex-col items-center">
                                <button type="button" class="category-btn {{ (string)$selectedCategoryId === (string)$tile['id'] ? 'active' : '' }}" 
                                        data-type="income" data-id="{{ $tile['id'] }}" data-name="{{ $tile['name'] }}" style="--cat-color: {{ $tile['color'] }};">
                                    <i class="bi {{ $tile['icon'] }}"></i>
                                </button>
                                <span class="cat-label" data-i18n="{{ $catMap[$tile['name']] ?? 'uncategorized' }}">{{ $tile['name'] }}</span>
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

                <!-- Quick Add Category (Hidden by default) -->
                <div id="quick-cat" class="glass-panel p-4 rounded-xl hidden">
                    <h6 class="font-bold text-sm mb-3 text-slate-700" data-i18n="quickNewCategory">فئة جديدة سريعة</h6>
                    <div class="space-y-3">
                        <input type="text" id="qc-name" class="input-premium py-2" data-i18n-placeholder="categoryName" placeholder="اسم الفئة">
                        <div class="flex flex-wrap gap-2">
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

                <!-- Date & Note -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="date">التاريخ</label>
                        <input type="date" name="occurred_at" class="input-premium @error('occurred_at') input-invalid @enderror" value="{{ old('occurred_at', now()->toDateString()) }}" required max="{{ date('Y-m-d') }}">
                        @error('occurred_at')
                            <div class="invalid-feedback-premium">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="optionalNote">ملاحظة (اختياري)</label>
                        <input type="text" name="note" class="input-premium @error('note') input-invalid @enderror" data-i18n-placeholder="notePlaceholder" placeholder="تفاصيل إضافية..." value="{{ old('note') }}" maxlength="255">
                        @error('note')
                            <div class="invalid-feedback-premium">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="submit" class="btn-gold flex-1 text-center justify-center py-3 text-lg shadow-md" data-i18n="saveTransaction">حفظ المعاملة</button>
                    <a href="{{ route('transactions.index') }}" class="btn-soft px-6" data-i18n="cancel">إلغاء</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btnExpense = document.getElementById('btn-expense');
            const btnIncome = document.getElementById('btn-income');
            const typeInput = document.getElementById('type');
            const expenseGrid = document.getElementById('expense-grid');
            const incomeGrid = document.getElementById('income-grid');
            const goalSelection = document.getElementById('goal-selection');
            const hiddenId = document.getElementById('category_id_hidden');
            const hiddenName = document.getElementById('category_name_hidden');

            const savingsContainer = document.getElementById('savings-amount-container');
            const fixedExpenseContainer = document.getElementById('fixed-expense-container');

            const clearCategorySelection = () => {
                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                hiddenId.value = '';
                hiddenName.value = '';
            };

            const enforceCategoryVisibility = (type) => {
                const selector = `.category-btn[data-type="${type}"][data-id="${hiddenId.value}"]`;
                const matching = hiddenId.value ? document.querySelector(selector) : null;
                if (!matching) {
                    clearCategorySelection();
                }
            };

            const setType = (type) => {
                const oldType = typeInput.value;
                typeInput.value = type;
                if(type === 'income') {
                    btnIncome.classList.add('bg-white', 'dark:bg-slate-700', 'shadow', 'text-green-500');
                    btnIncome.classList.remove('text-text-muted');
                    btnExpense.classList.remove('bg-white', 'dark:bg-slate-700', 'shadow', 'text-red-500');
                    btnExpense.classList.add('text-text-muted');
                    incomeGrid.classList.remove('hidden');
                    expenseGrid.classList.add('hidden');
                    goalSelection.classList.remove('hidden');
                    savingsContainer.classList.remove('hidden');
                    fixedExpenseContainer.classList.add('hidden');
                } else {
                    btnExpense.classList.add('bg-white', 'dark:bg-slate-700', 'shadow', 'text-red-500');
                    btnExpense.classList.remove('text-text-muted');
                    btnIncome.classList.remove('bg-white', 'dark:bg-slate-700', 'shadow', 'text-green-500');
                    btnIncome.classList.add('text-text-muted');
                    expenseGrid.classList.remove('hidden');
                    incomeGrid.classList.add('hidden');
                    goalSelection.classList.add('hidden');
                    savingsContainer.classList.add('hidden');
                    fixedExpenseContainer.classList.remove('hidden');
                }
                
                // Reset selection if type changed
                if (oldType !== type) {
                    clearCategorySelection();
                }

                // Ensure no hidden cross-type category remains selected
                enforceCategoryVisibility(type);
            };

            btnExpense.addEventListener('click', () => setType('expense'));
            btnIncome.addEventListener('click', () => setType('income'));

            // Initialize view to enforce the right grid and clear mismatched category
            setType(typeInput.value);

            // Category Selection
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    hiddenId.value = btn.dataset.id;
                    hiddenName.value = btn.dataset.name;
                    checkAmount(); // Trigger check
                });
            });

            // AI Budget Check Logic
            const amountInput = document.getElementById('amount');
            const alertBox = document.getElementById('budget-alert');
            const alertMsg = document.getElementById('budget-alert-msg');
            let timer;

            function checkAmount() {
                const amount = amountInput.value;
                const catId = hiddenId.value;
                const type = typeInput.value;

                if (type !== 'expense' || !amount || !catId) {
                    if(alertBox) alertBox.classList.add('hidden');
                    return;
                }

                clearTimeout(timer);
                timer = setTimeout(() => {
                    fetch('{{ route("transactions.checkBudget") }}', {
                         method: 'POST',
                         headers: { 
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                         },
                         body: JSON.stringify({ amount, category_id: catId })
                    })
                    .then(r => r.json())
                    .then(d => {
                        if(d.status === 'warning' && alertBox) {
                            alertMsg.innerText = d.message;
                            alertBox.classList.remove('hidden');
                        } else if(alertBox) {
                            alertBox.classList.add('hidden');
                        }
                    })
                    .catch(e => console.log('Check budget skipped'));
                }, 600);
            }

            if(amountInput) amountInput.addEventListener('input', checkAmount);

            // Quick Category Logic
            const btnAddCat = document.getElementById('btn-add-cat');
            const quickCat = document.getElementById('quick-cat');
            const qcCancel = document.getElementById('qc-cancel');
            const qcSave = document.getElementById('qc-save');
            const qcIcon = document.getElementById('qc-icon');
            
            if(btnAddCat) btnAddCat.addEventListener('click', () => quickCat.classList.remove('hidden'));
            if(qcCancel) qcCancel.addEventListener('click', () => quickCat.classList.add('hidden'));
            
            document.querySelectorAll('.icon-pick').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.icon-pick').forEach(b => b.classList.remove('border-amber-400', 'bg-amber-50'));
                    btn.classList.add('border-amber-400', 'bg-amber-50');
                    qcIcon.value = btn.dataset.icon;
                });
            });

            if(qcSave) {
                qcSave.addEventListener('click', async () => {
                    const name = document.getElementById('qc-name').value;
                    const icon = qcIcon.value;
                    const type = typeInput.value;
                    if(!name) return;

                    // Simple AJAX post to create category (simulated or real route)
                    const token = document.querySelector('meta[name="csrf-token"]').content;
                    try {
                        const res = await fetch('{{ route('categories.quickStore') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                            body: JSON.stringify({ name, icon, type })
                        });
                        if(res.ok) {
                            const cat = await res.json();
                            // Append new category to grid
                            const grid = type === 'income' ? incomeGrid : expenseGrid;
                            const wrapper = document.createElement('div');
                            wrapper.className = 'flex flex-col items-center animate-enter';
                            wrapper.innerHTML = `
                                <button type="button" class="category-btn active" data-id="${cat.id}" data-name="${cat.name}" style="--cat-color: #22c55e;">
                                    <i class="bi ${cat.icon || 'bi-hash'}"></i>
                                </button>
                                <span class="cat-label">${cat.name}</span>
                            `;
                            // Deselect others and select this new one
                            document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                            grid.appendChild(wrapper);
                            hiddenId.value = cat.id; hiddenName.value = cat.name;
                            
                            // Re-bind click
                            wrapper.querySelector('button').addEventListener('click', function() {
                                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                                this.classList.add('active');
                                hiddenId.value = this.dataset.id; hiddenName.value = this.dataset.name;
                            });

                            quickCat.classList.add('hidden');
                            document.getElementById('qc-name').value = '';
                        }
                    } catch(e) { console.error(e); alert('Error creating category'); }
                });
            }

            // Double Submission Protection
            const form = document.querySelector('form');
            const submitBtn = document.querySelector('button[type="submit"]');
            if(form && submitBtn) {
                form.addEventListener('submit', function() {
                    // Only disable if form is valid (HTML5 validation)
                    if(form.checkValidity()) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
                        const originalText = submitBtn.innerText;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> جارِ الحفظ...';
                        
                        // Safety timeout in case of server error/network hang (5 seconds)
                        setTimeout(() => {
                            submitBtn.disabled = false;
                            submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                            submitBtn.innerHTML = originalText;
                        }, 5000);
                    }
                });
            }

            // --- AI Auto-Classify Logic ---
            const noteInput = document.querySelector('input[name="note"]');
            let classifyTimer;
            if (noteInput) {
                noteInput.addEventListener('input', () => {
                    clearTimeout(classifyTimer);
                    classifyTimer = setTimeout(() => {
                        const desc = noteInput.value.trim();
                        if (desc.length < 3) return; 

                        console.log('Classifying (debounced):', desc);
                    
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
                            if (btn) {
                                // Auto-select with animation
                                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                                btn.classList.add('active');
                                hiddenId.value = btn.dataset.id;
                                hiddenName.value = btn.dataset.name;
                                
                                // Visual Cue (Magic Effect)
                                const originalTransform = btn.style.transform;
                                btn.style.transition = 'all 0.5s ease';
                                btn.style.transform = 'scale(1.2) rotate(10deg)';
                                btn.style.boxShadow = '0 0 20px var(--gold-500)';
                                
                                const icon = btn.querySelector('i');
                                const originalIconClass = icon.className;
                                icon.className = 'bi bi-stars text-white'; // Change icon momentarily
                                
                                setTimeout(() => {
                                    btn.style.transform = originalTransform || 'scale(1.05)'; // Return to active state scale
                                    btn.style.boxShadow = '';
                                    icon.className = originalIconClass;
                                    // Flash message
                                    
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
