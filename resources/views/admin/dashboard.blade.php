@extends('layouts.app')

@section('content')
    @php
        $palette = [
            'users' => 'var(--brand-start)',
            'categories' => 'var(--brand-end)',
        ];
    @endphp

    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-xl font-bold text-slate-900 dark:text-slate-50">لوحة إدارة النظام</h3>
        <div class="flex gap-2">
            <a href="{{ route('admin.users') }}" class="btn btn-primary">المستخدمون</a>
            <a href="{{ route('admin.categories') }}" class="btn btn-primary">الفئات</a>
        </div>
    </div>

    <section class="mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @foreach($stats as $key => $value)
                <div class="card-soft p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <div class="text-sm text-slate-600 dark:text-slate-400">{{ __(ucfirst($key)) }}</div>
                    <div class="text-2xl font-extrabold text-slate-900 dark:text-slate-50">{{ $value }}</div>
                    <div class="h-1.5 rounded-full mt-2" style="background: var(--brand-soft);">
                        <div class="h-full rounded-full" style="width: 65%; background: {{ $palette[$key] ?? 'var(--brand-start)' }};"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mb-4">
        <div class="grid grid-cols-1 lg:grid-cols-1 gap-3">
            <div class="card-soft p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between mb-2">
                    <h5 class="mb-0 text-slate-900 dark:text-slate-100">أحدث المستخدمين</h5>
                    <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                </div>
                <div class="space-y-2">
                    @forelse($latestUsers as $u)
                        <div class="list-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                            <div class="fw-bold text-slate-900 dark:text-slate-100">{{ $u->name }}</div>
                            <div class="text-muted small">{{ $u->email }}</div>
                        </div>
                    @empty
                        <div class="text-muted">لا يوجد مستخدمون.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection
