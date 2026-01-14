<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Services\Insights\GoalInsightsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoalController extends Controller
{
    public function index(GoalInsightsService $insights): View
    {
        $goals = Goal::orderBy('deadline')->orderBy('id')->paginate(12);
        $feedback = [];
        foreach ($goals as $g) {
            $feedback[$g->id] = $insights->generateFor($g);
        }

        return view('goals.index', compact('goals', 'feedback'));
    }

    public function create(): View
    {
        return view('goals.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['status'] = $data['status'] ?? 'in_progress'; // ensure NOT NULL status aligns with migration default
        Goal::create($data);

        return redirect()->route('goals.index')->with('status', 'تم حفظ الهدف بنجاح');
    }

    public function edit(Goal $goal): View
    {
        return view('goals.edit', compact('goal'));
    }

    public function update(Request $request, Goal $goal): RedirectResponse
    {
        $data = $this->validateData($request);
        $data['status'] = $data['status'] ?? $goal->status ?? 'in_progress';
        $goal->update($data);

        return redirect()->route('goals.index')->with('status', 'تم تحديث الهدف بنجاح');
    }

    public function destroy(Goal $goal): RedirectResponse
    {
        $goal->delete();

        return redirect()->route('goals.index')->with('status', 'تم حذف الهدف');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'target_amount' => ['required', 'numeric', 'min:0.01'],
            'current_amount' => ['required', 'numeric', 'min:0'],
            'deadline' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);
    }
}
