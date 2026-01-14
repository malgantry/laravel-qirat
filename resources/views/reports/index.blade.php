@extends('layouts.app')

@section('content')
    <script>
        window.dashboardData = @json($dashboardData ?? []);
    </script>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="text-sm text-muted dark:text-slate-200">لوحة التقارير والإحصائيات</div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-slate-50">التقارير</h3>
        </div>
        <div class="flex flex-wrap gap-2">
            <div class="relative inline-block">
                <button type="button" class="btn btn-outline-secondary" id="exportToggle">
                    تصدير التقرير <i class="bi bi-download ms-1"></i>
                </button>
                <div id="exportMenu" class="hidden absolute right-0 mt-2 w-44 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg z-50 py-2">
                    <a class="block px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-100" href="{{ route('reports.export', ['start' => $start, 'end' => $end]) }}">تصدير CSV</a>
                    <a class="block px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-100" href="{{ route('reports.exportPdf', ['start' => $start, 'end' => $end]) }}">تصدير PDF</a>
                </div>
            </div>
            <a class="btn btn-primary" href="{{ route('budgets.index') }}">إدارة الميزانيات</a>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 items-center mb-4">
        <div class="flex-1 min-w-[260px] card-soft flex items-center gap-2 px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl">
            <i class="bi bi-search text-muted"></i>
            <input type="text" class="w-full bg-transparent focus:outline-none text-sm text-slate-900 dark:text-slate-50 placeholder:text-muted" placeholder="بحث فوري أو اكتب لتحليل AI" aria-label="بحث">
        </div>
        <div class="flex gap-2 flex-wrap">
            <button class="px-3 pb-1 pt-2 rounded-lg text-sm font-semibold border border-transparent relative" style="color: var(--brand-start); background: var(--brand-soft);" data-range="month">
                الشهر الحالي
                <span class="absolute left-2 right-2 -bottom-1 h-0.5 rounded-full" style="background: var(--brand-start);"></span>
            </button>
            <button type="button" class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 dark:text-slate-300 border border-transparent" data-range="30d">آخر 30 يوماً</button>
            <button type="button" class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 dark:text-slate-300 border border-transparent" data-range="quarter">الربع الحالي</button>
            <button type="button" class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 dark:text-slate-300 border border-transparent" data-range="year">هذا العام</button>
        </div>
    </div>

    <form method="get" class="d-flex align-items-end gap-3 flex-wrap mb-4" id="filters">
        <button class="btn btn-primary">تحديث</button>
    </form>
    @push('scripts')
    <script>
        (function(){
            const pad = (n)=> String(n).padStart(2,'0');
            const fmt = (d)=> `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
            const startOfMonth = (d)=> new Date(d.getFullYear(), d.getMonth(), 1);
            const endOfMonth = (d)=> new Date(d.getFullYear(), d.getMonth()+1, 0);
            const startOfQuarter = (d)=> { const q = Math.floor(d.getMonth()/3)*3; return new Date(d.getFullYear(), q, 1); };
            const endOfQuarter = (d)=> { const q = Math.floor(d.getMonth()/3)*3; return new Date(d.getFullYear(), q+3, 0); };
            const startOfYear = (d)=> new Date(d.getFullYear(), 0, 1);
            const endOfYear = (d)=> new Date(d.getFullYear(), 12, 0);

            document.querySelectorAll('[data-range]')?.forEach(btn=>{
                btn.addEventListener('click', ()=>{
                    const now = new Date();
                    const kind = btn.dataset.range;
                    let s, e;
                    if (kind === 'month') { s = startOfMonth(now); e = endOfMonth(now); }
                    else if (kind === '30d') { e = now; s = new Date(now); s.setDate(s.getDate()-30); }
                    else if (kind === 'quarter') { s = startOfQuarter(now); e = endOfQuarter(now); }
                    else if (kind === 'year') { s = startOfYear(now); e = endOfYear(now); }
                    if (!s || !e) return;
                    const url = new URL(`{{ route('reports.index') }}` , window.location.origin);
                    url.searchParams.set('start', fmt(s));
                    url.searchParams.set('end', fmt(e));
                    window.location = url.toString();
                });
            });

            const exportToggle = document.getElementById('exportToggle');
            const exportMenu = document.getElementById('exportMenu');
            const closeMenu = () => exportMenu?.classList.add('hidden');

            exportToggle?.addEventListener('click', (e)=>{
                e.stopPropagation();
                exportMenu?.classList.toggle('hidden');
            });
            document.addEventListener('click', (e)=>{
                if (!exportMenu || !exportToggle) return;
                if (exportMenu.contains(e.target) || exportToggle.contains(e.target)) return;
                closeMenu();
            });
            document.addEventListener('keydown', (e)=>{
                if (e.key === 'Escape') closeMenu();
            });
        })();
    </script>
    @endpush

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <div class="card-soft p-4 h-full rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col gap-2">
            <div class="text-sm text-muted">إجمالي الدخل</div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-slate-50">{{ number_format($totalIncome, 2) }} د.ل</div>
        </div>
        <div class="card-soft p-4 h-full rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col gap-2">
            <div class="text-sm text-muted">إجمالي المصروف</div>
            <div class="text-2xl font-extrabold" style="color: var(--danger);">{{ number_format($totalExpense, 2) }} د.ل</div>
        </div>
        <div class="card-soft p-4 h-full rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col gap-2">
            <div class="text-sm text-muted">الصافي</div>
            <div class="text-2xl font-extrabold" style="color: {{ $net >= 0 ? 'var(--brand-start)' : 'var(--danger)' }};">{{ number_format($net, 2) }} د.ل</div>
        </div>
        <div class="card-soft p-4 h-full rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col gap-2">
            <div class="text-sm text-muted">معدل الادخار</div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-slate-50">{{ number_format($savingsRate, 1) }}%</div>
        </div>
    </div>

    <section class="mb-4">
        @php
            $cards = [
                ['icon' => 'bi-list', 'label' => 'إجمالي المعاملات', 'value' => $dashboardData['transactionsCount'] ?? 0, 'color' => 'var(--brand-start)'],
                ['icon' => 'bi-bullseye', 'label' => 'الأهداف المكتملة', 'value' => $dashboardData['completedGoals'] ?? 0, 'color' => 'var(--brand-mid)'],
                ['icon' => 'bi-calendar3', 'label' => 'متوسط الإنفاق اليومي', 'value' => number_format($dashboardData['avgDaily'] ?? 0, 2) . ' د.ل', 'color' => 'var(--brand-blue)'],
                ['icon' => 'bi-calculator', 'label' => 'متوسط المعاملة', 'value' => number_format($dashboardData['avgTransaction'] ?? 0, 2) . ' د.ل', 'color' => 'var(--warning)'],
                ['icon' => 'bi-piggy-bank', 'label' => 'معدل الادخار', 'value' => number_format($dashboardData['savingsRate'] ?? 0, 1) . '%', 'color' => 'var(--brand-end)'],
                ['icon' => 'bi-graph-down-arrow', 'label' => 'أعلى فئة إنفاق', 'value' => $dashboardData['topExpenseCategory'] ?? '—', 'color' => 'var(--danger)'],
            ];
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($cards as $stat)
                <div class="card-soft p-4 h-full rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <div class="avatar-icon" style="background: {{ $stat['color'] }};">
                            <i class="bi {{ $stat['icon'] }}"></i>
                        </div>
                        <i class="bi bi-three-dots text-muted"></i>
                    </div>
                    <div class="text-sm text-slate-600 dark:text-slate-400">{{ $stat['label'] }}</div>
                    <div class="text-2xl font-extrabold text-slate-900 dark:text-slate-50">{{ $stat['value'] }}</div>
                    <div class="h-1.5 rounded-full" style="background: var(--brand-soft);">
                        <div class="h-full rounded-full" style="width: 65%; background: var(--brand-start);"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mb-4">
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card card-soft h-100 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="card-title mb-0">توزيع المصروفات حسب الفئة</h5>
                        <span class="chip"><i class="bi bi-pie-chart"></i> تفاعلي</span>
                    </div>
                    <div style="height:320px;">
                        <canvas id="categoryChart"></canvas>
                        <div id="categoryEmpty" class="text-muted text-center d-none mt-4">لا توجد بيانات مصروفات بعد.</div>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-3 text-sm">
                        <span class="inline-flex items-center gap-2 px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                            <span class="w-3 h-3 rounded-full" style="background: var(--brand-start);"></span> ضمن الميزانية
                        </span>
                        <span class="inline-flex items-center gap-2 px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                            <span class="w-3 h-3 rounded-full" style="background: var(--brand-mid);"></span> تحت المراقبة
                        </span>
                        <span class="inline-flex items-center gap-2 px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                            <span class="w-3 h-3 rounded-full" style="background: var(--danger);"></span> تجاوز/مصروف عالٍ
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card card-soft h-100 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="card-title mb-0">الدخل والمصروف شهريا</h5>
                        <span class="chip"><i class="bi bi-graph-up"></i> شهر/شهر</span>
                    </div>
                    <div style="height:320px;">
                        <canvas id="monthlyChart"></canvas>
                        <div id="monthlyEmpty" class="text-muted text-center d-none mt-4">لا توجد بيانات شهرية بعد.</div>
                    </div>
                    <div class="flex gap-3 mt-3 text-sm">
                        <span class="inline-flex items-center gap-2 px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                            <span class="w-3 h-3 rounded-full" style="background: var(--brand-start);"></span> الدخل
                        </span>
                        <span class="inline-flex items-center gap-2 px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                            <span class="w-3 h-3 rounded-full" style="background: var(--danger);"></span> المصروف
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if(isset($overspends) && $overspends->sum('overspend') > 0)
    <div class="card mb-4">
        <div class="card-header"><strong>أعلى التجاوزات</strong> <small class="text-muted">أكثر 3 فئات تجاوزاً للميزانية</small></div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                @foreach($overspends as $o)
                    @if($o['overspend'] > 0)
                        <span class="chip" style="background: var(--danger-soft); color: var(--danger);">
                            <i class="bi bi-exclamation-triangle"></i>
                            {{ $o['category'] }}: {{ number_format($o['overspend'], 2) }} د.ل فوق الحد
                        </span>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>الميزانيات ضمن الفترة</strong>
            <small class="text-muted">مقارنة حد الميزانية بالمصروف الفعلي</small>
        </div>
        <div class="card-body p-0">
            @if($budgets->isEmpty())
                <div class="p-3 text-muted">لا توجد ميزانيات مطابقة للفترة المحددة.</div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-modern">
                        <thead>
                        <tr>
                            <th>الفئة</th>
                            <th>الفترة</th>
                            <th>الحد</th>
                            <th>المصروف</th>
                            <th>المتبقي</th>
                            <th>التقدم</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($budgets as $b)
                            <tr>
                                <td>{{ $b['category'] }}</td>
                                <td><small class="text-muted">{{ $b['period_start'] }} → {{ $b['period_end'] }}</small></td>
                                <td>{{ number_format($b['limit'], 2) }} د.ل</td>
                                <td class="{{ $b['over'] ? 'text-danger fw-semibold' : '' }}">{{ number_format($b['spent'], 2) }} د.ل</td>
                                <td class="{{ $b['remaining'] < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($b['remaining'], 2) }} د.ل</td>
                                <td style="min-width:200px;">
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar {{ $b['over'] ? 'bg-danger' : '' }}" role="progressbar" style="width: {{ $b['progress'] }}%;" aria-valuenow="{{ $b['progress'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">{{ $b['progress'] }}%</small>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>تفصيل المصروف حسب الفئة</strong>
            <small class="text-muted">ضمن الفترة المحددة</small>
        </div>
        <div class="card-body p-0">
            @if($categoryBreakdown->isEmpty())
                <div class="p-3 text-muted">لا توجد مصروفات في هذه الفترة.</div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-modern">
                        <thead>
                        <tr>
                            <th>الفئة</th>
                            <th>المجموع</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($categoryBreakdown as $row)
                            <tr>
                                <td>{{ $row['category'] }}</td>
                                <td class="fw-semibold">{{ number_format($row['total'], 2) }} د.ل</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <section class="mt-4">
        <style>
            .goal-shell { background: var(--card-bg); box-shadow: var(--shadow-soft); border-radius: 24px; }
            .goal-inner { border: 1px solid var(--card-border); border-radius: 18px; padding: 20px; background: var(--card-bg); box-shadow: inset 0 1px 0 rgba(255,255,255,0.06); height: 100%; }
            .goal-ring { position: relative; width: 120px; height: 120px; border-radius: 9999px; background: conic-gradient(var(--brand-start) calc(var(--p) * 1%), var(--card-border) 0deg); display: grid; place-items: center; }
            .goal-ring::after { content: ""; position: absolute; inset: 14px; border-radius: 9999px; background: var(--card-bg); box-shadow: inset 0 0 0 1px var(--card-border); z-index: 0; }
            .goal-ring .ring-value { position: relative; z-index: 1; font-weight: 800; color: var(--brand-start); font-size: 1.1rem; display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; }
            .goal-label { color: var(--text-secondary); font-weight: 600; }
            .goal-value { color: var(--text-primary); font-weight: 700; }
            .goal-remaining { color: #f97316; font-weight: 800; }
        </style>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">الأهداف النشطة</h5>
            <a href="{{ route('goals.index') }}" class="btn btn-sm btn-primary"><i class="bi bi-bullseye"></i> إدارة الأهداف</a>
        </div>
        @if(($activeGoals ?? collect())->isEmpty())
            <p class="text-muted mb-0">لا توجد أهداف نشطة.</p>
        @else
            <div class="row g-3">
                @foreach($activeGoals as $goal)
                    @php $progress = (int) $goal->progress; @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="goal-shell h-100 p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                            <div class="goal-inner text-center">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="fw-bold text-slate-900 dark:text-slate-100">{{ $goal->name }}</div>
                                    <span class="badge text-bg-light">{{ optional($goal->deadline)->toDateString() ?? 'بدون موعد' }}</span>
                                </div>
                                <div class="d-flex flex-column align-items-center gap-3">
                                    <div class="goal-ring" style="--p: {{ $progress }};">
                                        <div class="ring-value">{{ $progress }}%</div>
                                    </div>
                                    <div class="w-100" dir="rtl">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="goal-label">المبلغ الحالي:</span>
                                            <span class="goal-value">{{ number_format($goal->current_amount, 2) }} د.ل</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="goal-label">المبلغ المستهدف:</span>
                                            <span class="goal-value">{{ number_format($goal->target_amount, 2) }} د.ل</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="goal-label">المتبقي:</span>
                                            <span class="goal-remaining">{{ number_format(max(0, $goal->target_amount - $goal->current_amount), 2) }} د.ل</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a class="btn btn-sm btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2" href="{{ route('goals.edit', $goal) }}">
                                        تعديل <i class="bi bi-pencil-square"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection
