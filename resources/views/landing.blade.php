@extends('layouts.app')

@section('content')
<div class="landing-hero py-6 px-3 sm:px-4" dir="rtl">
    <div class="max-w-6xl mx-auto bg-gradient-to-br from-black via-[#1a1208] to-[#c9a227] rounded-3xl p-6 sm:p-8 lg:p-10 shadow-2xl border border-[#2b241b] text-white">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
            <div class="space-y-4">
                <div class="inline-flex items-center gap-2 bg-white/10 border border-white/15 text-sm px-3 py-2 rounded-full on-dark">
                    <i class="bi bi-shield-check"></i>
                    <span>إدارة مالية آمنة – يدعم العربية</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold leading-tight on-dark">قيراط المالي: سيطرة كاملة على الدخل والمصروفات</h1>
                <p class="text-base sm:text-lg text-white/80 max-w-2xl on-dark">تتبّع معاملاتك، حدّد أهداف الادخار، واطّلع على إحصاءات لحظية بتصميم متجاوب يلائم الجوال واللوحي والحاسب.</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('login') }}" class="btn primary-gradient px-5 py-3 text-base shadow-lg">تسجيل الدخول</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-secondary bg-white/10 border-white/20 text-white hover:bg-white/15">إنشاء حساب جديد</a>
                </div>
                <div class="flex flex-wrap gap-4 text-sm text-white/80 on-dark">
                    <div class="flex items-center gap-2"><i class="bi bi-phone"></i><span>متجاوب لكل الشاشات</span></div>
                    <div class="flex items-center gap-2"><i class="bi bi-moon-stars"></i><span>وضع داكن وفاتح</span></div>
                    <div class="flex items-center gap-2"><i class="bi bi-cloud-arrow-down"></i><span>حفظ وتصدير التقارير</span></div>
                </div>
            </div>
            <div class="bg-white/90 text-slate-900 rounded-2xl p-5 sm:p-6 shadow-xl border border-white/60">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <div class="text-sm text-slate-600">نظرة سريعة</div>
                        <div class="text-xl font-bold text-slate-900">لوحة التحكم المبسطة</div>
                    </div>
                    <span class="pill-badge pill-income">+ دخل</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    <div class="p-3 rounded-xl bg-slate-100">
                        <div class="text-slate-600 text-sm">الرصيد</div>
                        <div class="text-2xl font-bold text-slate-900">12,350 د.ل</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-100">
                        <div class="text-slate-600 text-sm">معدل الادخار</div>
                        <div class="text-2xl font-bold text-emerald-600">+18%</div>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 p-3 bg-white">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-slate-600 text-sm">تقدم الأهداف</span>
                        <span class="text-slate-900 font-semibold">70%</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-slate-200 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-[#c9a227] to-[#b07f2f]" style="width: 70%"></div>
                    </div>
                </div>
                <p class="text-sm text-slate-600 mt-3">يعمل بسلاسة على الهاتف، التابلت، والحاسب — تجربة واحدة متجاوبة.</p>
            </div>
        </div>
    </div>
</div>

<div class="max-w-6xl mx-auto px-3 sm:px-4 py-8 space-y-8" dir="rtl">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-2 mb-2"><i class="bi bi-bar-chart"></i><h3 class="m-0 text-lg font-bold">تتبّع لحظي</h3></div>
            <p class="text-slate-600 dark:text-slate-300">أضف معاملاتك فوراً وشاهد الأثر على الرصيد والميزانية مباشرة.</p>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-2 mb-2"><i class="bi bi-bullseye"></i><h3 class="m-0 text-lg font-bold">أهداف وادخار</h3></div>
            <p class="text-slate-600 dark:text-slate-300">حدّد أهداف ادخار واضحة مع حلقات تقدم ورسائل تذكير.</p>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-2 mb-2"><i class="bi bi-pie-chart"></i><h3 class="m-0 text-lg font-bold">تقارير ذكية</h3></div>
            <p class="text-slate-600 dark:text-slate-300">اكتشف أعلى فئات الإنفاق وصدّر تقارير PDF أو Excel بسهولة.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="text-xl font-bold mb-1">خطوات البدء السريعة</h3>
                <p class="text-slate-600 dark:text-slate-300 mb-0">واجهه تمهيدية، تسجيل الدخول، ثم لوحة التحكم المتجاوبة.</p>
            </div>
            <a href="{{ route('login') }}" class="btn primary-gradient px-4">ابدأ الآن</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
                <div class="flex items-center gap-2 mb-2"><span class="pill-badge">١</span><strong>تعرّف على المزايا</strong></div>
                <p class="text-slate-600 dark:text-slate-300 mb-0">استعرض الواجهة التعريفية المتجاوبة لمعرفة كل إمكانيات التطبيق.</p>
            </div>
            <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
                <div class="flex items-center gap-2 mb-2"><span class="pill-badge">٢</span><strong>سجّل دخولك</strong></div>
                <p class="text-slate-600 dark:text-slate-300 mb-0">أنشئ حساباً أو سجّل الدخول، مع حماية ووضوح في الحقول.</p>
            </div>
            <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40">
                <div class="flex items-center gap-2 mb-2"><span class="pill-badge">٣</span><strong>ابدأ الإدارة</strong></div>
                <p class="text-slate-600 dark:text-slate-300 mb-0">أضف معاملات، حدّد أهدافاً، واطّلع على الإحصاءات بمرونة على أي جهاز.</p>
            </div>
        </div>
    </div>
</div>
@endsection
