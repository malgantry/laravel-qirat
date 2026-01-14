<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Services\Insights\TransactionInsightsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(TransactionInsightsService $insights): View
    {
        $q = request('q');
        $typeFilter = request('type');
        $transactions = Transaction::query()
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

        $feedback = [];
        foreach ($transactions as $tx) {
            $feedback[$tx->id] = $insights->generateFor($tx);
        }

        return view('transactions.index', compact('transactions', 'feedback'));
    }

    public function create(): View
    {
        $this->ensureStarterCategories();
        $categories = Category::orderBy('name')->get();
        $icons = $this->iconChoices();
        return view('transactions.create', compact('categories', 'icons'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        Transaction::create($data);

        return redirect()->route('transactions.index')->with('status', 'تم حفظ المعاملة بنجاح');
    }

    public function edit(Transaction $transaction): View
    {
        $this->ensureStarterCategories();
        $categories = Category::orderBy('name')->get();
        $icons = $this->iconChoices();
        return view('transactions.edit', compact('transaction', 'categories', 'icons'));
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $data = $this->validateData($request);
        $transaction->update($data);

        return redirect()->route('transactions.index')->with('status', 'تم تحديث المعاملة بنجاح');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $transaction->delete();

        return redirect()->route('transactions.index')->with('status', 'تم حذف المعاملة');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', 'in:income,expense'],
            'category_id' => ['nullable', 'exists:categories,id', 'required_without:category'],
            'category' => ['nullable', 'string', 'max:120', 'required_without:category_id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'occurred_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        // If category_id provided, prefer it and sync the category name
        if (!empty($data['category_id'])) {
            $cat = Category::find($data['category_id']);
            if ($cat) {
                $data['category'] = $cat->name;
            }
        }

        return $data;
    }

    private function iconChoices(): array
    {
        // Common Bootstrap Icons useful for finance categories
        return [
            'bi-egg-fried','bi-cart2','bi-receipt','bi-mic','bi-phone','bi-activity','bi-person-hearts','bi-journal-text','bi-people',
            'bi-cash-coin','bi-gift','bi-graph-up-arrow','bi-arrow-left-right','bi-bag','bi-fuel-pump','bi-bus-front','bi-house','bi-lightning',
            'bi-credit-card','bi-heart','bi-basket','bi-bicycle','bi-geo-alt','bi-music-note','bi-cup-hot','bi-mortarboard','bi-tools'
        ];
    }

    private function ensureStarterCategories(): void
    {
        // If there are already categories, skip
        if (Category::query()->exists()) return;

        $userId = optional(request()->user())->id;
        if (!$userId) {
            $userId = \App\Models\User::query()->value('id');
        }
        if (!$userId) return; // cannot create without a user due to FK

        $starters = $this->defaultCategories();
        foreach ($starters as $c) {
            Category::firstOrCreate([
                'user_id' => $userId,
                'name' => $c['name'],
                'type' => $c['type'],
            ], [
                'icon' => $c['icon'] ?? null,
            ]);
        }
    }

    private function defaultCategories(): array
    {
        return [
            ['name' => 'طعام', 'type' => 'expense', 'icon' => 'bi-egg-fried'],
            ['name' => 'تسوق', 'type' => 'expense', 'icon' => 'bi-cart2'],
            ['name' => 'فواتير', 'type' => 'expense', 'icon' => 'bi-receipt'],
            ['name' => 'ترفيه', 'type' => 'expense', 'icon' => 'bi-mic'],
            ['name' => 'هاتف', 'type' => 'expense', 'icon' => 'bi-phone'],
            ['name' => 'رياضة', 'type' => 'expense', 'icon' => 'bi-activity'],
            ['name' => 'تجميل', 'type' => 'expense', 'icon' => 'bi-person-hearts'],
            ['name' => 'تعليم', 'type' => 'expense', 'icon' => 'bi-journal-text'],
            ['name' => 'اجتماعي', 'type' => 'expense', 'icon' => 'bi-people'],
            ['name' => 'راتب', 'type' => 'income', 'icon' => 'bi-cash-coin'],
            ['name' => 'مكافأة', 'type' => 'income', 'icon' => 'bi-gift'],
            ['name' => 'استثمار', 'type' => 'income', 'icon' => 'bi-graph-up-arrow'],
            ['name' => 'تحويل', 'type' => 'income', 'icon' => 'bi-arrow-left-right'],
        ];
    }
}
