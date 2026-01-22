@extends('layouts.app')

@section('content')
    @php
        $expenseCats = ($categories ?? collect())->where('type', 'expense');
        $selectedCategoryId = old('category_id');
        
        // Premium Icons & Palettes (Unified Standard)
        $fallbackIcons = [
            'طعام' => 'bi-egg-fried', 'تسوق' => 'bi-cart2', 'فواتير' => 'bi-receipt', 'ترفيه' => 'bi-controller',
            'هاتف' => 'bi-phone', 'رياضة' => 'bi-activity', 'تجميل' => 'bi-person-hearts', 'تعليم' => 'bi-journal-text',
            'اجتماعي' => 'bi-people', 'راتب' => 'bi-cash-coin', 'مكافأة' => 'bi-gift', 'استثمار' => 'bi-graph-up-arrow',
            'تحويل' => 'bi-arrow-left-right', 'مواصلات' => 'bi-bus-front', 'صحة' => 'bi-bandaid', 'هدايا' => 'bi-gift',
            'غير مصنف' => 'bi-question-circle'
        ];
        $palette = [
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
    @endphp

            <form action="{{ route('budgets.store') }}" method="POST" class="space-y-6" novalidate>
                @csrf

                <!-- Category Selection -->
                <div>
                     <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300" data-i18n="selectCategory">اختر الفئة</label>
                        <button type="button" id="btn-add-cat" class="text-xs font-bold text-[var(--gold-600)] hover:underline flex items-center gap-1">
                            <i class="bi bi-plus-circle"></i> <span data-i18n="newCategory">فئة جديدة</span>
                        </button>
                    </div>
                    
                    <input type="hidden" name="category_id" id="category_id_hidden" value="{{ $selectedCategoryId }}">
                    
                    <div class="category-grid">
                        @foreach($expenseCats as $cat)
                            @php
                                $icon = $cat->icon ?: ($fallbackIcons[$cat->name] ?? 'bi-basket');
                                $col = $palette[$cat->name] ?? ($cat->color ?? '#ef4444');
                            @endphp
                            <div class="flex flex-col items-center">
                                <button type="button" class="category-btn {{ (string)$selectedCategoryId === (string)$cat->id ? 'active' : '' }}" 
                                        data-id="{{ $cat->id }}" data-name="{{ $cat->name }}" style="--cat-color: {{ $col }};">
                                    <i class="bi {{ $icon }}"></i>
                                </button>
                                <span class="cat-label" data-i18n="{{ $catMap[$cat->name] ?? '' }}">{{ $cat->name }}</span>
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
                        <input type="text" id="qc-name" class="input-premium py-2" data-i18n-placeholder="categoryName" placeholder="Category Name">
                        <div class="flex flex-wrap gap-2">
                             @php($icons = ['bi-egg-fried','bi-bag','bi-car-front','bi-gift','bi-receipt','bi-mortarboard','bi-heart','bi-basket'])
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

                <!-- Limit Amount -->
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="budgetLimit">حد الميزانية</label>
                    <div class="relative">
                        <input type="number" step="0.01" name="limit_amount" class="input-premium text-center text-2xl font-bold pl-12 @error('limit_amount') input-invalid @enderror" placeholder="0.00" value="{{ old('limit_amount') }}" required min="1" max="99999999">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold" data-i18n="lydSymbol">د.ل</span>
                    </div>
                    @error('limit_amount')
                        <div class="invalid-feedback-premium">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>
 
                <!-- Period -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="periodStart">بداية الفترة</label>
                        <input type="date" name="period_start" class="input-premium @error('period_start') input-invalid @enderror" value="{{ old('period_start', now()->toDateString()) }}" required>
                        @error('period_start')
                            <div class="invalid-feedback-premium">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="periodEnd">نهاية الفترة</label>
                        <input type="date" name="period_end" class="input-premium @error('period_end') input-invalid @enderror" value="{{ old('period_end', now()->addMonth()->toDateString()) }}" required>
                        @error('period_end')
                            <div class="invalid-feedback-premium">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="status">الحالة</label>
                    <input type="text" name="status" class="input-premium" value="{{ old('status', 'active') }}" data-i18n-placeholder="statusPlaceholder" placeholder="active">
                </div>
 
                <!-- Actions -->
                <div class="flex gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="submit" class="btn-gold flex-1 text-center justify-center py-3 text-lg shadow-md" data-i18n="saveBudget">حفظ الميزانية</button>
                    <a href="{{ route('budgets.index') }}" class="btn-soft px-6" data-i18n="cancel">إلغاء</a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tiles = document.querySelectorAll('.category-btn');
            const hidden = document.getElementById('category_id_hidden');
            const btnAddCat = document.getElementById('btn-add-cat');
            const quickCat = document.getElementById('quick-cat');
            const qcName = document.getElementById('qc-name');
            const qcIcon = document.getElementById('qc-icon');
            const qcSave = document.getElementById('qc-save');
            const qcCancel = document.getElementById('qc-cancel');
            
            const clearActive = () => tiles.forEach(t => t.classList.remove('active'));
            
            // Select default if none selected
            const selectDefault = () => {
                if (hidden.value) return;
                const first = tiles[0];
                if (first) {
                    hidden.value = first.dataset.id || '';
                    clearActive();
                    first.classList.add('active');
                }
            };
            
            tiles.forEach(tile => {
                tile.addEventListener('click', (e) => {
                    e.preventDefault(); // prevent form submit if button type not explicitly 'button' (though it is)
                    clearActive();
                    tile.classList.add('active');
                    hidden.value = tile.dataset.id || '';
                });
            });
            selectDefault();

            // Quick Category Logic
            if (btnAddCat) btnAddCat.addEventListener('click', () => quickCat.classList.remove('hidden'));
            if (qcCancel) qcCancel.addEventListener('click', () => quickCat.classList.add('hidden'));

            document.querySelectorAll('.icon-pick').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.icon-pick').forEach(b => b.classList.remove('border-amber-400', 'bg-amber-50'));
                    btn.classList.add('border-amber-400', 'bg-amber-50');
                    qcIcon.value = btn.dataset.icon;
                });
            });
            
            if (qcSave) {
                 qcSave.addEventListener('click', async () => {
                    const name = qcName.value.trim();
                    const icon = qcIcon.value;
                    const type = 'expense';
                    if (!name) return;
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                     try {
                        const res = await fetch('{{ route('categories.quickStore') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                            body: JSON.stringify({ name, icon, type })
                        });
                        if(res.ok) {
                            const cat = await res.json();
                            const grid = document.querySelector('.category-grid');
                            const wrapper = document.createElement('div');
                            wrapper.className = 'flex flex-col items-center animate-enter';
                            wrapper.innerHTML = `
                                <button type="button" class="category-btn active" data-id="${cat.id}" data-name="${cat.name}" style="--cat-color: #ef4444;">
                                    <i class="bi ${cat.icon || 'bi-basket'}"></i>
                                </button>
                                <span class="cat-label">${cat.name}</span>
                            `;
                            
                            clearActive();
                            grid.appendChild(wrapper);
                            hidden.value = cat.id;

                            // Rebind
                             wrapper.querySelector('button').addEventListener('click', function() {
                                document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                                this.classList.add('active');
                                hidden.value = this.dataset.id;
                            });
                            
                            quickCat.classList.add('hidden');
                            qcName.value = '';
                        }
                    } catch(e) { console.error(e); alert('Error'); }
                 });
            }
        });
    </script>
    @endpush
@endsection
