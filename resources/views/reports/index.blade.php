@extends('layouts.app')

@section('content')
    <script>
        window.dashboardData = @json($dashboardData ?? []);
    </script>

    <div class="space-y-8 animate-enter">
        <!-- Dashboard Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 text-[var(--gold-500)] font-black text-[10px] uppercase tracking-[0.2em] mb-2" data-i18n="businessIntelligence">
                    <span class="w-8 h-px bg-[var(--gold-400)]"></span>
                    التحليلات المالية المتقدمة
                </div>
                <h3 class="text-4xl font-heading font-black text-text-main tracking-tight" data-i18n="analyticalReports">التقارير التحليلية</h3>
            </div>
            
            <div class="flex flex-wrap gap-4 items-center">
                <div class="relative" id="exportDropShell">
                    <button type="button" class="flex items-center gap-2 pl-2 pr-1.5 py-1.5 rounded-xl border border-transparent hover:border-slate-200 dark:hover:border-slate-700 hover:bg-white/50 dark:hover:bg-slate-800/50 transition-all duration-300 group shadow-sm" id="exportDropToggle">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-[var(--gold-400)] to-[var(--gold-600)] flex items-center justify-center text-white shadow-sm border border-white/20">
                            <i class="bi bi-cloud-download text-lg group-hover:scale-110 transition-transform"></i>
                        </div>
                        
                        <div class="flex flex-col items-start leading-none px-1">
                            <span data-i18n="exportData" class="text-xs font-bold text-slate-700 dark:text-slate-200 group-hover:text-[var(--gold-600)] transition-colors">تصدير البيانات</span>
                            <i class="bi bi-caret-down-fill text-[8px] text-slate-400 mt-0.5 group-hover:rotate-180 transition-transform shadow-none"></i>
                        </div>
                    </button>
                    <div id="exportDropMenu" class="profile-menu hidden" style="right: 0; left: auto; width: 220px;">
                        <a class="menu-item transition-all" href="{{ route('reports.exportExcel', ['start' => $start, 'end' => $end]) }}">
                            <i class="bi bi-file-earmark-excel text-lg text-emerald-500"></i>
                            <span data-i18n="excelFormat" class="font-bold text-sm">Excel (احترافي)</span>
                        </a>
                        <a class="menu-item transition-all" href="{{ route('reports.exportPdf', ['start' => $start, 'end' => $end]) }}">
                            <i class="bi bi-file-earmark-pdf text-lg text-rose-500"></i>
                            <span data-i18n="pdfFormat" class="font-bold text-sm">PDF (جاهز للطباعة)</span>
                        </a>
                    </div>
                </div>

                <a class="btn-gold px-10 py-3.5 text-sm font-black shadow-xl shadow-amber-500/20 rounded-full hover:scale-105 active:scale-95 transition-all flex items-center gap-3 border-none" href="{{ route('budgets.index') }}">
                    <i class="bi bi-wallet2 text-lg"></i> <span data-i18n="financialPlanning">التخطيط المالي</span>
                </a>
            </div>
        </div>
        


        <!-- Filter Bar -->
        <div class="card-premium p-4 flex flex-col md:flex-row items-center gap-4 border-none shadow-lg">
            <div class="flex-1 w-full relative group">
                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400 group-focus-within:text-[var(--gold-500)] transition-colors">
                    <i class="bi bi-funnel"></i>
                </div>
                <input type="text" class="input-premium pr-11 text-sm py-3" data-i18n-placeholder="searchTransactionsPlaceholder" placeholder="Search transactions or categories..." data-i18n-title="search" aria-label="Search">
            </div>
            
            <div class="flex gap-2 p-1 bg-slate-100 dark:bg-slate-900/50 rounded-2xl border border-slate-200/50 dark:border-slate-800/50">
                <button class="px-5 py-2.5 text-xs font-bold rounded-xl transition-all range-btn text-text-muted" data-range="week" data-i18n="thisWeek">هذا الأسبوع</button>
                <button class="px-5 py-2.5 text-xs font-black rounded-xl transition-all range-btn bg-white dark:bg-slate-800 text-[var(--gold-600)] shadow-sm border border-[var(--gold-100)] dark:border-[var(--gold-900)]/30" data-range="month" data-i18n="thisMonth">هذا الشهر</button>
                <button class="px-5 py-2.5 text-xs font-bold rounded-xl transition-all range-btn text-text-muted" data-range="year" data-i18n="thisYear">هذا العام</button>
            </div>
        </div>

        <!-- Dynamic Stat Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $topStats = [
                    ['label' => 'totalIncomeLabel', 'value' => number_format($totalIncome, 2), 'suffix' => 'د.ل', 'icon' => 'bi-arrow-up-right', 'color' => 'emerald'],
                    ['label' => 'totalExpenseLabel', 'value' => number_format($totalExpense, 2), 'suffix' => 'د.ل', 'icon' => 'bi-arrow-down-left', 'color' => 'rose'],
                    ['label' => 'netBalance', 'value' => number_format($net, 2), 'suffix' => 'د.ل', 'icon' => 'bi-wallet2', 'color' => $net >= 0 ? 'amber' : 'rose'],
                    ['label' => 'totalSavings', 'value' => number_format($dashboardData['totalSavings'] ?? 0, 2), 'suffix' => 'د.ل', 'icon' => 'bi-piggy-bank', 'color' => 'indigo'],
                ];
            @endphp

            @foreach($topStats as $s)
                <div class="card-premium p-6 border-none shadow-xl relative overflow-hidden group backdrop-blur-md bg-white/40 dark:bg-slate-900/40 hover:border-{{ $s['color'] }}-500/30 transition-all duration-500 hover:shadow-{{ $s['color'] }}-500/10">
                    <div class="absolute -top-12 -left-12 w-32 h-32 bg-{{ $s['color'] }}-500/10 blur-3xl rounded-full group-hover:bg-{{ $s['color'] }}-500/20 transition-colors"></div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-{{ $s['color'] }}-50 dark:bg-{{ $s['color'] }}-900/20 text-{{ $s['color'] }}-600 dark:text-{{ $s['color'] }}-400 flex items-center justify-center text-xl shadow-inner border border-{{ $s['color'] }}-100/50 dark:border-{{ $s['color'] }}-900/30 group-hover:scale-110 transition-transform">
                            <i class="bi {{ $s['icon'] }}"></i>
                        </div>
                        <span class="text-[10px] font-black text-slate-400 dark:text-slate-600 uppercase tracking-widest" data-i18n="{{ $s['label'] }}">{{ $s['label'] }}</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-heading font-black text-text-main group-hover:translate-x-[-4px] transition-transform">{{ $s['value'] }}</span>
                        <span class="text-[10px] font-bold text-slate-400">{{ $s['suffix'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Analytical Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="card-premium p-8 border-none shadow-2xl relative">
                <div class="flex justify-between items-center mb-10">
                    <div>
                        <h5 class="text-xl font-heading font-black text-text-main mb-1" data-i18n="expenseDistribution">توزيع المصروفات</h5>
                        <p class="text-xs text-text-muted font-medium" data-i18n="analyticalAnalysis">تحليل نسبي للإنفاق حسب الفئات الرئيسية.</p>
                    </div>
                    <div class="w-10 h-10 rounded-2xl bg-[var(--gold-50)] dark:bg-[var(--gold-900)]/20 text-[var(--gold-500)] flex items-center justify-center text-lg">
                        <i class="bi bi-pie-chart-fill"></i>
                    </div>
                </div>
                <div class="relative" style="height:350px;">
                    <canvas id="categoryChart"></canvas>
                    <div id="categoryEmpty" class="hidden absolute inset-0 flex items-center justify-center text-slate-400 text-sm font-medium bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm rounded-3xl border border-dashed border-slate-200 dark:border-slate-800">
                        <div class="text-center">
                            <i class="bi bi-inbox text-4xl mb-4 block opacity-20"></i>
                            <span data-i18n="noDataPeriod">لا توجد بيانات مصروفات للفترة المحددة.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-premium p-8 border-none shadow-2xl relative">
                <div class="flex justify-between items-center mb-10">
                    <div>
                        <h5 class="text-xl font-heading font-black text-text-main mb-1" data-i18n="financialTimeline">المسار الزمني المالي</h5>
                        <p class="text-xs text-text-muted font-medium" data-i18n="monthlyComparison">مقارنة شهرية بين تدفقات الدخل وحجم المصروفات.</p>
                    </div>
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-blue-500 flex items-center justify-center text-lg">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
                <div class="relative" style="height:350px;">
                    <canvas id="monthlyChart"></canvas>
                    <div id="monthlyEmpty" class="hidden absolute inset-0 flex items-center justify-center text-slate-400 text-sm font-medium bg-white/50 dark:bg-slate-900/50 backdrop-blur-sm rounded-3xl border border-dashed border-slate-200 dark:border-slate-800">
                        <div class="text-center">
                            <i class="bi bi-inbox text-4xl mb-4 block opacity-20"></i>
                            <span data-i18n="noTimelineData">لم يتم العثور على سجلات زمنية كافية.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
        $catMap = [
            'طعام' => 'food', 'تسوق' => 'shopping', 'فواتير' => 'bills', 'ترفيه' => 'entertainment',
            'هاتف' => 'phone', 'رياضة' => 'sports', 'تجميل' => 'beauty', 'تعليم' => 'education',
            'اجتماعي' => 'social', 'راتب' => 'salary', 'مكافأة' => 'bonus', 'استثمار' => 'investment',
            'تحويل' => 'transfer', 'صحة' => 'health', 'مواصلات' => 'transport', 'هدايا' => 'gifts',
            'غير مصنف' => 'uncategorized', 'رواتب' => 'salary', 'أخرى' => 'uncategorized', 'غير محدد' => 'uncategorized',
            'Savings' => 'savings', 'ادخار' => 'savings', 'الادخار' => 'savings'
        ];
    @endphp

    @if(isset($overspends) && $overspends->sum('overspend') > 0)
        <!-- Alerts & Warnings -->
        <div class="ai-feedback-gold border-rose-500/30 bg-rose-50/50 dark:bg-rose-900/10 p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                </div>
                <div>
                    <h5 class="text-lg font-heading font-black text-rose-900 dark:text-rose-100 mb-0" data-i18n="overspendAlerts">تنبيهات تجاوز الميزانية</h5>
                    <p class="text-xs text-rose-700 dark:text-rose-400 font-medium opacity-80" data-i18n="overspendInfo">تم التجاوز في الفئات التالية:</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($overspends as $o)
                    @if($o['overspend'] > 0)
                        <div class="px-4 py-2 rounded-2xl bg-white/80 dark:bg-slate-900/50 border border-rose-200 dark:border-rose-900/30 shadow-sm flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                            <span class="text-sm font-bold text-slate-800 dark:text-slate-200" data-i18n="{{ $catMap[$o['category']] ?? 'uncategorized' }}">{{ $o['category'] }}</span>
                            <span class="text-sm font-black text-rose-600 ml-1">{{ number_format($o['overspend'], 2) }} د.ل</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        <!-- Budget Detailed Comparison -->
        <div class="card-premium overflow-hidden border-none shadow-2xl">
            <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <div>
                    <h5 class="text-xl font-heading font-black text-text-main mb-1" data-i18n="budgetAnalysis">تحليل حدود الميزانية</h5>
                    <p class="text-xs text-text-muted font-medium" data-i18n="plannedVsActual">مقارنة دقيقة بين تقديراتك المخططة والواقع المالي.</p>
                </div>
                <i class="bi bi-layers-half text-2xl text-slate-300"></i>
            </div>
            
            @if($budgets->isEmpty())
                <div class="p-16 text-center">
                    <div class="w-24 h-24 bg-slate-50 dark:bg-slate-900/50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-200 dark:text-slate-800">
                        <i class="bi bi-wallet2 text-5xl"></i>
                    </div>
                    <p class="text-slate-400 font-bold" data-i18n="noBudgetsPeriod">لا ميزانيات مسجلة لهذه الفترة.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-950/20">
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800" data-i18n="statisticalCategory">الفئة الإحصائية</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800" data-i18n="timePeriod">الفترة الزمنية</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 text-center" data-i18n="definedCap">السقف المحدد</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 text-center" data-i18n="actualSpending">الإنفاق الفعلي</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 text-center" data-i18n="consumptionIndicator">مؤشر الاستهلاك</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($budgets as $b)
                                <tr class="group hover:bg-[var(--gold-50)]/30 dark:hover:bg-[var(--gold-900)]/5 transition-colors">
                                    <td class="px-8 py-6 border-b border-slate-100 dark:border-slate-800">
                                        <div class="flex items-center gap-3">
                                            <div class="w-2 h-2 rounded-full bg-[var(--gold-500)]"></div>
                                            <span class="font-heading font-black text-slate-800 dark:text-slate-200" data-i18n="{{ $catMap[$b['category']] ?? 'uncategorized' }}">{{ $b['category'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 border-b border-slate-100 dark:border-slate-800">
                                        <div class="text-xs text-text-muted font-bold"><span data-i18n="from">من</span> {{ $b['period_start'] ?? '—' }}</div>
                                        <div class="text-[10px] text-slate-400 mt-1 font-medium"><span data-i18n="to">إلى</span> {{ $b['period_end'] ?? '—' }}</div>
                                    </td>
                                    <td class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 text-center">
                                        <span class="text-sm font-black text-text-main">{{ number_format($b['limit'], 2) }}</span>
                                        <span class="text-[10px] font-bold text-slate-400 mr-1">د.ل</span>
                                    </td>
                                    <td class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 text-center">
                                        <span class="text-sm font-black {{ $b['over'] ? 'text-rose-600' : 'text-slate-600 dark:text-slate-400' }}">{{ number_format($b['spent'], 2) }}</span>
                                        <span class="text-[10px] font-bold text-slate-400 mr-1">د.ل</span>
                                    </td>
                                    <td class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 min-w-[200px]">
                                        <div class="flex items-center gap-4">
                                            <div class="flex-1 h-2 rounded-full bg-slate-100 dark:bg-slate-900 shadow-inner overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-1000 ease-out {{ $b['over'] ? 'bg-gradient-to-r from-rose-500 to-rose-600 shadow-[0_0_10px_rgba(244,63,94,0.3)]' : 'bg-gradient-to-r from-[var(--gold-400)] to-[var(--gold-600)] shadow-[0_0_10px_rgba(212,175,55,0.3)]' }}" style="width: {{ min($b['progress'], 100) }}%;"></div>
                                            </div>
                                            <span class="text-xs font-black {{ $b['over'] ? 'text-rose-600' : 'text-[var(--gold-600)]' }} min-w-[40px] text-left">{{ $b['progress'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- AI Financial Intelligence Center -->
        <div class="space-y-6 mb-12">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-xl shadow-lg">
                    <i class="bi bi-robot"></i>
                </div>
                <div>
                    <h4 class="text-2xl font-heading font-black text-text-main mb-0" data-i18n="aiIntelCenter">التوقعات والحلول</h4>
                    <p class="text-xs text-text-muted font-medium" data-i18n="aiAdviceDesc">تحليلات متقدمة ونقاط تركيز بناءً على نشاطك المالي الأخير.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="ai-insights-container">
                <!-- Skeletons / Loading State -->
                @for($i=0; $i<3; $i++)
                <div class="card-premium p-6 border-none shadow-xl animate-pulse bg-slate-100/50 dark:bg-slate-800/50 h-32 rounded-3xl"></div>
                @endfor
            </div>
        </div>

        <!-- Secondary Analysis Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Category Breakdown List -->
            <div class="card-premium p-8 border-none shadow-2xl lg:col-span-1">
                <div class="flex items-center gap-3 mb-8">
                    <div class="w-10 h-10 rounded-2xl bg-slate-50 dark:bg-slate-900 text-slate-400 flex items-center justify-center text-lg shadow-inner">
                        <i class="bi bi-tag-fill"></i>
                    </div>
                    <h5 class="text-xl font-heading font-black text-text-main mb-0" data-i18n="categoryBreakdown">التفصيل الفئوي</h5>
                </div>
                
                @if($categoryBreakdown->isEmpty())
                    <p class="text-slate-400 text-sm font-medium py-10 text-center italic" data-i18n="noCategoryActivity">لا نشاط مسجل للفئات.</p>
                @else
                    <div class="space-y-4">
                        @foreach($categoryBreakdown as $row)
                            @php 
                                $rowColor = $row['color'] ?? '#94a3b8'; 
                                $rowIcon = $row['icon'] ?? 'bi-tag';
                            @endphp
                            <div class="p-4 rounded-[24px] bg-white/40 dark:bg-slate-900/40 border border-slate-100 dark:border-white/5 flex items-center justify-between group hover:border-[var(--gold-400)] dark:hover:border-[var(--gold-600)] hover:shadow-xl transition-all duration-300">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl transition-all shadow-sm group-hover:scale-110" style="background-color: {{ $rowColor }}15; color: {{ $rowColor }}; border: 1px solid {{ $rowColor }}30;">
                                        <i class="bi {{ $rowIcon }}"></i>
                                    </div>
                                    <div>
                                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1" data-i18n="{{ $catMap[$row['category']] ?? 'uncategorized' }}">{{ $row['category'] }}</div>
                                        <div class="text-lg font-heading font-black text-text-main flex items-baseline gap-1">
                                            {{ number_format($row['total'], 2) }} 
                                            <span class="text-[10px] font-bold text-slate-400">د.ل</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-white/5 text-[10px] font-black text-slate-400 group-hover:text-[var(--gold-600)] transition-colors">
                                    {{ number_format(($totalExpense > 0 ? ($row['total'] / $totalExpense) * 100 : 0), 1) }}%
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Active Goals Focus -->
            <div class="card-premium p-8 border-none shadow-2xl lg:col-span-2">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-[var(--gold-50)] dark:bg-[var(--gold-900)]/20 text-[var(--gold-500)] flex items-center justify-center text-lg shadow-inner border border-[var(--gold-100)]/30">
                            <i class="bi bi-bullseye"></i>
                        </div>
                        <h5 class="text-xl font-heading font-black text-text-main mb-0" data-i18n="savingsProgressCenter">مركز التقدّم للادخار</h5>
                    </div>
                    <a href="{{ route('goals.index') }}" class="btn-soft px-6 py-2.5 text-xs font-black rounded-2xl" data-i18n="manageStrategicGoals">
                        إدارة الأهداف الاستراتيجية
                    </a>
                </div>

                @if(($activeGoals ?? collect())->isEmpty())
                    <div class="p-16 text-center border-2 border-dashed border-slate-100 dark:border-slate-800 rounded-[32px]">
                        <i class="bi bi-patch-question text-4xl text-slate-100 block mb-4"></i>
                        <p class="text-slate-400 font-bold" data-i18n="startSettingGoals">ابدأ بتحديد أهدافك المالية لنقوم بتتبعها هنا.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($activeGoals as $goal)
                            @php $progress = (int) $goal->progress; @endphp
                            <div class="card-premium p-6 border-none shadow-xl bg-white/40 dark:bg-slate-900/40 backdrop-blur-3xl overflow-hidden relative group hover:shadow-2xl transition-all duration-500">
                                <div class="absolute -right-8 -bottom-8 w-24 h-24 bg-[var(--gold-500)]/5 blur-3xl rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                                
                                <div class="flex justify-between items-start mb-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500 flex items-center justify-center text-xl shadow-inner border border-emerald-100/30">
                                            <i class="bi bi-flag-fill"></i>
                                        </div>
                                        <div>
                                            <h6 class="font-heading font-black text-slate-800 dark:text-slate-100 mb-0 tracking-tight">{{ $goal->name }}</h6>
                                            <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400">
                                                <i class="bi bi-calendar-event"></i>
                                                <span data-i18n="{{ optional($goal->deadline)->toDateString() ? '' : 'noTimeline' }}">{{ optional($goal->deadline)->toDateString() ?? 'بدون جدول زمني' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 text-[9px] font-black rounded-lg border border-emerald-200/50 dark:border-emerald-800/50 uppercase tracking-widest" data-i18n="active">نشط</span>
                                </div>

                                <div class="flex items-center gap-6 mb-8 mt-2">
                                    <div class="relative w-20 h-20 flex-shrink-0">
                                        <div class="ring-premium ring-thickness-3 group-hover:rotate-12 transition-transform duration-700" style="--p: {{ $progress }}%; width: 80px; height: 80px;">
                                            <div class="absolute inset-0 rounded-full border border-slate-100/50 dark:border-white/5"></div>
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <span class="ring-premium-val text-sm text-text-main">{{ $progress }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-1 space-y-4">
                                        <div class="flex justify-between items-baseline">
                                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest" data-i18n="current">الحالي</span>
                                            <div class="flex items-baseline gap-1">
                                                <span class="text-sm font-black text-emerald-600">{{ number_format($goal->current_amount, 2) }}</span>
                                                <span class="text-[9px] font-bold text-slate-400">د.ل</span>
                                            </div>
                                        </div>
                                        <div class="flex justify-between items-baseline">
                                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest" data-i18n="remaining">المتبقي</span>
                                            <div class="flex items-baseline gap-1">
                                                <span class="text-sm font-black text-amber-600">{{ number_format(max(0, $goal->target_amount - $goal->current_amount), 2) }}</span>
                                                <span class="text-[9px] font-bold text-slate-400">د.ل</span>
                                            </div>
                                        </div>
                                        <div class="h-1.5 rounded-full bg-slate-100 dark:bg-slate-800 shadow-inner overflow-hidden mt-1">
                                            <div class="h-full bg-gradient-to-r from-[var(--gold-400)] to-[var(--gold-600)] rounded-full shadow-[0_0_10px_rgba(212,175,55,0.2)]" style="width: {{ $progress }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-3 relative z-10 pt-2 border-t border-slate-100/50 dark:border-white/5">
                                    <a class="btn-soft py-2.5 text-[9px] font-black uppercase tracking-widest flex items-center justify-center gap-2 rounded-xl transition-all hover:bg-[var(--gold-50)] dark:hover:bg-[var(--gold-900)]/20" href="{{ route('goals.edit', $goal) }}" data-i18n="adjustGoal">
                                        <i class="bi bi-sliders text-xs"></i> ضبط الهدف
                                    </a>
                                    <form action="{{ route('goals.destroy', $goal) }}" method="POST" onsubmit="return confirm(i18n[currentLang()].deleteConfirm);" class="h-full">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-soft text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 w-full py-2.5 text-[9px] font-black uppercase tracking-widest flex items-center justify-center gap-2 rounded-xl" data-i18n="finalDelete">
                                            <i class="bi bi-trash text-xs"></i> حذف نهائي
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>



    @push('scripts')
    <script>
        (function(){
            const pad = (n)=> String(n).padStart(2,'0');
            const fmt = (d)=> `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
            const startOfWeek = (d)=> { const diff = (d.getDay() + 6) % 7; return new Date(d.getFullYear(), d.getMonth(), d.getDate() - diff); };
            const endOfWeek = (d)=> { const diff = (d.getDay() + 6) % 7; return new Date(d.getFullYear(), d.getMonth(), d.getDate() + (6 - diff)); };
            const startOfMonth = (d)=> new Date(d.getFullYear(), d.getMonth(), 1);
            const endOfMonth = (d)=> new Date(d.getFullYear(), d.getMonth()+1, 0);
            const startOfYear = (d)=> new Date(d.getFullYear(), 0, 1);
            const endOfYear = (d)=> new Date(d.getFullYear(), 12, 0);

            // 1. Language sync for export links
            const currentLang = localStorage.getItem('preferredLanguage') || 'ar';
            document.querySelectorAll('a[href*="/reports/export"]').forEach(link => {
                const url = new URL(link.href);
                url.searchParams.set('lang', currentLang);
                link.href = url.toString();
            });

            // 2. Filter Buttons Range Logic
            document.querySelectorAll('[data-range]')?.forEach(btn=>{
                btn.addEventListener('click', ()=>{
                    const now = new Date();
                    const kind = btn.dataset.range;
                    let s, e;
                    if (kind === 'week') { s = startOfWeek(now); e = endOfWeek(now); }
                    else if (kind === 'month') { s = startOfMonth(now); e = endOfMonth(now); }
                    else if (kind === 'year') { s = startOfYear(now); e = endOfYear(now); }
                    if (!s || !e) return;
                    const url = new URL(`{{ route('reports.index') }}` , window.location.origin);
                    url.searchParams.set('start', fmt(s));
                    url.searchParams.set('end', fmt(e));
                    window.location = url.toString();
                });
            });

            // 3. Export Dropdown Logic (Final Robust Fix)
            const expToggle = document.getElementById('exportDropToggle');
            const expMenu = document.getElementById('exportDropMenu');
            const expShell = document.getElementById('exportDropShell');

            if (expToggle && expMenu && expShell) {
                expToggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    expMenu.classList.toggle('hidden');
                    console.log('Export dropdown toggled');
                });
                document.addEventListener('click', (e) => {
                    if (!expShell.contains(e.target)) expMenu.classList.add('hidden');
                });
            }

            // 5. Load AI Insights for Reports (Unified Clean Logic)
            const aiContainer = document.getElementById('ai-insights-container');
            if (aiContainer) {
                const urlParams = new URLSearchParams(window.location.search);
                const start = urlParams.get('start') || '{{ $start ?? now()->startOfMonth()->toDateString() }}';
                const end = urlParams.get('end') || '{{ $end ?? now()->endOfMonth()->toDateString() }}';
                
                fetch(`{{ route('ai.insights.reports') }}?start=${start}&end=${end}`)
                .then(res => res.json())
                .then(data => {
                    aiContainer.innerHTML = ''; // Clear skeletons
                    if (data.status === 'success' && data.aiInsights && data.aiInsights.length > 0) {
                        data.aiInsights.forEach(insight => {
                            aiContainer.insertAdjacentHTML('beforeend', renderInsightCard(insight));
                        });
                        if (window.applyLanguage) applyLanguage(localStorage.getItem('preferredLanguage') || 'ar');
                    } else {
                        aiContainer.innerHTML = `
                            <div class="col-span-full text-center py-12 opacity-70 animate-enter">
                                <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-200 dark:border-slate-700/50 shadow-inner">
                                    <i class="bi bi-robot text-2xl text-[var(--gold-500)]"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-500" data-i18n="noInsightsFound">لا توجد تحليلات متطورة كافية لهذه الفترة لتوليد توصيات.</p>
                            </div>
                        `;
                    }
                })
                .catch(err => {
                    console.error('Failed to load reports AI', err);
                    aiContainer.innerHTML = '<p class="col-span-full text-center text-rose-400 font-bold py-12">فشل تحميل التحليلات الاستباقية.</p>';
                });
            }

            function renderInsightCard(insight) {
                const isWarning = insight.type === 'warning' || insight.type === 'danger';
                const isSuccess = insight.type === 'success';
                const statusClass = isSuccess ? 'ai-card-success' : (isWarning ? (insight.type === 'danger' ? 'ai-card-danger' : 'ai-card-warning') : 'ai-card-info');
                const textClass = isWarning ? (insight.type === 'danger' ? 'text-rose-600' : 'text-amber-600') : (isSuccess ? 'text-emerald-600' : 'text-indigo-600');
                const emoji = isSuccess ? '🏆' : (isWarning ? '⚡' : '💡');
                const severity = (insight.priority ?? 5) >= 9 ? 'مرتفع' : ((insight.priority ?? 5) >= 6 ? 'متوسط' : 'منخفض');
                const catQuery = encodeURIComponent(insight.category || '');

                return `
                    <div class="ai-insight-card p-6 ${statusClass} group hover:scale-[1.03] transition-all duration-500 animate-enter backdrop-blur-xl border border-white/20 dark:border-white/5 shadow-[0_8px_32px_rgba(0,0,0,0.08)] hover:shadow-[0_20px_48px_rgba(0,0,0,0.15)] rounded-[32px] relative overflow-hidden">
                        <div class="absolute inset-0 bg-white/10 dark:bg-slate-900/10 pointer-events-none"></div>
                        <div class="flex items-start gap-4 relative z-10">
                            <div class="w-14 h-14 rounded-[22px] bg-white/40 backdrop-blur-md flex items-center justify-center text-3xl shadow-sm border border-white/30 transition-transform duration-500 group-hover:rotate-6">
                                <span class="group-hover:animate-bounce">${emoji}</span>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <h6 class="font-black ${textClass} text-lg mb-0 tracking-tight">${insight.title}</h6>
                                        <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-white/60 dark:bg-slate-800/60 text-slate-600 dark:text-slate-200 border border-white/40">${severity}</span>
                                    </div>
                                    ${insight.priority === 'high' || insight.priority >= 9 ? `
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-rose-500/10 text-[8px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-widest border border-rose-500/20 shadow-[0_0_15px_rgba(244,63,94,0.1)]">
                                            <i class="bi bi-fire animate-pulse text-rose-500"></i> <span data-i18n="critical">أولوية عليا</span>
                                        </span>` : ''}
                                </div>
                                <p class="text-slate-800 dark:text-slate-200 text-sm leading-relaxed font-bold mb-6">
                                    ${insight.message}
                                </p>
                                <div class="flex items-center gap-3 flex-wrap">
                                    <a class="px-6 py-2.5 rounded-[16px] bg-white/70 dark:bg-slate-800/60 text-[10px] font-black border border-slate-200/60 shadow-sm hover:scale-105 transition" href="{{ route('transactions.index') }}?q=${catQuery}">
                                        <i class="bi bi-list-task mr-1 text-indigo-600"></i> معاملات الفئة
                                    </a>
                                    <a class="px-6 py-2.5 rounded-[16px] bg-white/70 dark:bg-slate-800/60 text-[10px] font-black border border-slate-200/60 shadow-sm hover:scale-105 transition" href="{{ route('budgets.index') }}">
                                        <i class="bi bi-wallet2 mr-1 text-amber-600"></i> إدارة الميزانيات
                                    </a>
                                    <a class="px-6 py-2.5 rounded-[16px] bg-white/70 dark:bg-slate-800/60 text-[10px] font-black border border-slate-200/60 shadow-sm hover:scale-105 transition" href="{{ route('goals.index') }}">
                                        <i class="bi bi-piggy-bank mr-1 text-emerald-600"></i> تعديل الأهداف
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

        })();
    </script>
    @endpush
@endsection
