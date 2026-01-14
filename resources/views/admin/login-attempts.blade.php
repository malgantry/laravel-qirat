@extends('layouts.app')

@php use Illuminate\Support\Str; @endphp

@section('content')
<div class="mb-4 flex items-center justify-between">
    <h3 class="text-xl font-bold text-slate-900 dark:text-slate-50">سجلات الدخول</h3>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">لوحة الإدارة</a>
</div>

<div class="card shadow-sm" style="background: var(--card-bg); color: var(--text-primary); border-color: var(--border-color)">
    <div class="card-body p-0">
        <div class="table-modern">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>المستخدم</th>
                        <th>البريد</th>
                        <th>النتيجة</th>
                        <th>IP</th>
                        <th>المتصفح</th>
                        <th>الوقت</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attempts as $a)
                        <tr>
                            <td>{{ optional($a->user)->name ?? 'غير معروف' }}</td>
                            <td>{{ $a->email }}</td>
                            <td>
                                @if($a->success)
                                    <span class="badge bg-success">ناجحة</span>
                                @else
                                    <span class="badge bg-danger">فاشلة</span>
                                @endif
                            </td>
                            <td>{{ $a->ip_address }}</td>
                            <td class="text-truncate" style="max-width: 260px;" title="{{ $a->user_agent }}">{{ Str::limit($a->user_agent, 50) }}</td>
                            <td>{{ optional($a->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-3">{{ $attempts->links() }}</div>
@endsection
