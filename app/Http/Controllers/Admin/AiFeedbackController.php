<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiFeedback;
use Illuminate\Http\Request;

class AiFeedbackController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','can:admin']);
    }

    public function index(Request $request)
    {
        $q = $request->query('q');
        $items = AiFeedback::query()
            ->when($q, fn($qr) => $qr->where('feedback_id', 'like', "%$q%"))
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('admin.ai_feedbacks.index', compact('items'));
    }

    public function destroy($id)
    {
        $it = AiFeedback::findOrFail($id);
        $it->delete();
        // If request expects JSON (AJAX), return JSON response for smoother UI
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['status' => 'success']);
        }

        return redirect()->route('admin.ai.feedbacks')->with('status', 'تم حذف السجل');
    }
}
