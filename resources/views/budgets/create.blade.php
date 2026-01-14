@extends('layouts.app')

@section('content')
    @php
        $expenseCats = ($categories ?? collect())->where('type', 'expense');
        $selectedCategoryId = old('category_id');
        $fallbackIcons = [
            'طعام' => 'bi-egg-fried','تسوق' => 'bi-cart2','فواتير' => 'bi-receipt','ترفيه' => 'bi-mic','هاتف' => 'bi-phone','رياضة' => 'bi-activity','تجميل' => 'bi-person-hearts','تعليم' => 'bi-journal-text','اجتماعي' => 'bi-people',
            'راتب' => 'bi-cash-coin','مكافأة' => 'bi-gift','استثمار' => 'bi-graph-up-arrow','تحويل' => 'bi-arrow-left-right'
        ];
        $palette = [
            'طعام' => '#F59E0B','تسوق' => '#8B5CF6','فواتير' => '#EF4444','ترفيه' => '#3B82F6','هاتف' => '#06B6D4','رياضة' => '#10B981','تجميل' => '#EC4899','تعليم' => '#22C55E','اجتماعي' => '#6366F1'
        ];
    @endphp

    <style>
        .form-card-tight { max-width: 880px; margin: 0 auto; }
        .category-round-grid { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 14px; }
        @media (max-width: 768px) { .category-round-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        @media (max-width: 640px) { .category-round-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .category-tile.round { width: 58px; height: 58px; border-radius: 50%; display: grid; place-items: center; border: 1px solid #e2e8f0; background: #fff; color: var(--cat-color,#0f172a); transition: all .15s ease; }
        .category-tile.round:hover { transform: translateY(-2px); box-shadow: 0 10px 18px rgba(15,23,42,0.06); }
        .category-tile.round.active-expense { border-color: #ef4444; box-shadow: 0 10px 20px rgba(239,68,68,0.12); }
        .cat-item { display: flex; flex-direction: column; align-items: center; gap: 6px; }
        .cat-label { font-size: .85rem; font-weight: 600; }
        .icon-pick { border: 1px solid var(--card-border, #e2e8f0); background: var(--card-bg, #ffffff); }
        .icon-pick.active { border-color: #c9a227; box-shadow: 0 0 0 2px rgba(201,162,39,0.25); }
    </style>

    <div class="form-hero" dir="rtl">
        <div class="form-card form-card-tight">
            <div class="accent-bar"></div>
            <div class="card-body space-y-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="p-2 rounded-circle" style="background: rgba(15,118,110,0.12); color: #0f766e;">
                        <i class="bi bi-wallet2 fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">ميزانية جديدة</h5>
                        <div class="form-sub">حدد الفئة وحد الميزانية.</div>
                    </div>
                    <div class="ms-auto d-flex gap-2">
                        <button type="button" id="btn-add-cat" class="btn btn-light d-flex align-items-center gap-1"><i class="bi bi-plus-circle"></i> فئة جديدة</button>
                        <a href="{{ route('budgets.index') }}" class="btn btn-light">عودة</a>
                    </div>
                </div>

    <form action="{{ route('budgets.store') }}" method="POST" class="space-y-4" novalidate>
        @csrf
        <div class="mb-3">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                <label class="form-label mb-0">اختر الفئة</label>
            </div>
            <input type="hidden" name="category_id" id="category_id_hidden" value="{{ $selectedCategoryId }}">
            <div class="category-round-grid">
                @foreach($expenseCats as $cat)
                    @php
                        $icon = $cat->icon ?: ($fallbackIcons[$cat->name] ?? 'bi-basket');
                        $col = $palette[$cat->name] ?? ($cat->color ?? '#ef4444');
                    @endphp
                    <div class="cat-item">
                        <button type="button" class="category-tile round {{ (string)$selectedCategoryId === (string)$cat->id ? 'active-expense' : '' }}" data-id="{{ $cat->id }}" data-name="{{ $cat->name }}" style="--cat-color: {{ $col }};">
                            <i class="bi {{ $icon }}"></i>
                        </button>
                        <div class="cat-label">{{ $cat->name }}</div>
                    </div>
                @endforeach
            </div>
            @error('category_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
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
                                @php($icons = ['bi-egg-fried','bi-bag','bi-car-front','bi-gift','bi-receipt','bi-mortarboard','bi-heart','bi-basket'])
                                @foreach($icons as $ic)
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
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">الحد</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-piggy-bank"></i></span>
                    <input type="number" step="0.01" name="limit_amount" class="form-control" value="{{ old('limit_amount') }}" required aria-required="true">
                    <span class="input-group-text">د.ل</span>
                </div>
                <small class="text-muted">ضع سقف المصروف للفئة المختارة.</small>
                @error('limit_amount')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="mt-3">
            <label class="form-label">الحالة (اختياري)</label>
            <input type="text" name="status" class="form-control" value="{{ old('status') }}" placeholder="نشطة / متوقفة">
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2 pt-2">
            <a href="{{ route('budgets.index') }}" class="btn btn-light flex-1">إلغاء</a>
            <button class="btn primary-gradient flex-1">حفظ الميزانية</button>
        </div>
    </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tiles = document.querySelectorAll('.category-tile');
            const hidden = document.getElementById('category_id_hidden');
            const btnAddCat = document.getElementById('btn-add-cat');
            const quickCat = document.getElementById('quick-cat');
            const qcName = document.getElementById('qc-name');
            const qcIcon = document.getElementById('qc-icon');
            const qcSave = document.getElementById('qc-save');
            const qcCancel = document.getElementById('qc-cancel');
            const clearActive = () => tiles.forEach(t => t.classList.remove('active-expense'));
            const selectDefault = () => {
                if (hidden.value) return;
                const first = tiles[0];
                if (first) {
                    hidden.value = first.dataset.id || '';
                    clearActive();
                    first.classList.add('active-expense');
                }
            };
            tiles.forEach(tile => {
                tile.addEventListener('click', () => {
                    clearActive();
                    tile.classList.add('active-expense');
                    hidden.value = tile.dataset.id || '';
                });
            });
            selectDefault();

            if (btnAddCat) btnAddCat.addEventListener('click', () => quickCat.classList.toggle('d-none'));
            const iconButtons = Array.from(document.querySelectorAll('#qc-icon-grid .icon-pick'));
            const setIcon = (btn) => {
                iconButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                qcIcon.value = btn.dataset.icon || '';
            };
            if (iconButtons.length) setIcon(iconButtons[0]);
            iconButtons.forEach(btn => btn.addEventListener('click', () => setIcon(btn)));
            if (qcCancel) qcCancel.addEventListener('click', () => { quickCat.classList.add('d-none'); qcName.value=''; qcIcon.value=''; iconButtons.forEach(b=>b.classList.remove('active')); iconButtons[0]?.classList.add('active'); qcIcon.value = iconButtons[0]?.dataset.icon || ''; });
            if (qcSave) {
                qcSave.addEventListener('click', async () => {
                    const name = qcName.value.trim();
                    const icon = qcIcon.value;
                    const type = 'expense';
                    if (!name) return alert('يرجى إدخال اسم الفئة');
                    const token = document.querySelector('input[name="_token"]').value;
                    const res = await fetch('{{ route('categories.quickStore') }}', {
                        method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token }, body: JSON.stringify({ name, icon, type })
                    });
                    if (!res.ok) return alert('تعذر حفظ الفئة');
                    const cat = await res.json();
                    const grid = document.querySelector('.category-round-grid');
                    const wrapper = document.createElement('div');
                    wrapper.className = 'cat-item';
                    const btn = document.createElement('button');
                    btn.type = 'button'; btn.className='category-tile round active-expense';
                    btn.dataset.id = cat.id; btn.dataset.name = cat.name;
                    btn.innerHTML = `<i class="bi ${cat.icon || 'bi-basket'}"></i>`;
                    const palette = { 'طعام': '#F59E0B','تسوق': '#8B5CF6','فواتير': '#EF4444','ترفيه': '#3B82F6','هاتف': '#06B6D4','رياضة': '#10B981','تجميل': '#EC4899','تعليم': '#22C55E','اجتماعي': '#6366F1' };
                    const color = palette[cat.name] || '#ef4444';
                    btn.style.setProperty('--cat-color', color);
                    const label = document.createElement('div');
                    label.className = 'cat-label';
                    label.textContent = cat.name;
                    clearActive();
                    wrapper.appendChild(btn);
                    wrapper.appendChild(label);
                    grid.appendChild(wrapper);
                    hidden.value = cat.id;
                    btn.addEventListener('click', () => { clearActive(); btn.classList.add('active-expense'); hidden.value = cat.id; });
                    quickCat.classList.add('d-none'); qcName.value=''; qcIcon.value='';
                });
            }
        });
    </script>
    @endpush
@endsection
