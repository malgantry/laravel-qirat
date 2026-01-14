@extends('layouts.app')

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-xl font-bold text-slate-900 dark:text-slate-50">الفئات</h3>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary">لوحة الإدارة</a>
    </div>

    <div class="table-modern">
        <table class="table">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>النوع</th>
                    <th>الأيقونة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $c)
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->type === 'income' ? 'دخل' : 'مصروف' }}</td>
                        <td><i class="bi {{ $c->icon }}"></i></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $categories->links() }}</div>
@endsection
