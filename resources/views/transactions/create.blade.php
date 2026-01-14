@extends('layouts.app')

@section('content')
    @php
        $selectedType = old('type', request('type', 'expense'));
        if (!in_array($selectedType, ['income','expense'], true)) { $selectedType = 'expense'; }
        $selectedCategoryId = old('category_id');
        $selectedCategoryName = old('category');
        $categories = $categories ?? collect();
        $incomeCats = $categories->where('type','income');
        $expenseCats = $categories->where('type','expense');
        $fallbackIcons = [
            'طعام' => 'bi-egg-fried','تسوق' => 'bi-cart2','فواتير' => 'bi-receipt','ترفيه' => 'bi-mic','هاتف' => 'bi-phone','رياضة' => 'bi-activity','تجميل' => 'bi-person-hearts','تعليم' => 'bi-journal-text','اجتماعي' => 'bi-people',
            'راتب' => 'bi-cash-coin','مكافأة' => 'bi-gift','استثمار' => 'bi-graph-up-arrow','تحويل' => 'bi-arrow-left-right'
        ];
        $palette = [
            'طعام' => '#F59E0B',
            'تسوق' => '#8B5CF6',
            'فواتير' => '#EF4444',
            'ترفيه' => '#3B82F6',
            'هاتف' => '#06B6D4',
            'رياضة' => '#10B981',
            'تجميل' => '#EC4899',
            'تعليم' => '#22C55E',
            'اجتماعي' => '#6366F1',
            'راتب' => '#0EA5E9',
            'مكافأة' => '#F43F5E',
            'استثمار' => '#34D399',
            'تحويل' => '#64748B',
        ];

        // فئات المصروف فقط
        $expensePreset = [
            ['name' => 'ترفيه', 'icon' => 'bi-controller', 'color' => '#f472b6', 'bg' => '#ffe4ed'],
            ['name' => 'صحة', 'icon' => 'bi-heart', 'color' => '#f43f5e', 'bg' => '#ffe4e6'],
            ['name' => 'تسوق', 'icon' => 'bi-bag', 'color' => '#a855f7', 'bg' => '#f3e8ff'],
            ['name' => 'مواصلات', 'icon' => 'bi-car-front', 'color' => '#38bdf8', 'bg' => '#e0f2fe'],
            ['name' => 'طعام', 'icon' => 'bi-egg-fried', 'color' => '#fb923c', 'bg' => '#fff7ed'],
            ['name' => 'هدايا', 'icon' => 'bi-gift', 'color' => '#f59e0b', 'bg' => '#fff7ed'],
            ['name' => 'فواتير', 'icon' => 'bi-receipt', 'color' => '#ef4444', 'bg' => '#fee2e2'],
            ['name' => 'تعليم', 'icon' => 'bi-mortarboard', 'color' => '#6366f1', 'bg' => '#eef2ff'],
        ];

        $expenseTiles = collect($expensePreset)->map(function ($tile) use ($expenseCats) {
            $match = $expenseCats->firstWhere('name', $tile['name']);
            $tile['id'] = $match->id ?? null;
            return $tile;
        });

        $incomePreset = [
            ['name' => 'راتب', 'icon' => 'bi-wallet2', 'color' => '#22c55e', 'bg' => '#ecfdf3'],
            ['name' => 'مكافأة', 'icon' => 'bi-gift', 'color' => '#f43f5e', 'bg' => '#ffe4e6'],
            ['name' => 'استثمار', 'icon' => 'bi-graph-up-arrow', 'color' => '#0ea5e9', 'bg' => '#e0f2fe'],
            ['name' => 'تحويل', 'icon' => 'bi-arrow-left-right', 'color' => '#6366f1', 'bg' => '#eef2ff'],
        ];

        // طقم دخل افتراضي مطابق للصورة مع ربطه ببيانات قاعدة البيانات إن وجدت
        $incomeTiles = collect($incomePreset)->map(function ($tile) use ($incomeCats) {
            $match = $incomeCats->firstWhere('name', $tile['name']);
            $tile['id'] = $match->id ?? null;
            return $tile;
        });

        // إضافة أي فئات دخل أخرى موجودة في قاعدة البيانات وليست في الطقم الافتراضي
        $extraIncome = $incomeCats->filter(function ($cat) use ($incomePreset) {
            return collect($incomePreset)->where('name', $cat->name)->isEmpty();
        })->map(function ($cat) {
            return [
                'name' => $cat->name,
                'id' => $cat->id,
                'icon' => $cat->icon ?: 'bi-cash-coin',
                'color' => '#22c55e',
                'bg' => '#ecfdf3',
            ];
        });

        $incomeTiles = $incomeTiles->concat($extraIncome)->values();
        // مجموعة أيقونات افتراضية للاختيار السريع
        $icons = [
            'bi-egg-fried','bi-bag','bi-car-front','bi-gift','bi-receipt','bi-mortarboard','bi-heart','bi-basket',
            'bi-phone','bi-activity','bi-person-hearts','bi-journal-text','bi-people',
            'bi-cash-coin','bi-wallet2','bi-gift','bi-graph-up-arrow','bi-arrow-left-right'
        ];
    @endphp

    <style>
        /* Ensure hidden grids don't render when toggled */
        .d-none { display: none !important; }
        .form-card-tight { max-width: 880px; margin: 0 auto; }

        .type-toggle { box-shadow: inset 0 0 0 1px rgba(148,163,184,0.28); background: rgba(255,255,255,0.65); }
        .type-btn { transition: all 0.2s ease; border: 1px solid transparent; font-weight: 800; letter-spacing: -0.01em; font-size: 0.95rem; }
        .type-btn.active-expense { background: linear-gradient(135deg, #0b0b0b, #c9a227); color: #fff; border-color: #c9a227; box-shadow: 0 10px 24px rgba(11,11,11,0.18); }
        .type-btn.active-income { background: linear-gradient(135deg, #0b0b0b, #c9a227); color: #fff; border-color: #c9a227; box-shadow: 0 10px 24px rgba(11,11,11,0.18); }

        .category-round-grid { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 14px; }
        @media (max-width: 768px) { .category-round-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        @media (max-width: 640px) { .category-round-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .category-tile.round { width: 58px; height: 58px; border-radius: 50%; display: grid; place-items: center; border: 1px solid #e2e8f0; background: #fff; color: var(--cat-color,#0f172a); transition: all .15s ease; }
        .category-tile.round:hover { transform: translateY(-2px); box-shadow: 0 10px 18px rgba(15,23,42,0.06); }
        .category-tile.round.active-expense { border-color: #ef4444; box-shadow: 0 10px 20px rgba(239,68,68,0.12); }
        .category-tile.round.active-income { border-color: #22c55e; box-shadow: 0 10px 20px rgba(34,197,94,0.12); }
        .cat-item { display: flex; flex-direction: column; align-items: center; gap: 6px; }
        .cat-label { font-size: .85rem; font-weight: 600; }

        /* primary-gradient button now provided globally via app.scss */
        .icon-pick { border: 1px solid var(--card-border, #e2e8f0); background: var(--card-bg, #ffffff); }
        .icon-pick.active { border-color: #c9a227; box-shadow: 0 0 0 2px rgba(201,162,39,0.25); }
    </style>

    <div class="form-hero" dir="rtl">
        <div class="form-card form-card-tight">
            <div class="accent-bar"></div>
            <div class="card-body space-y-5">
                <div class="d-flex align-items-start gap-3">
                    <div class="p-2 rounded-circle" style="background: rgba(201,162,39,0.15); color: #c9a227;">
                        <i class="bi bi-cash-coin fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">إضافة دخل/مصروف</h5>
                        <div class="form-sub">اختر النوع ثم الفئة وأدخل التفاصيل.</div>
                    </div>
                </div>

                <form action="{{ route('transactions.store') }}" method="POST" class="space-y-4">
                    @csrf

        <div class="type-toggle grid grid-cols-2 rounded-2xl overflow-hidden bg-white dark:bg-slate-900">
            <button type="button" id="btn-expense" class="type-btn py-2 font-semibold flex items-center justify-center gap-2 {{ $selectedType === 'expense' ? 'active-expense' : '' }}">
                <i class="bi bi-arrow-down-circle"></i>
                <span>مصروفات</span>
            </button>
            <button type="button" id="btn-income" class="type-btn py-2 font-semibold flex items-center justify-center gap-2 {{ $selectedType === 'income' ? 'active-income' : '' }}">
                <i class="bi bi-arrow-up-circle"></i>
                <span>دخل</span>
            </button>
        </div>

        <input type="hidden" name="type" id="type" value="{{ $selectedType }}">

        <div class="space-y-4">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 p-3 flex flex-col gap-2">
                <label class="form-label mb-0 text-slate-800 dark:text-slate-100">المبلغ</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                    <input type="number" step="0.01" name="amount" class="form-control form-control-lg" value="{{ old('amount') }}" placeholder="0.00" required aria-required="true">
                    <span class="input-group-text">د.ل</span>
                </div>
                <small class="text-muted">أدخل المبلغ بدقة (يمكن استخدام الكسور العشرية).</small>
                @error('amount')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2">
                <label class="form-label mb-0 text-slate-800 dark:text-slate-100">الفئة</label>
                <button type="button" id="btn-add-cat" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1"><i class="bi bi-plus-circle"></i> فئة جديدة</button>
            </div>
            <input type="hidden" name="category_id" id="category_id_hidden" value="{{ $selectedCategoryId }}">
            <input type="hidden" name="category" id="category_name_hidden" value="{{ $selectedCategoryName }}">

            <div id="expense-grid" class="category-round-grid {{ $selectedType === 'expense' ? '' : 'd-none' }}">
                @foreach($expenseTiles as $tile)
                    @php $col = $tile['color'] ?? '#ef4444'; @endphp
                    <div class="cat-item">
                        <button type="button" class="category-tile round {{ (string)$selectedCategoryId === (string)$tile['id'] ? 'active-expense' : '' }}" data-type="expense" data-id="{{ $tile['id'] }}" data-name="{{ $tile['name'] }}" style="--cat-color: {{ $col }};">
                            <i class="bi {{ $tile['icon'] }}"></i>
                        </button>
                        <div class="cat-label">{{ $tile['name'] }}</div>
                    </div>
                @endforeach
            </div>

            <div id="income-grid" class="category-round-grid {{ $selectedType === 'income' ? '' : 'd-none' }}">
                @foreach($incomeTiles as $tile)
                    @php $col = $tile['color'] ?? '#22c55e'; @endphp
                    <div class="cat-item">
                        <button type="button" class="category-tile round {{ (string)$selectedCategoryId === (string)$tile['id'] ? 'active-income' : '' }}" data-type="income" data-id="{{ $tile['id'] }}" data-name="{{ $tile['name'] }}" style="--cat-color: {{ $col }};">
                            <i class="bi {{ $tile['icon'] }}"></i>
                        </button>
                        <div class="cat-label">{{ $tile['name'] }}</div>
                    </div>
                @endforeach
            </div>

            <div id="quick-cat" class="mt-2 d-none">
                <div class="card-soft border border-slate-200 dark:border-slate-700 rounded-xl p-3 shadow-sm">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label mb-1 text-slate-800 dark:text-slate-100">اسم الفئة</label>
                            <input type="text" id="qc-name" class="form-control pill-input" placeholder="مثلاً: قهوة">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label mb-1 text-slate-800 dark:text-slate-100">الأيقونة</label>
                            <input type="hidden" id="qc-icon" value="">
                            <div id="qc-icon-grid" class="d-flex flex-wrap gap-2">
                                @foreach(($icons ?? []) as $ic)
                                    <button type="button" class="btn btn-light icon-pick" data-icon="{{ $ic }}"><i class="bi {{ $ic }}"></i></button>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="button" class="btn btn-primary flex-fill" id="qc-save">حفظ الفئة</button>
                            <button type="button" class="btn btn-light flex-fill" id="qc-cancel">إلغاء</button>
                        </div>
                    </div>
                </div>
            </div>

            @error('category_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            @error('category')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
        </div>

        <div class="space-y-3">
            <div>
                <label class="form-label text-slate-800 dark:text-slate-100">الوصف (اختياري)</label>
                <textarea name="note" class="form-control pill-input" rows="2" placeholder="مثلاً: عشاء مع الأصدقاء">{{ old('note') }}</textarea>
                @error('note')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="form-label text-slate-800 dark:text-slate-100">التاريخ</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" name="occurred_at" class="form-control pill-input" value="{{ old('occurred_at', now()->toDateString()) }}" required aria-required="true">
                    </div>
                    <small class="text-muted">اختر اليوم الذي تمت فيه المعاملة.</small>
                    @error('occurred_at')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:gap-3 pt-2">
            <button class="btn flex-1 border border-slate-200 text-slate-700 bg-white" type="button" onclick="window.location='{{ route('transactions.index') }}'">إلغاء</button>
            <button class="btn flex-1 primary-gradient" id="save-btn">تأكيد</button>
        </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const btnExpense = document.getElementById('btn-expense');
                const btnIncome = document.getElementById('btn-income');
                const typeInput = document.getElementById('type');
                const catIdInput = document.getElementById('category_id_hidden');
                const catNameInput = document.getElementById('category_name_hidden');
                const saveBtn = document.getElementById('save-btn');
                const btnAddCat = document.getElementById('btn-add-cat');
                const quickCat = document.getElementById('quick-cat');
                const qcName = document.getElementById('qc-name');
                const qcIcon = document.getElementById('qc-icon');
                const expenseGrid = document.getElementById('expense-grid');
                const incomeGrid = document.getElementById('income-grid');

                const clearActive = () => {
                    document.querySelectorAll('.category-tile').forEach(el => el.classList.remove('active-income', 'active-expense'));
                };

                const selectDefault = (type) => {
                    const grid = type === 'income' ? incomeGrid : expenseGrid;
                    const first = grid?.querySelector('.category-tile');
                    if (first) selectTile(first);
                };

                const selectTile = (tile) => {
                    clearActive();
                    const type = tile.dataset.type;
                    tile.classList.add(type === 'income' ? 'active-income' : 'active-expense');
                    catIdInput.value = tile.dataset.id || '';
                    catNameInput.value = tile.dataset.name || '';
                };

                const activateType = (type) => {
                    typeInput.value = type;
                    if (type === 'income') {
                        btnIncome.classList.add('active-income');
                        btnExpense.classList.remove('active-expense');
                        incomeGrid.classList.remove('d-none');
                        expenseGrid.classList.add('d-none');
                    } else {
                        btnExpense.classList.add('active-expense');
                        btnIncome.classList.remove('active-income');
                        expenseGrid.classList.remove('d-none');
                        incomeGrid.classList.add('d-none');
                    }
                    // امسح اختيار النوع الآخر لتفادي تعارض الفئة مع النوع
                    catIdInput.value = '';
                    catNameInput.value = '';
                    selectDefault(type);
                };

                btnExpense.addEventListener('click', () => activateType('expense'));
                btnIncome.addEventListener('click', () => activateType('income'));

                document.querySelectorAll('.category-tile').forEach(tile => {
                    tile.addEventListener('click', () => selectTile(tile));
                });

                activateType(typeInput.value || 'expense');

                // Quick add category handlers
                if (btnAddCat) {
                    btnAddCat.addEventListener('click', () => quickCat.classList.toggle('d-none'));
                }
                const iconButtons = Array.from(document.querySelectorAll('#qc-icon-grid .icon-pick'));
                const setIcon = (btn) => {
                    iconButtons.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    qcIcon.value = btn.dataset.icon || '';
                };
                if (iconButtons.length) setIcon(iconButtons[0]);
                iconButtons.forEach(btn => btn.addEventListener('click', () => setIcon(btn)));
                const qcCancel = document.getElementById('qc-cancel');
                if (qcCancel) {
                    qcCancel.addEventListener('click', () => {
                        quickCat.classList.add('d-none');
                        qcName.value='';
                        qcIcon.value='';
                        iconButtons.forEach(b=>b.classList.remove('active'));
                        if (iconButtons[0]) { iconButtons[0].classList.add('active'); qcIcon.value = iconButtons[0].dataset.icon || ''; }
                    });
                }
                const qcSave = document.getElementById('qc-save');
                if (qcSave) {
                    qcSave.addEventListener('click', async () => {
                        const name = qcName.value.trim();
                        const icon = qcIcon.value;
                        const type = typeInput.value || 'expense';
                        if (!name) return alert('يرجى إدخال اسم الفئة');
                        const token = document.querySelector('input[name="_token"]').value;
                        const res = await fetch('{{ route('categories.quickStore') }}', {
                            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token }, body: JSON.stringify({ name, icon, type })
                        });
                        if (!res.ok) return alert('تعذر حفظ الفئة');
                        const cat = await res.json();
                        const grid = type === 'income' ? incomeGrid : expenseGrid;
                        const wrapper = document.createElement('div');
                        wrapper.className = 'cat-item';
                        const btn = document.createElement('button');
                        btn.type = 'button'; btn.className='category-tile round ' + (type==='income'?'active-income':'active-expense');
                        btn.dataset.type = type; btn.dataset.id = cat.id; btn.dataset.name = cat.name;
                        btn.innerHTML = `<i class="bi ${cat.icon || (type==='income'?'bi-cash-coin':'bi-basket')}"></i>`;
                        const palette = {
                            'طعام': '#F59E0B','تسوق': '#8B5CF6','فواتير': '#EF4444','ترفيه': '#3B82F6','هاتف': '#06B6D4','رياضة': '#10B981','تجميل': '#EC4899','تعليم': '#22C55E','اجتماعي': '#6366F1','راتب': '#0EA5E9','مكافأة': '#F43F5E','استثمار': '#34D399','تحويل': '#64748B'
                        };
                        const color = palette[cat.name] || (type==='income' ? '#22c55e' : '#ef4444');
                        btn.style.setProperty('--cat-color', color);
                        const label = document.createElement('div');
                        label.className = 'cat-label';
                        label.textContent = cat.name;
                        clearActive();
                        wrapper.appendChild(btn);
                        wrapper.appendChild(label);
                        grid.appendChild(wrapper);
                        catIdInput.value = cat.id; catNameInput.value = cat.name;
                        btn.addEventListener('click', () => { selectTile(btn); });
                        quickCat.classList.add('d-none'); qcName.value=''; qcIcon.value='';
                    });
                }
            });
        </script>
    @endpush
@endsection