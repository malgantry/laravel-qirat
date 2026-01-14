@extends('layouts.app')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-xl font-bold text-slate-900 dark:text-slate-50">المستخدمون</h3>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">لوحة الإدارة</a>
    </div>

    <div class="table-modern">
        <table class="table">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>البريد</th>
                    <th>الدور</th>
                    <th>الحالة</th>
                    <th>أنشئ في</th>
                    <th class="text-center">إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>
                            @if($u->is_admin)
                                <span class="badge bg-dark">مدير النظام</span>
                            @else
                                <span class="badge bg-primary">مستخدم مالي</span>
                            @endif
                        </td>
                        <td>
                            @if($u->is_active)
                                <span class="badge bg-success">نشط</span>
                            @else
                                <span class="badge bg-secondary">معطل</span>
                            @endif
                        </td>
                        <td>{{ optional($u->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="text-center">
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                <form method="POST" action="{{ route('admin.users.toggleActive', $u) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $u->is_active ? 'btn-outline-secondary' : 'btn-success' }}">
                                        {{ $u->is_active ? 'تعطيل' : 'تفعيل' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.toggleAdmin', $u) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $u->is_admin ? 'btn-outline-danger' : 'btn-outline-dark' }}">
                                        {{ $u->is_admin ? 'إزالة مدير' : 'منح مدير' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.reset', $u) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">إرسال إعادة تعيين</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $users->links() }}</div>
@endsection
