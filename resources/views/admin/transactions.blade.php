@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto animate-enter">
        <div class="card-premium p-6 text-center">
            <h3 class="font-heading font-bold text-text-main mb-2" data-i18n="manageTransactionsTitle">إدارة المعاملات</h3>
            <p class="text-text-muted" data-i18n="pageDisabledInfo">تم إيقاف هذه الصفحة في لوحة الإدارة حالياً.</p>
             <div class="mt-4">
                <a href="{{ route('admin.dashboard') }}" class="btn-soft px-4 py-2 text-sm" data-i18n="backToAdminHome">عودة للرئيسية</a>
            </div>
        </div>
    </div>
@endsection
