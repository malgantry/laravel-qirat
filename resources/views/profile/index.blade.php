@extends('layouts.app')

@section('content')
    @php
        $avatarUrl = $user?->avatar_path ? asset('storage/'.$user->avatar_path) : null;
        $initial = mb_substr($user?->name ?? 'م', 0, 1);
    @endphp

    <div class="form-hero" dir="rtl">
        <div class="form-card">
            <div class="accent-bar"></div>
            <div class="card-body space-y-4">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div class="d-flex align-items-start gap-3">
                    <div class="p-2 rounded-circle" style="background: rgba(201,162,39,0.15); color: #c9a227;">
                        <i class="bi bi-person-lines-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">الملف الشخصي</h5>
                        <div class="form-sub">حدّث صورتك واسمك مع معاينة فورية.</div>
                    </div>
                    </div>
                    @if(auth()->user()?->is_admin)
                        <span class="chip" style="background: var(--brand-soft); color: var(--text-primary);">
                            <i class="bi bi-shield-lock me-1"></i> مدير النظام
                        </span>
                    @endif
                </div>

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4" novalidate>
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card-soft border border-slate-200 dark:border-slate-700 rounded-2xl p-3 text-center h-100">
                                <div class="avatar-icon mx-auto mb-3" id="avatarPreview" style="background: linear-gradient(135deg, var(--brand-start), var(--brand-mid)); overflow: hidden;">
                                    @if($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="الصورة الحالية" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <span class="fw-bold" style="color: #fff; font-size: 1.4rem;">{{ $initial }}</span>
                                    @endif
                                </div>
                                <label class="btn btn-outline-primary w-100" for="avatarInput"><i class="bi bi-camera"></i> تغيير الصورة</label>
                                <input type="file" name="avatar" id="avatarInput" accept="image/*" class="d-none">
                                <small class="text-muted d-block mt-2">png أو jpg، بحد أقصى 2MB</small>
                                @error('avatar')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label text-slate-800 dark:text-slate-100">الاسم</label>
                                    <input type="text" name="name" class="form-control pill-input" value="{{ old('name', $user?->name) }}" required aria-required="true" placeholder="اسم العرض">
                                    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-slate-800 dark:text-slate-100">البريد الإلكتروني</label>
                                    <input type="email" class="form-control pill-input" value="{{ $user?->email }}" disabled>
                                    <small class="text-muted">يمكن تحديث البريد من قسم الإعدادات الأمنية لاحقاً.</small>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex flex-column flex-sm-row gap-2 mt-2">
                                        <button class="btn btn-primary flex-1" type="submit"><i class="bi bi-save"></i> حفظ التعديلات</button>
                                        <a href="{{ route('settings.index') }}" class="btn btn-light flex-1"><i class="bi bi-gear"></i> الإعدادات</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-3">
        @if(auth()->user()?->is_admin)
            <div class="col-md-4">
                <div class="card-soft p-3 h-100 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold">لوحة المدير</div>
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <p class="text-muted mb-2">إدارة النظام واستعراض أحدث الأنشطة.</p>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">فتح لوحة المدير</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-soft p-3 h-100 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold">إدارة المستخدمين</div>
                        <i class="bi bi-people"></i>
                    </div>
                    <p class="text-muted mb-2">تفعيل/تعطيل الحسابات ومنح صلاحيات الإدارة.</p>
                    <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">فتح المستخدمين</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-soft p-3 h-100 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold">سجلات الدخول</div>
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <p class="text-muted mb-2">مراجعة محاولات الدخول ومتابعة الأمان.</p>
                    <a href="{{ route('admin.loginAttempts') }}" class="btn btn-outline-secondary">عرض السجلات</a>
                </div>
            </div>
        @else
            <div class="col-md-4">
                <div class="card-soft p-3 h-100 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold">التقرير الشهري</div>
                        <i class="bi bi-bar-chart"></i>
                    </div>
                    <p class="text-muted mb-2">افتح صفحة الإحصائيات لعرض تقرير شهري.</p>
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-primary">فتح الإحصائيات</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-soft p-3 h-100 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold">مراجعة الأهداف</div>
                        <i class="bi bi-bullseye"></i>
                    </div>
                    <p class="text-muted mb-2">انتقل إلى صفحة الأهداف لمتابعة التقدم.</p>
                    <a href="{{ route('goals.index') }}" class="btn btn-outline-secondary">فتح الأهداف</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-soft p-3 h-100 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold">إعدادات</div>
                        <i class="bi bi-sliders"></i>
                    </div>
                    <p class="text-muted mb-2">غيّر اللغة والمظهر والعملة والمزيد.</p>
                    <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">فتح الإعدادات</a>
                </div>
            </div>
        @endif
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
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });
    </script>
    @endpush
@endsection
