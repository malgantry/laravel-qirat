<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Services\Insights\GoalInsightsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $userId = request()->user()->id;
        $goals = Goal::where('user_id', $userId)->orderBy('deadline')->orderBy('id')->paginate(12);

        return view('goals.index', compact('goals'));
    }

    public function create(): View
    {
        return view('goals.create');
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $data = $this->validateData($request);
            $data['user_id'] = $request->user()->id;
            Goal::create($data);

            return redirect()->route('goals.index')->with('status', 'تم حفظ الهدف بنجاح');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Goal Store Error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'حدث خطأ أثناء حفظ الهدف. يرجى المحاولة لاحقاً.');
        }
    }

    public function edit(Goal $goal): View
    {
        $this->authorizeGoal($goal);
        return view('goals.edit', compact('goal'));
    }

    public function update(Request $request, Goal $goal): RedirectResponse
    {
        try {
            $this->authorizeGoal($goal);
            $data = $this->validateData($request);
            $data['user_id'] = $request->user()->id;
            $goal->update($data);

            return redirect()->route('goals.index')->with('status', 'تم تحديث الهدف بنجاح');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Goal Update Error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'حدث خطأ أثناء تحديث الهدف.');
        }
    }

    public function destroy(Goal $goal): RedirectResponse
    {
        try {
            $this->authorizeGoal($goal);
            $goal->delete();

            return redirect()->route('goals.index')->with('status', 'تم حذف الهدف');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Goal Delete Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف الهدف.');
        }
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'min:3'],
            'target_amount' => ['required', 'numeric', 'min:1', 'max:99999999'],
            'current_amount' => [
                'required', 
                'numeric', 
                'min:0', 
                'max:99999999',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value > $request->target_amount) {
                        $fail(__('المبلغ الحالي لا يمكن أن يكون أكبر من المبلغ المستهدف.'));
                    }
                }
            ],
            'deadline' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        // Automatic Status Determination
        $data['status'] = $data['current_amount'] >= $data['target_amount'] ? 'completed' : 'in_progress';
        
        return $data;
    }

    private function authorizeGoal(Goal $goal): void
    {
        if ($goal->user_id !== request()->user()->id) {
            abort(404);
        }
    }
}
