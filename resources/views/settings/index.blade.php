@extends('layouts.app')

@section('content')
    <div class="card card-soft p-4 mb-4" style="background: linear-gradient(135deg, var(--brand-start), var(--brand-mid)); color: var(--button-text);">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <div class="fw-bold fs-5">لوحة التحكم بالإعدادات</div>
                <div class="text-sm" style="opacity: .9;">تبديل سريع للمظهر واللغة، مع وصول فوري للأقسام الهامة.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-light" type="button" onclick="applyTheme('light')"><i class="bi bi-brightness-high"></i> وضع فاتح</button>
                <button class="btn btn-outline-light" type="button" onclick="applyTheme('dark')"><i class="bi bi-moon"></i> وضع داكن</button>
                <button class="btn btn-outline-light" type="button" onclick="applyLanguage('ar')"><i class="bi bi-translate"></i> العربية</button>
                <button class="btn btn-outline-light" type="button" onclick="applyLanguage('en')"><i class="bi bi-translate"></i> English</button>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card card-soft h-100 p-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="text-muted">التنقل السريع</div>
                        <div class="fw-bold">حساب، تقارير، معاملات</div>
                    </div>
                    <span class="chip"><i class="bi bi-lightning-charge"></i> سريع</span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @if(auth()->user()?->is_admin)
                        <a class="btn btn-outline-secondary" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> لوحة المدير</a>
                        <a class="btn btn-outline-secondary" href="{{ route('admin.users') }}"><i class="bi bi-people"></i> المستخدمون</a>
                        <a class="btn btn-outline-secondary" href="{{ route('admin.loginAttempts') }}"><i class="bi bi-shield-lock"></i> سجلات الدخول</a>
                        <a class="btn btn-outline-secondary" href="{{ route('admin.categories') }}"><i class="bi bi-tags"></i> الفئات</a>
                    @else
                        <a class="btn btn-outline-secondary" href="{{ route('profile.index') }}"><i class="bi bi-person"></i> الملف الشخصي</a>
                        <a class="btn btn-outline-secondary" href="{{ route('reports.index') }}"><i class="bi bi-file-earmark-bar-graph"></i> التقارير</a>
                        <a class="btn btn-outline-secondary" href="{{ route('transactions.index') }}"><i class="bi bi-list"></i> المعاملات</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-soft h-100 p-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="text-muted">الإعدادات العامة</div>
                        <div class="fw-bold">لغة، مظهر، عملة</div>
                    </div>
                    <i class="bi bi-gear"></i>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">اللغة / Language</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-light" id="set-ar">العربية</button>
                            <button class="btn btn-light" id="set-en">English</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">المظهر / Appearance</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-light" id="set-light">فاتح</button>
                            <button class="btn btn-light" id="set-dark">داكن</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">العملة / Currency</label>
                        <select class="form-select" id="currency">
                            <option value="LYD">الدينار الليبي (د.ل)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-soft p-3 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <div class="text-muted">الإعدادات المتقدمة</div>
                <div class="fw-bold">إشعارات، خصوصية، إدارة الحساب</div>
            </div>
            <i class="bi bi-shield-check"></i>
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">الإشعارات / Notifications</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="notifSwitch">
                    <label class="form-check-label" for="notifSwitch">مفعل</label>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">الخصوصية / Privacy</label>
                <select class="form-select" id="privacy">
                    <option value="public">عام</option>
                    <option value="private">خاص</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label text-danger">حذف الحساب / Delete Account</label>
                <button class="btn btn-outline-danger w-100" id="deleteAccount"><i class="bi bi-trash"></i> حذف الحساب</button>
            </div>
        </div>
    </div>

    <script>
        const setStore = (k,v) => localStorage.setItem(k, v);
        document.getElementById('set-ar').onclick = () => { applyLanguage('ar'); qirataeToast('success','تم ضبط اللغة إلى العربية'); };
        document.getElementById('set-en').onclick = () => { applyLanguage('en'); qirataeToast('success','Language set to English'); };
        document.getElementById('set-light').onclick = () => { applyTheme('light'); qirataeToast('info','تم ضبط الوضع الفاتح'); };
        document.getElementById('set-dark').onclick = () => { applyTheme('dark'); qirataeToast('info','تم ضبط الوضع الداكن'); };
        document.getElementById('currency').onchange = (e) => { setStore('qiratae-currency', e.target.value); qirataeToast('success','تم تغيير العملة'); };
        document.getElementById('notifSwitch').onchange = (e) => { setStore('qiratae-notifications', e.target.checked ? 'on' : 'off'); qirataeToast('info','تم تحديث الإشعارات'); };
        document.getElementById('privacy').onchange = (e) => { setStore('qiratae-privacy', e.target.value); qirataeToast('info','تم تحديث الخصوصية'); };
        document.getElementById('deleteAccount').onclick = () => {
            if (confirm('سيتم حذف جميع بيانات المستخدم. هل أنت متأكد؟')) {
                qirataeToast('error','هذه مجرد واجهة — يلزم تكامل حساب فعلي');
            }
        };
    </script>
@endsection
