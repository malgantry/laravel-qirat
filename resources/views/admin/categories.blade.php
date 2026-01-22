@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto space-y-8 animate-enter">
        <!-- Categories Management Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 text-[var(--gold-500)] font-black text-[10px] uppercase tracking-[0.2em] mb-2" data-i18n="categoryEngine">
                    <span class="w-8 h-px bg-[var(--gold-400)]"></span>
                    <span data-i18n="categoryEngine">محرك التصنيف والتبويب</span>
                </div>
                <h3 class="text-4xl font-heading font-black text-text-main tracking-tight" data-i18n="manageCategoriesTitle">إدارة الفئات</h3>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.dashboard') }}" class="btn-soft px-8 py-3 text-sm font-bold shadow-xl border border-slate-200/50 dark:border-slate-800/50">
                    <i class="bi bi-grid-fill me-2"></i> <span data-i18n="adminDashboard">لوحة الإدارة</span>
                </a>
            </div>
        </div>

        <!-- Categories Table Container -->
        <div class="card-premium overflow-hidden border-none shadow-2xl relative">
            <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row justify-between items-center bg-white/50 dark:bg-slate-900/50 backdrop-blur-xl gap-4">
                <div>
                    <h5 class="text-xl font-heading font-black text-text-main mb-1" data-i18n="categoryStructureHeader">هيكلة التصنيفات المالية</h5>
                    <p class="text-xs text-slate-500 font-medium" data-i18n="organizeCategoriesInfo">عرض وتنظيم الفئات المستخدمة في تبويب الدخل والمصروفات.</p>
                </div>
                <div class="flex flex-col md:flex-row items-center gap-4">
                    <form action="{{ route('admin.categories') }}" method="GET" class="relative group w-full md:w-64">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="input-premium py-2 pl-10 text-sm focus:ring-2 focus:ring-[var(--gold-400)]/20" 
                               placeholder="بحث عن فئة..." data-i18n-placeholder="searchTransactionsPlaceholder">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[var(--gold-500)] transition-colors"></i>
                    </form>
                    <div class="w-10 h-10 rounded-2xl bg-[var(--gold-50)] dark:bg-[var(--gold-900)]/20 text-[var(--gold-500)] flex items-center justify-center text-lg shadow-inner">
                        <i class="bi bi-tag-fill"></i>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table-premium">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-950/20">
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800" data-i18n="identificationTitle">العنوان التعريفي</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800" data-i18n="accountingType">النوع المحاسبي</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 text-center" data-i18n="visualIcon">الرمز البصري</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 dark:border-slate-800 text-center" data-i18n="sovereignOps">العمليات السيادية</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $c)
                            <tr class="group hover:bg-[var(--gold-50)]/30 dark:hover:bg-[var(--gold-900)]/5 transition-colors border-b border-slate-100 dark:border-slate-800 last:border-0">
                                <td class="px-8 py-6">
                                    <span class="font-heading font-black text-slate-800 dark:text-slate-200 text-base tracking-tight">{{ $c->name }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    @if($c->type === 'income')
                                        <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-black uppercase tracking-widest border border-emerald-100 dark:border-emerald-900/30">
                                            <i class="bi bi-arrow-up-right me-2"></i> <span data-i18n="financialIncome">دخل مالي</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 text-[10px] font-black uppercase tracking-widest border border-rose-100 dark:border-rose-900/30">
                                            <i class="bi bi-arrow-down-left me-2"></i> <span data-i18n="currentExpense">مصروف جاري</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <div class="w-10 h-10 rounded-2xl bg-slate-50 dark:bg-slate-900 text-slate-400 flex items-center justify-center text-lg mx-auto shadow-inner border border-slate-100 dark:border-slate-800 group-hover:text-[var(--gold-500)] group-hover:border-[var(--gold-400)]/30 transition-all">
                                        <i class="bi {{ $c->icon }}"></i>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm(i18n[currentLang()].deleteConfirm);">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-soft px-4 py-2 text-[10px] font-black uppercase tracking-widest text-rose-600">
                                                <i class="bi bi-trash me-1"></i> <span data-i18n="delete">حذف</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Container -->
            <div class="p-8 border-t border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-950/10">
                <div class="flex justify-center">
                    {{ $categories->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
