<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index()
    {
        $budgets = Budget::with('category')->orderByDesc('period_start')->paginate(12);
        return view('budgets.index', compact('budgets'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('budgets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'limit_amount' => ['required', 'numeric', 'min:0'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);
        $validated['spent_amount'] = $validated['spent_amount'] ?? 0;
        Budget::create($validated);
        return redirect()->route('budgets.index')->with('status', 'تم إنشاء الميزانية بنجاح');
    }

    public function edit(Budget $budget)
    {
        $categories = Category::orderBy('name')->get();
        return view('budgets.edit', compact('budget', 'categories'));
    }

    public function update(Request $request, Budget $budget)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'limit_amount' => ['required', 'numeric', 'min:0'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);
        $budget->update($validated);
        return redirect()->route('budgets.index')->with('status', 'تم تحديث الميزانية بنجاح');
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();
        return redirect()->route('budgets.index')->with('status', 'تم حذف الميزانية');
    }
}
