<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Goal;
use App\Models\Transaction;
use App\Http\Requests\StoreTransactionRequest;
use App\Services\Insights\TransactionInsightsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(TransactionInsightsService $insights): View
    {
        $userId = auth()->id();
        $q = request('q');
        $typeFilter = request('type');

        $transactions = Transaction::query()
            ->where('user_id', $userId)
            ->when(in_array($typeFilter, ['income','expense'], true), fn($qr) => $qr->where('type', $typeFilter))
            ->when($q, function ($qr) use ($q) {
                $qr->where(function ($sub) use ($q) {
                    $sub->where('note', 'like', "%$q%")
                        ->orWhere('category', 'like', "%$q%");
                });
            })
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->appends(['q' => $q, 'type' => $typeFilter]);

        return view('transactions.index', compact('transactions'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $this->ensureStarterCategories($user->id);
        
        $categories = Category::where('user_id', $user->id)->orderBy('name')->get();
        [$expenseTiles, $incomeTiles] = $this->splitCategoryTiles($categories);
        $goals = Goal::where('user_id', $user->id)->where('status', 'in_progress')->orderBy('name')->get();
        $icons = $this->iconChoices();

        return view('transactions.create', compact('categories', 'icons', 'goals', 'expenseTiles', 'incomeTiles'));
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        
        $savingsAmount = $data['savings_amount'] ?? null;
        unset($data['savings_amount']);

        $data['is_fixed'] = $data['type'] === 'expense' ? (bool) ($request->is_fixed ?? false) : false;

        // Logic for Income Savings Allocation
        $goalId = $data['goal_id'] ?? null;
        if ($data['type'] === 'income' && $goalId) {
             $data['goal_id'] = null; 
             $data['note'] = ($data['note'] ?? '') . ' (Allocated to goal)';
        }

        $transaction = Transaction::create($data);

        if ($data['type'] === 'income' && $goalId) {
            $this->handleGoalAllocation($request->user(), $goalId, $savingsAmount ?? $transaction->amount, $data['occurred_at']);
        }

        return redirect()->route('transactions.index')->with('status', 'تم حفظ المعاملة بنجاح');
    }

    protected function handleGoalAllocation($user, $goalId, $amount, $date)
    {
        $goal = Goal::find($goalId);
        if ($goal) {
            $goal->increment('current_amount', $amount);
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'expense',
                'amount' => $amount,
                'category' => 'Savings',
                'occurred_at' => $date,
                'note' => __('Allocated to goal') . ': ' . $goal->name,
                'goal_id' => $goal->id,
            ]);
        }
    }

    public function edit(Transaction $transaction): View
    {
        $this->authorizeTransaction($transaction);
        $user = auth()->user();

        $categories = Category::where('user_id', $user->id)->orderBy('name')->get();
        [$expenseTiles, $incomeTiles] = $this->splitCategoryTiles($categories);
        $goals = Goal::where('user_id', $user->id)
            ->when(!$transaction->goal_id, function($q){ $q->where('status', 'in_progress'); })
            ->orderBy('name')
            ->get();
            
        $icons = $this->iconChoices();
        return view('transactions.edit', compact('transaction', 'categories', 'icons', 'goals', 'expenseTiles', 'incomeTiles'));
    }

    public function update(StoreTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($transaction);
        
        $oldAmount = $transaction->amount;
        $oldGoalId = $transaction->goal_id;
        $oldType = $transaction->type;

        $data = $request->validated();
        unset($data['savings_amount']);
        $data['is_fixed'] = $data['type'] === 'expense' ? (bool) ($request->is_fixed ?? false) : false;

        if ($data['type'] === 'income') {
            unset($data['goal_id']);
        }
        
        $transaction->update($data);

        // Manage Goal Balance Shifts
        if ($oldType === 'expense' && $oldGoalId) {
            optional(Goal::find($oldGoalId))->decrement('current_amount', $oldAmount);
        }

        if ($transaction->type === 'expense' && $transaction->goal_id) {
             optional(Goal::find($transaction->goal_id))->increment('current_amount', $transaction->amount);
        }

        return redirect()->route('transactions.index')->with('status', 'تم تحديث المعاملة بنجاح');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransaction($transaction);
        
        if ($transaction->type === 'expense' && $transaction->goal_id) {
            $goal = Goal::find($transaction->goal_id);
            if ($goal) {
                $goal->update(['current_amount' => max(0, $goal->current_amount - $transaction->amount)]);
            }
        }

        $transaction->delete();
        return redirect()->route('transactions.index')->with('status', 'تم حذف المعاملة');
    }

    private function authorizeTransaction(Transaction $transaction): void
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(404);
        }
    }

    private function iconChoices(): array
    {
        return [
            'bi-egg-fried','bi-cart2','bi-receipt','bi-mic','bi-phone','bi-activity','bi-person-hearts','bi-journal-text','bi-people',
            'bi-cash-coin','bi-gift','bi-graph-up-arrow','bi-arrow-left-right','bi-bag','bi-fuel-pump','bi-bus-front','bi-house','bi-lightning',
            'bi-credit-card','bi-heart','bi-basket','bi-bicycle','bi-geo-alt','bi-music-note','bi-cup-hot','bi-mortarboard','bi-tools'
        ];
    }

    private function ensureStarterCategories(int $userId): void
    {
        if (Category::where('user_id', $userId)->exists()) return;

        $starters = [
            ['name' => 'طعام', 'type' => 'expense', 'icon' => 'bi-egg-fried', 'color' => '#fb923c'],
            ['name' => 'تسوق', 'type' => 'expense', 'icon' => 'bi-cart2', 'color' => '#a855f7'],
            ['name' => 'فواتير', 'type' => 'expense', 'icon' => 'bi-receipt', 'color' => '#ef4444'],
            ['name' => 'ترفيه', 'type' => 'expense', 'icon' => 'bi-mic', 'color' => '#f472b6'],
            ['name' => 'هاتف', 'type' => 'expense', 'icon' => 'bi-phone', 'color' => '#38bdf8'],
            ['name' => 'رياضة', 'type' => 'expense', 'icon' => 'bi-activity', 'color' => '#4ade80'],
            ['name' => 'تجميل', 'type' => 'expense', 'icon' => 'bi-person-hearts', 'color' => '#f472b6'],
            ['name' => 'تعليم', 'type' => 'expense', 'icon' => 'bi-journal-text', 'color' => '#6366f1'],
            ['name' => 'اجتماعي', 'type' => 'expense', 'icon' => 'bi-people', 'color' => '#f59e0b'],
            ['name' => 'راتب', 'type' => 'income', 'icon' => 'bi-cash-coin', 'color' => '#10b981'],
            ['name' => 'مكافأة', 'type' => 'income', 'icon' => 'bi-gift', 'color' => '#34d399'],
            ['name' => 'استثمار', 'type' => 'income', 'icon' => 'bi-graph-up-arrow', 'color' => '#059669'],
            ['name' => 'تحويل', 'type' => 'income', 'icon' => 'bi-arrow-left-right', 'color' => '#6366f1'],
        ];

        foreach ($starters as $c) {
            Category::firstOrCreate(['user_id' => $userId, 'name' => $c['name'], 'type' => $c['type']], ['icon' => $c['icon'] ?? null, 'color' => $c['color'] ?? null]);
        }
    }

    private function splitCategoryTiles($categories): array
    {
        $expense = [];
        $income = [];

        foreach ($categories as $c) {
            $type = strtolower(trim($c->type ?? 'expense'));
            $target = $type === 'income' ? 'income' : 'expense';
            ${$target}[] = [
                'id' => $c->id,
                'name' => $c->name,
                'icon' => $c->icon,
                'color' => $c->color,
            ];
        }

        return [$expense, $income];
    }
}
