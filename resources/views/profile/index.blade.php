@extends('layouts.app')

@section('content')
    @php
        $avatarUrl = $user?->avatar_path ? asset('storage/'.$user->avatar_path) : null;
        $initial = mb_substr($user?->name ?? 'M', 0, 1);
    @endphp

    <div class="max-w-4xl mx-auto space-y-8 animate-enter">
        <!-- Main Profile Card -->
        <div class="card-premium overflow-hidden border-none shadow-2xl">
            <!-- Header with Gradient Background -->
            <div class="relative h-32 bg-gradient-to-r from-[var(--navy-800)] via-[var(--navy-700)] to-[var(--navy-600)] dark:from-slate-900 dark:via-slate-950 dark:to-slate-900 p-8 flex items-end border-b border-white/10">
                <div class="absolute inset-0 opacity-20 pointer-events-none bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
                <div class="absolute -bottom-12 right-8 z-20">
                    <div class="w-32 h-32 rounded-3xl border-4 border-white dark:border-slate-900 shadow-2xl overflow-hidden relative group" id="avatarPreview" style="background: linear-gradient(135deg, var(--gold-500), var(--gold-600));">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="الصورة الحالية" data-i18n-alt="currentAvatar" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        @else
                            <span class="w-full h-full flex items-center justify-center text-white text-5xl font-black font-heading">{{ $initial }}</span>
                        @endif
                        <label for="avatarInput" class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                            <i class="bi bi-camera text-white text-2xl"></i>
                        </label>
                    </div>
                </div>
                <div class="relative z-10 mr-44 mb-2">
                    <h2 class="text-3xl font-heading font-black text-white tracking-tight">{{ $user?->name }}</h2>
                    <p class="text-white/70 font-medium text-sm">{{ $user?->email }}</p>
                </div>
            </div>

            <!-- Content Area -->
            <div class="p-8 pt-16">
                <div class="flex items-center justify-between mb-8 border-b border-[var(--border-light)] pb-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/20 text-amber-500 flex items-center justify-center text-xl shadow-inner border border-amber-100/50 dark:border-amber-900/30">
                            <i class="bi bi-person-badge-fill"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-heading font-bold text-text-main" data-i18n="accountSettings">إعدادات الحساب</h3>
                            <p class="text-text-muted text-sm font-medium" data-i18n="updatePersonalInfo">قم بتحديث معلوماتك الشخصية وصورة الملف.</p>
                        </div>
                    </div>
                    @if(auth()->user()?->is_admin)
                        <div class="px-4 py-2 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 text-slate-700 dark:text-slate-300 text-xs font-black border border-white/50 dark:border-slate-700/50 shadow-sm">
                            <i class="bi bi-shield-check-fill text-emerald-500 me-2"></i> <span data-i18n="adminLabel">مدير النظام</span>
                        </div>
                    @endif
                </div>

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8" novalidate>
                    @csrf
                    <input type="file" name="avatar" id="avatarInput" accept="image/*" class="hidden">
                    @error('avatar')
                        <div class="invalid-feedback-premium mb-4">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="block text-sm font-black text-slate-700 dark:text-slate-300 mr-2 uppercase tracking-widest text-[10px]" data-i18n="fullNameLabel">الاسم الكامل</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400 group-focus-within:text-[var(--gold-500)] transition-colors">
                                    <i class="bi bi-person"></i>
                                </div>
                                <input type="text" name="name" class="input-premium pr-11 @error('name') input-invalid @enderror" value="{{ old('name', $user?->name) }}" required minlength="3" maxlength="80" data-i18n-placeholder="displayNamePlaceholder" placeholder="Display Name">
                            </div>
                            @error('name')
                                <div class="invalid-feedback-premium">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
                        
                        <div class="space-y-2">
                            <label class="block text-sm font-black text-slate-700 dark:text-slate-300 mr-2 uppercase tracking-widest text-[10px]" data-i18n="emailLabel">البريد الإلكتروني</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400 group-focus-within:text-[var(--gold-500)] transition-colors">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <input type="email" name="email" class="input-premium pr-11 @error('email') input-invalid @enderror" value="{{ old('email', $user?->email) }}" required>
                            </div>
                            @error('email')
                                <div class="invalid-feedback-premium">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-6 flex flex-wrap gap-4">
                        <button class="btn-gold px-10 py-4 text-lg font-black shadow-xl min-w-[200px]" type="submit">
                            <i class="bi bi-check2-circle me-2"></i> <span data-i18n="saveChanges">حفظ التغييرات</span>
                        </button>
                        <a href="{{ route('settings.index') }}" class="btn-soft px-8 py-4 font-bold flex items-center justify-center">
                            <i class="bi bi-gear-wide-connected me-2"></i> <span data-i18n="advancedSettings">الإعدادات المتقدمة</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Interactive Section Title -->
        <div class="flex items-center gap-4 px-4 overflow-hidden">
            <h3 class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] whitespace-nowrap" data-i18n="quickAccessTools">الوصول السريع للأدوات</h3>
            <div class="h-px w-full bg-gradient-to-r from-slate-200 dark:from-slate-800 to-transparent"></div>
        </div>

        <!-- Luxury Quick Links & Privacy -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @php
                $links = auth()->user()?->is_admin ? [
                    ['title' => 'لوحة المدير', 'key' => 'adminDashboard', 'icon' => 'bi-speedometer2', 'desc' => 'إدارة النظام والأنشطة.', 'descKey' => 'adminDashboardDesc', 'route' => 'admin.dashboard'],
                    ['title' => 'المستخدمين', 'key' => 'manageUsers', 'icon' => 'bi-people', 'desc' => 'تفعيل الصلاحيات والحسابات.', 'descKey' => 'manageUsersDesc', 'route' => 'admin.users'],
                ] : [
                    ['title' => 'التقارير', 'key' => 'reports', 'icon' => 'bi-bar-chart-line', 'desc' => 'تحليل مالي مفصل للإنفاق.', 'descKey' => 'reportsDesc', 'route' => 'reports.index'],
                    ['title' => 'الأهداف', 'key' => 'goals', 'icon' => 'bi-bullseye', 'desc' => 'متابعة تقدم ادخارك المخطط.', 'descKey' => 'goalsDesc', 'route' => 'goals.index'],
                ];
            @endphp

            @foreach($links as $link)
                <a href="{{ route($link['route']) }}" class="card-premium p-8 group transition-all duration-500">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-slate-50 dark:bg-slate-800 text-slate-400 group-hover:text-[var(--gold-500)] group-hover:bg-[var(--gold-50)] dark:group-hover:bg-[var(--gold-900)]/20 transition-all flex items-center justify-center text-2xl shadow-inner border border-transparent group-hover:border-amber-100 dark:group-hover:border-amber-900/30">
                            <i class="bi {{ $link['icon'] }}"></i>
                        </div>
                        <i class="bi bi-arrow-up-left text-slate-300 dark:text-slate-700 opacity-0 group-hover:opacity-100 group-hover:translate-x-[-4px] transition-all"></i>
                    </div>
                    <h4 class="text-xl font-heading font-black text-slate-800 dark:text-slate-100 group-hover:text-[var(--gold-600)] transition-colors mb-2" data-i18n="{{ $link['key'] }}">{{ $link['title'] }}</h4>
                    <p class="text-sm text-text-muted font-medium leading-relaxed" data-i18n="{{ $link['descKey'] }}">{{ $link['desc'] }}</p>
                </a>
            @endforeach

            <!-- Privacy & Security Card (New) -->
            <div class="card-premium p-8 border-none shadow-xl relative overflow-hidden group">
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-emerald-500/5 blur-3xl rounded-full"></div>
                <div class="flex justify-between items-start mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500 flex items-center justify-center text-2xl shadow-inner border border-emerald-100/50 dark:border-emerald-900/30">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                </div>
                <h4 class="text-xl font-heading font-black text-slate-800 dark:text-slate-100 mb-2" data-i18n="accountSecurity">الأمان والخصوصية</h4>
                <p class="text-sm text-text-muted font-medium mb-6" data-i18n="securityDescription">تحكم في ظهور بياناتك وسياسة خصوصية الذكاء الاصطناعي.</p>
                
                <div class="space-y-4">
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-white/5">
                        <div class="flex items-center justify-between mb-2">
                             <label class="text-xs font-black text-slate-400 uppercase tracking-widest mr-1" data-i18n="walletVisibility">رؤية المحفظة</label>
                             <span id="privacy-status-badge" class="px-2 py-0.5 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-[10px] font-bold text-emerald-600 dark:text-emerald-400" data-i18n="public">PUBLIC</span>
                        </div>
                        <select class="input-premium py-2 pl-4 pr-10 appearance-none bg-white dark:bg-slate-900 text-sm border-slate-200/50 dark:border-slate-800/50" id="privacy-toggle">
                            <option value="public" data-i18n="public">عام (مرئي للمدراء)</option>
                            <option value="private" data-i18n="private">خاص (مخفي تماماً)</option>
                        </select>
                    </div>
                    <div id="ai-training-notice" class="flex gap-2 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-900/30">
                        <i class="bi bi-info-circle-fill text-amber-500 text-sm"></i>
                        <p class="text-[10px] text-amber-700 dark:text-amber-400 font-bold leading-tight" data-i18n="aiTrainingNotice">
                            عند تفعيل الوضع العام، سيتم استغلال بياناتك (بشكل مغفل) في تدريب النموذج لتحسين دقة النصائح.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Advanced Settings Link Card -->
            <a href="{{ route('settings.index') }}" class="card-premium p-8 group border-dashed border-2 bg-slate-50/30 dark:bg-slate-950/30 flex flex-col items-center justify-center text-center">
                <div class="w-14 h-14 rounded-full bg-white dark:bg-slate-800 shadow-sm flex items-center justify-center text-2xl text-slate-400 group-hover:text-[var(--gold-500)] transition-all mb-4">
                    <i class="bi bi-gear-wide-connected"></i>
                </div>
                <h4 class="font-heading font-black text-slate-800 dark:text-slate-100" data-i18n="advancedSettings">الإعدادات المتقدمة</h4>
                <p class="text-xs text-text-muted mt-2" data-i18n="fullSystemControl">تحكم كامل بالعملة، المظهر، واللغة.</p>
            </a>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('avatarInput');
            const preview = document.getElementById('avatarPreview');
            input?.addEventListener('change', (e) => {
                const file = e.target.files?.[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (ev) => {
                    preview.innerHTML = '';
                    const img = document.createElement('img');
                    img.src = ev.target?.result || '';
                    img.className = 'w-full h-full object-cover group-hover:scale-110 transition-transform duration-500';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });

            // Privacy Toggle Logic
            const privacyToggle = document.getElementById('privacy-toggle');
            const aiNotice = document.getElementById('ai-training-notice');
            const badge = document.getElementById('privacy-status-badge');
            
            const savedPrivacy = localStorage.getItem('qiratae-privacy') || 'public';
            if(privacyToggle) {
                privacyToggle.value = savedPrivacy;
                aiNotice.style.display = savedPrivacy === 'public' ? 'flex' : 'none';
                badge.textContent = savedPrivacy.toUpperCase();
                badge.className = savedPrivacy === 'public' ? 'px-2 py-0.5 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-[10px] font-bold text-emerald-600 dark:text-emerald-400' : 'px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-[10px] font-bold text-slate-500';

                privacyToggle.addEventListener('change', (e) => {
                    const val = e.target.value;
                    localStorage.setItem('qiratae-privacy', val);
                    aiNotice.style.display = val === 'public' ? 'flex' : 'none';
                    badge.textContent = val.toUpperCase();
                    badge.className = val === 'public' ? 'px-2 py-0.5 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-[10px] font-bold text-emerald-600 dark:text-emerald-400' : 'px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-[10px] font-bold text-slate-500';
                    if(window.qirataeToast) window.qirataeToast('info', val === 'public' ? 'تم تفعيل مشاركة البيانات' : 'تم تفعيل الخصوصية الكاملة');
                });
            }
        });
    </script>
    @endpush
@endsection
