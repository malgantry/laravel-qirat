@extends('layouts.app')
@section('hideNav', true)

@section('content')
<div class="min-h-screen selection:bg-[var(--gold-400)] selection:text-white" dir="rtl">
    <div class="max-w-6xl mx-auto px-4 pt-4 pb-2 flex flex-col items-center gap-6 relative z-10 animate-enter">
        <div class="landing-logo p-3 sm:p-4 rounded-[2rem] sm:rounded-[2.5rem] bg-white dark:bg-slate-800/30 backdrop-blur-2xl border border-white/40 shadow-[0_20px_50px_rgba(0,0,0,0.12)] transition-all hover:scale-105 duration-500 ring-1 ring-gold-200/20 theme-icon-wrapper">
            <img src="{{ asset('images/logo-qirat-premium.jpg') }}" alt="شعار قيراط" class="h-20 sm:h-28 w-auto light-only rounded-2xl shadow-sm" loading="lazy">
            <img src="{{ asset('images/logo-dark.jpg') }}" alt="شعار قيراط" class="h-20 sm:h-28 w-auto dark-only" loading="lazy">
        </div>
        <div class="text-center space-y-2">
            <h2 class="text-2xl sm:text-3xl font-heading font-black text-slate-900 dark:text-white tracking-tight" data-i18n="appBrand">قيراط</h2>
            <p class="text-xs sm:text-sm font-bold text-text-muted tracking-[0.2em] uppercase opacity-80" data-i18n="appTagline">تحكم كامل بمدخراتك</p>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="landing-hero pt-4 pb-12 px-4 relative z-10" dir="rtl">
        <div class="max-w-6xl mx-auto card-premium p-6 sm:p-12 lg:p-16 dark:shadow-[0_40px_100px_rgba(0,0,0,0.6)] text-slate-900 dark:text-white relative overflow-hidden group !rounded-[32px] sm:!rounded-[40px]">
            <!-- Glass Overlay Layer -->
            <div class="absolute inset-0 bg-black/20 backdrop-blur-[2px] opacity-0 group-hover:opacity-10 transition-opacity duration-700"></div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center relative z-20">
                <div class="space-y-8 sm:space-y-10 animate-enter text-center lg:text-right" style="animation-delay: 0.1s">
                    <div class="flex flex-col sm:flex-row items-center gap-3 justify-center lg:justify-start">
                        <div class="inline-flex items-center gap-2 bg-slate-100/80 dark:bg-white/5 backdrop-blur-md border border-slate-200 dark:border-white/10 text-[10px] sm:text-sm px-3 sm:px-4 py-2 rounded-xl text-slate-600 dark:text-text-muted shadow-sm order-2 sm:order-1">
                            <i class="bi bi-shield-lock-fill text-[var(--gold-500)]"></i>
                            <span class="font-bold tracking-tight" data-i18n="secureFinManagement">إدارة مالية آمنة • خوارزميات متقدمة</span>
                        </div>
                        
                        <button id="themeToggle" class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl border border-transparent hover:border-slate-200 dark:hover:border-slate-700 hover:bg-white/50 dark:hover:bg-slate-800/50 text-slate-600 dark:text-amber-400 flex items-center justify-center transition-all duration-300 theme-icon-wrapper group order-1 sm:order-2" data-i18n-title="toggleTheme">
                            <i class="bi bi-brightness-high light-only text-lg group-hover:rotate-12 transition-transform"></i>
                            <i class="bi bi-moon-stars dark-only text-lg group-hover:-rotate-12 transition-transform"></i>
                        </button>
                    </div>
                    
                    <h1 class="text-3xl sm:text-5xl lg:text-7xl font-heading font-black leading-[1.2] lg:leading-[1.1] tracking-tight text-text-main">
                        <span class="block mb-2 sm:mb-3" data-i18n="appName">قيراط:</span>
                        <span class="text-[var(--gold-600)] dark:text-[#EAB308] block mb-3 sm:mb-4" data-i18n="controlPower">قوة السيطرة</span>
                        <span class="block text-2xl sm:text-4xl lg:text-5xl font-bold opacity-80 dark:opacity-100" data-i18n="onFuture">على مستقبلك المالي</span>
                    </h1>

                    <p class="text-lg sm:text-xl text-text-muted leading-[1.8] font-body max-w-xl" data-i18n="landingSummary">
                        تتبّع معاملاتك بدقة متناهية، حدّد أهداف الادخار، واطّلع على إحصاءات متقدمة بتصميم يجسد الفخامة والوضوح.
                    </p>

                    <div class="flex flex-wrap gap-4 pt-6">
                        <a href="{{ route('login') }}" class="btn-gold group px-8 py-4 text-lg font-bold">
                            <i class="bi bi-box-arrow-in-right group-hover:translate-x-1 transition-transform"></i>
                            <span data-i18n="login">تسجيل الدخول</span>
                        </a>
                        <a href="{{ route('register') }}" class="px-8 py-4 text-lg font-bold rounded-2xl bg-slate-100 dark:bg-white/10 backdrop-blur-xl border border-slate-200 dark:border-white/20 hover:bg-slate-200 dark:hover:bg-white/20 text-slate-800 dark:text-white transition-all shadow-sm">
                            <span data-i18n="register">إنشاء حساب</span>
                        </a>
                    </div>

                    <div class="flex flex-wrap gap-6 text-sm font-medium text-text-muted">
                        <div class="flex items-center gap-2"><i class="bi bi-phone-vibrate text-[var(--gold-500)] dark:text-[var(--gold-400)] text-xl"></i><span data-i18n="fullyCompatible">متوافق كلياً</span></div>
                        <div class="flex items-center gap-2"><i class="bi bi-palette2 text-[var(--gold-500)] dark:text-[var(--gold-400)] text-xl"></i><span data-i18n="premiumInterfaces">واجهات فاخرة</span></div>
                        <div class="flex items-center gap-2"><i class="bi bi-file-earmark-pdf text-[var(--gold-500)] dark:text-[var(--gold-400)] text-xl"></i><span data-i18n="proReports">تقارير احترافية</span></div>
                    </div>
                </div>

                <!-- Floating Demo Card -->
                <div class="relative animate-enter" style="animation-delay: 0.3s">
                    <div class="absolute -inset-4 bg-gradient-to-tr from-[var(--gold-400)] to-transparent opacity-20 blur-3xl rounded-full"></div>
                    <div class="goal-card-premium p-8 shadow-2xl border border-white/40 dark:border-white/10 relative z-10 backdrop-blur-2xl">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-widest text-text-muted mb-1" data-i18n="advancedWallet">المحفظة المتقدمة</p>
                                <h3 class="text-2xl font-bold text-text-main" data-i18n="overview">نظرة عامة</h3>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-lg">
                                <i class="bi bi-graph-up-arrow text-xl"></i>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6 mb-8">
                            <div class="stat-inner p-4 rounded-3xl bg-slate-50 dark:bg-white/5 border border-slate-200/50 dark:border-white/10">
                                <p class="text-xs font-medium text-slate-500 dark:text-text-muted mb-2" data-i18n="currentBalance">إجمالي الرصيد</p>
                                <p class="text-2xl font-heading font-black text-[var(--gold-600)] dark:text-[#fbbf24]">12,350<span class="text-sm mr-1" data-i18n="lydSymbol">د.ل</span></p>
                            </div>
                            <div class="stat-inner p-4 rounded-3xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/50 dark:border-white/5">
                                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-2" data-i18n="growthRate">معدل النمو</p>
                                <p class="text-2xl font-heading font-black text-emerald-600 dark:text-emerald-400">+24%</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-bold text-slate-700 dark:text-slate-300" data-i18n="homeGoal">هدف شراء منزل</span>
                                <span class="text-[var(--gold-600)] font-black">70%</span>
                            </div>
                            <div class="h-4 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden p-1 border border-slate-200/50 dark:border-slate-700/50 shadow-inner">
                                <div class="h-full rounded-full bg-gradient-to-r from-[var(--gold-500)] to-[var(--gold-300)] shadow-[0_0_10px_rgba(212,175,55,0.4)]" style="width: 70%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="max-w-6xl mx-auto px-4 py-20 space-y-24 relative z-10" dir="rtl">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="card-premium p-8 group hover:-translate-y-2 transition-all duration-500">
                <div class="w-16 h-16 rounded-2xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600 dark:text-amber-400 mb-6 group-hover:scale-110 transition-transform shadow-sm">
                    <i class="bi bi-lightning-charge-fill text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-4" data-i18n="fastPerformance">أداء فائق السرعة</h3>
                <p class="text-text-muted leading-relaxed" data-i18n="fastPerformanceDesc">
                    عمليات فورية وإحصاءات لحظية تظهر بمجرد إضافة المعاملة، دون أي انتظار.
                </p>
            </div>

            <div class="card-premium p-8 group hover:-translate-y-2 transition-all duration-500">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 dark:text-blue-400 mb-6 group-hover:scale-110 transition-transform shadow-sm">
                    <i class="bi bi-shield-check text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-4" data-i18n="totalPrivacy">خصوصية مطلقة</h3>
                <p class="text-text-muted leading-relaxed" data-i18n="privacyDesc">
                    بياناتك مشفرة ومحمية بأعلى المعايير الأمنية، لأن أمانك المالي أولويتنا.
                </p>
            </div>

            <div class="card-premium p-8 group hover:-translate-y-2 transition-all duration-500">
                <div class="w-16 h-16 rounded-2xl bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center text-purple-600 dark:text-purple-400 mb-6 group-hover:scale-110 transition-transform shadow-sm">
                    <i class="bi bi-stars text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-4" data-i18n="advancedAnalysisTitle">تحليلات متقدمة</h3>
                <p class="text-text-muted leading-relaxed" data-i18n="aiIntelligenceDesc">
                    نظام تحليل متقدم يحلل سلوكك المالي ويقدم نصائح مخصصة لزيادة ادخارك.
                </p>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="card-premium p-12 rounded-[40px] text-center relative overflow-hidden group">
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-[var(--gold-400)]/10 blur-[100px] rounded-full"></div>
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-[var(--navy-600)]/10 blur-[100px] rounded-full"></div>
            
            <div class="max-w-3xl mx-auto space-y-8 relative z-10">
                <h2 class="text-3xl sm:text-4xl font-heading font-black text-text-main" data-i18n="readyToElevate">جاهز للارتقاء بحياتك المالية؟</h2>
                <p class="text-lg text-text-muted font-medium" data-i18n="ctaSub">ابدأ رحلتك اليوم نحو الاستقرار والرفاهية مع قيراط.</p>
                <div class="flex justify-center gap-4">
                    <a href="{{ route('register') }}" class="btn-gold px-10 py-5 text-xl font-bold shadow-2xl" data-i18n="getStartedFree">ابدأ الآن مجاناً</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Light -->
    <footer class="max-w-6xl mx-auto px-4 py-12 border-t border-slate-200 dark:border-slate-800 relative z-10">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-3">
                <div class="theme-icon-wrapper">
                    <img src="{{ asset('images/logo-qirat-premium.jpg') }}" alt="Logo" class="h-8 w-auto grayscale opacity-50 light-only">
                    <img src="{{ asset('images/logo-dark.jpg') }}" alt="Logo" class="h-8 w-auto grayscale opacity-50 dark-only">
                </div>
                <span class="text-slate-400 dark:text-slate-600 font-bold" data-i18n="copyright" data-i18n-vars='{"year": "{{ date("Y") }}"}'>© {{ date('Y') }} قيراط. جميع الحقوق محفوظة.</span>
            </div>
            <div class="flex gap-8 text-slate-400 dark:text-slate-600 font-medium">
                <a href="#" class="hover:text-[var(--gold-500)] transition-colors" data-i18n="aboutUs">عن الشركة</a>
                <a href="#" class="hover:text-[var(--gold-500)] transition-colors" data-i18n="privacy">الخصوصية</a>
                <a href="#" class="hover:text-[var(--gold-500)] transition-colors" data-i18n="terms">الشروط</a>
            </div>
        </div>
    </footer>
</div>
@endsection
