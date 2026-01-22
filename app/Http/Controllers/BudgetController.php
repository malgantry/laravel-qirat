<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BudgetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $budgets = Budget::with('category')
            ->where('user_id', request()->user()->id)
            ->orderByDesc('period_start')
            ->paginate(12);
        return view('budgets.index', compact('budgets'));
    }

    public function create()
    {
        $categories = Category::where('user_id', request()->user()->id)->orderBy('name')->get();
        return view('budgets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('user_id', $request->user()->id),
            ],
            'limit_amount' => ['required', 'numeric', 'min:1', 'max:99999999'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);
        $validated['user_id'] = $request->user()->id;
        // Ensure nullable fields are set to non-null defaults to satisfy DB NOT NULL constraints
        $validated['status'] = $validated['status'] ?? '';
        $validated['spent_amount'] = $validated['spent_amount'] ?? 0;
        Budget::create($validated);
        return redirect()->route('budgets.index')->with('status', 'تم إنشاء الميزانية بنجاح');
    }

    public function edit(Budget $budget)
    {
        $this->authorizeBudget($budget);
        $categories = Category::where('user_id', request()->user()->id)->orderBy('name')->get();
        return view('budgets.edit', compact('budget', 'categories'));
    }

    public function update(Request $request, Budget $budget)
    {
        $this->authorizeBudget($budget);
        $validated = $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('user_id', $request->user()->id),
            ],
            'limit_amount' => ['required', 'numeric', 'min:1', 'max:99999999'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);
        // Ensure status is not null to avoid DB constraint errors
        $validated['status'] = $validated['status'] ?? '';
        $budget->update($validated);
        return redirect()->route('budgets.index')->with('status', 'تم تحديث الميزانية بنجاح');
    }

    public function destroy(Budget $budget)
    {
        $this->authorizeBudget($budget);
        $budget->delete();
        return redirect()->route('budgets.index')->with('status', 'تم حذف الميزانية');
    }

    private function authorizeBudget(Budget $budget): void
    {
        if ($budget->user_id !== request()->user()->id) {
            abort(404);
        }
    }
}
