@extends('layouts.app')

@section('content')
    <div class="form-hero" dir="rtl">
        <div class="form-card">
            <div class="accent-bar"></div>
            <div class="card-body space-y-4">
                <div class="d-flex align-items-start gap-3 mb-2">
                    <div class="p-2 rounded-circle" style="background: rgba(201,162,39,0.15); color: #c9a227;">
                        <i class="bi bi-pencil-square fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">تعديل معاملة</h5>
                        <div class="form-sub">حدث البيانات مع المحافظة على نوع الفئة.</div>
                    </div>
                </div>
    @php
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
        $icons = $icons ?? [
            'bi-egg-fried','bi-bag','bi-car-front','bi-gift','bi-receipt','bi-mortarboard','bi-heart','bi-basket',
            'bi-phone','bi-activity','bi-person-hearts','bi-journal-text','bi-people',
            'bi-cash-coin','bi-wallet2','bi-graph-up-arrow','bi-arrow-left-right'
        ];
        $selectedType = old('type', $transaction->type);
        $selectedCategoryId = old('category_id', $transaction->category_id);
        $selectedCategoryName = old('category', $transaction->category);
    @endphp
    <style>
        /* primary-gradient button now provided globally via app.scss */
        .category-round-grid { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 14px; }
        @media (max-width: 768px) { .category-round-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        @media (max-width: 640px) { .category-round-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .category-tile.round { width: 58px; height: 58px; border-radius: 50%; display: grid; place-items: center; border: 1px solid #e2e8f0; background: #fff; color: var(--cat-color,#0f172a); transition: all .15s ease; }
        .category-tile.round:hover { transform: translateY(-2px); box-shadow: 0 10px 18px rgba(15,23,42,0.06); }
        .category-tile.round.active-expense { border-color: #ef4444; box-shadow: 0 10px 20px rgba(239,68,68,0.12); }
        .category-tile.round.active-income { border-color: #22c55e; box-shadow: 0 10px 20px rgba(34,197,94,0.12); }
        .cat-item { display: flex; flex-direction: column; align-items: center; gap: 6px; }
        .cat-label { font-size: .85rem; font-weight: 600; }
        .d-none { display: none !important; }
        .icon-pick { border: 1px solid var(--card-border); background: var(--card-bg); }
        .icon-pick.active { border-color: var(--brand-start); box-shadow: 0 0 0 2px rgba(201,162,39,0.25); }
    </style>

    <form action="{{ route('transactions.update', $transaction) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        <div class="card-body row g-3">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="form-label text-slate-800 dark:text-slate-100">النوع</label>
                    <select name="type" class="form-select pill-input" required>
                        <option value="income" @selected(old('type', $transaction->type)==='income')>دخل</option>
                        <option value="expense" @selected(old('type', $transaction->type)==='expense')>مصروف</option>
                    </select>
                    @error('type')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="md:col-span-2">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <label class="form-label mb-0 text-slate-800 dark:text-slate-100">اختر الفئة</label>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-cat">إضافة فئة</button>
                    </div>
                    <input type="hidden" name="category_id" id="category_id_hidden" value="{{ $selectedCategoryId }}">
                    <input type="hidden" name="category" id="category_name_hidden" value="{{ $selectedCategoryName }}">

                    <div id="expense-grid" class="category-round-grid {{ $selectedType === 'expense' ? '' : 'd-none' }} mb-2">
                        @foreach($expenseCats as $cat)
                            @php $icon = $cat->icon ?: ($fallbackIcons[$cat->name] ?? 'bi-basket'); $col = $palette[$cat->name] ?? '#ef4444'; @endphp
                            <div class="cat-item">
                                <button type="button" class="category-tile round {{ (string)$selectedCategoryId === (string)$cat->id ? 'active-expense' : '' }}" data-type="expense" data-id="{{ $cat->id }}" data-name="{{ $cat->name }}" style="--cat-color: {{ $col }};">
                                    <i class="bi {{ $icon }}"></i>
                                </button>
                                <div class="cat-label">{{ $cat->name }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div id="income-grid" class="category-round-grid {{ $selectedType === 'income' ? '' : 'd-none' }} mb-2">
                        @foreach($incomeCats as $cat)
                            @php $icon = $cat->icon ?: ($fallbackIcons[$cat->name] ?? 'bi-cash-coin'); $col = $palette[$cat->name] ?? '#10b981'; @endphp
                            <div class="cat-item">
                                <button type="button" class="category-tile round {{ (string)$selectedCategoryId === (string)$cat->id ? 'active-income' : '' }}" data-type="income" data-id="{{ $cat->id }}" data-name="{{ $cat->name }}" style="--cat-color: {{ $col }};">
                                    <i class="bi {{ $icon }}"></i>
                                </button>
                                <div class="cat-label">{{ $cat->name }}</div>
                            </div>
                        @endforeach
                    </div>
                    @error('category_id')<div class="text-danger small">{{ $message }}</div>@enderror
                    @error('category')<div class="text-danger small">{{ $message }}</div>@enderror

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
                                @if($expenseCats->isEmpty() && $incomeCats->isEmpty())
                                    <div class="alert alert-warning soft-alert mt-2">لا توجد فئات بعد. استخدم زر "إضافة فئة" لإنشاء فئة بأيقونة.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full">
                <div>
                    <label class="form-label text-slate-800 dark:text-slate-100">المبلغ</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                        <input type="number" step="0.01" name="amount" class="form-control pill-input" value="{{ old('amount', $transaction->amount) }}" placeholder="مثلاً: 50 د.ل" required aria-required="true">
                        <span class="input-group-text">د.ل</span>
                    </div>
                    <small class="text-muted">أدخل المبلغ بدقة (يمكن استخدام الكسور العشرية).</small>
                    @error('amount')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label text-slate-800 dark:text-slate-100">التاريخ</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" name="occurred_at" class="form-control pill-input" value="{{ old('occurred_at', optional($transaction->occurred_at)->toDateString()) }}" required aria-required="true">
                    </div>
                    <small class="text-muted">اختر اليوم الذي تمت فيه المعاملة.</small>
                    @error('occurred_at')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
            <div>
                <label class="form-label text-slate-800 dark:text-slate-100">الوصف (اختياري)</label>
                <textarea name="note" class="form-control pill-input" rows="2" placeholder="مثلاً: عشاء مع الأصدقاء">{{ old('note', $transaction->note) }}</textarea>
                @error('note')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:gap-3 pt-2">
            <a href="{{ route('transactions.index') }}" class="btn border border-slate-200 text-slate-700 bg-white flex-1">عودة</a>
            <button class="btn primary-gradient flex-1">تحديث</button>
        </div>
    </form>
            </div>
        </div>
    </div>
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const typeSelect = document.querySelector('select[name="type"]');
                const btnAddCat = document.getElementById('btn-add-cat');
                const quickCat = document.getElementById('quick-cat');
                const qcName = document.getElementById('qc-name');
                const qcIcon = document.getElementById('qc-icon');
                const qcSave = document.getElementById('qc-save');
                const qcCancel = document.getElementById('qc-cancel');
                const catIdInput = document.getElementById('category_id_hidden');
                const catNameInput = document.getElementById('category_name_hidden');

                const clearActive = () => {
                    document.querySelectorAll('.category-tile').forEach(el => el.classList.remove('active-income','active-expense'));
                };
                const activateType = (type) => {
                    if (type === 'income') {
                        document.getElementById('income-grid').classList.remove('d-none');
                        document.getElementById('expense-grid').classList.add('d-none');
                    } else {
                        document.getElementById('expense-grid').classList.remove('d-none');
                        document.getElementById('income-grid').classList.add('d-none');
                    }
                    selectFirstIfEmpty(type);
                };

                const selectFirstIfEmpty = (type) => {
                    if (catIdInput.value) return;
                    const grid = type === 'income' ? document.getElementById('income-grid') : document.getElementById('expense-grid');
                    const first = grid?.querySelector('.category-tile:not(.more)');
                    if (first) {
                        catIdInput.value = first.dataset.id;
                        catNameInput.value = first.dataset.name;
                        clearActive();
                        first.classList.add(type === 'income' ? 'active-income' : 'active-expense');
                    }
                };

                document.querySelectorAll('.category-tile').forEach(tile => {
                    tile.addEventListener('click', () => {
                        clearActive();
                        const type = tile.dataset.type;
                        catIdInput.value = tile.dataset.id;
                        catNameInput.value = tile.dataset.name;
                        tile.classList.add(type === 'income' ? 'active-income' : 'active-expense');
                    });
                });

                btnAddCat.addEventListener('click', () => quickCat.classList.toggle('d-none'));
                qcCancel.addEventListener('click', () => { quickCat.classList.add('d-none'); qcName.value=''; qcIcon.value=''; iconButtons.forEach(b=>b.classList.remove('active')); iconButtons[0]?.classList.add('active'); qcIcon.value = iconButtons[0]?.dataset.icon || ''; });
                const iconButtons = Array.from(document.querySelectorAll('#qc-icon-grid .icon-pick'));
                const setIcon = (btn) => {
                    iconButtons.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    qcIcon.value = btn.dataset.icon || '';
                };
                if (iconButtons.length) setIcon(iconButtons[0]);
                iconButtons.forEach(btn => btn.addEventListener('click', () => setIcon(btn)));

                qcSave.addEventListener('click', async () => {
                    const name = qcName.value.trim();
                    const icon = qcIcon.value;
                    const type = typeSelect.value;
                    if (!name) return alert('يرجى إدخال اسم الفئة');
                    const token = document.querySelector('input[name="_token"]').value;
                    const res = await fetch('{{ route('categories.quickStore') }}', {
                        method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token }, body: JSON.stringify({ name, icon, type })
                    });
                    if (!res.ok) return alert('تعذر حفظ الفئة');
                    const cat = await res.json();
                    const grid = type === 'income' ? document.getElementById('income-grid') : document.getElementById('expense-grid');
                    const wrapper = document.createElement('div');
                    wrapper.className = 'cat-item';
                    const btn = document.createElement('button');
                    btn.type = 'button'; btn.className='category-tile round ' + (type==='income'?'active-income':'active-expense');
                    btn.dataset.type = type; btn.dataset.id = cat.id; btn.dataset.name = cat.name;
                    btn.innerHTML = `<i class="bi ${cat.icon || (type==='income'?'bi-cash-coin':'bi-basket')}"></i>`;
                    const palette = {
                        'طعام': '#F59E0B','تسوق': '#8B5CF6','فواتير': '#EF4444','ترفيه': '#3B82F6','هاتف': '#06B6D4','رياضة': '#10B981','تجميل': '#EC4899','تعليم': '#22C55E','اجتماعي': '#6366F1','راتب': '#0EA5E9','مكافأة': '#F43F5E','استثمار': '#34D399','تحويل': '#64748B'
                    };
                    const color = palette[cat.name] || (type==='income' ? '#10b981' : '#ef4444');
                    btn.style.setProperty('--cat-color', color);
                    const label = document.createElement('div');
                    label.className = 'cat-label';
                    label.textContent = cat.name;
                    clearActive();
                    wrapper.appendChild(btn);
                    wrapper.appendChild(label);
                    const moreTile = grid.querySelector('.category-tile.more')?.parentElement;
                    if (moreTile) {
                        grid.insertBefore(wrapper, moreTile);
                    } else {
                        grid.appendChild(wrapper);
                    }
                    catIdInput.value = cat.id; catNameInput.value = cat.name;
                    btn.addEventListener('click', () => { clearActive(); catIdInput.value = cat.id; catNameInput.value = cat.name; btn.classList.add(type==='income'?'active-income':'active-expense'); });
                    quickCat.classList.add('d-none'); qcName.value=''; qcIcon.value='';
                });

                activateType(typeSelect.value);
            });
        </script>
        @endpush
@endsection