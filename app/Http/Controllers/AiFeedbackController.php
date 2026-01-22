<?php

namespace App\Http\Controllers;

use App\Services\AiClient;
use App\Models\AiFeedback as AiFeedbackModel;
use App\Models\Transaction;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use App\Services\Insights\TransactionInsightsService;
use App\Services\Insights\GoalInsightsService;

class AiFeedbackController extends Controller
{
    public function store(Request $request, AiClient $ai): JsonResponse
    {
        $data = $request->validate([
            'feedback_id' => ['required', 'string', 'max:120'],
            'action' => ['required', 'in:accepted,dismissed,ignored'],
            'object_type' => ['nullable', 'in:transaction,goal'],
            'object_id' => ['nullable', 'numeric'],
            'feedback_type' => ['nullable', 'string', 'max:100'],
        ]);

        $userId = (string) optional($request->user())->id;
        if ($userId === '') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        // Forward to AI service
        $result = $ai->submitFeedback($userId, $data['feedback_id'], $data['action'], $data['feedback_type'] ?? null);

        // Persist locally
        $fb = AiFeedbackModel::create([
            'feedback_id' => $data['feedback_id'],
            'user_id' => (int) $request->user()->id,
            'action' => $data['action'],
            'object_type' => $data['object_type'] ?? null,
            'object_id' => isset($data['object_id']) ? (int) $data['object_id'] : null,
            'meta' => $result,
        ]);

        // Invalidate related cache so insights regenerate
        try {
            if (!empty($fb->object_type) && !empty($fb->object_id)) {
                if ($fb->object_type === 'transaction') {
                    $tx = Transaction::find($fb->object_id);
                    if ($tx) {
                        $uid = (string) ($request->user()->id ?? 'default');
                        $isIncome = $tx->type === 'income' ? '1' : '0';
                        $finger = implode(':', [(string)$tx->id, $uid, $isIncome, (string)$tx->amount]);
                        Cache::forget('insights:tx:' . md5($finger));
                    }
                }
                if ($fb->object_type === 'goal') {
                    $g = Goal::find($fb->object_id);
                    if ($g) {
                        $uid = (string) ($request->user()->id ?? 'default');
                        $finger = implode(':', [
                            (string) $g->id,
                            $uid,
                            (string) $g->target_amount,
                            (string) $g->current_amount,
                            (string) optional($g->deadline)->toDateString(),
                        ]);
                        Cache::forget('insights:goal:' . md5($finger));
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return response()->json(array_merge(['status' => 'success'], is_array($result) ? $result : ['result' => $result]));
    }

    /**
     * Re-run insights for a specific object and return the top feedback item (if any).
     */
    public function refresh(Request $request, TransactionInsightsService $txInsights, GoalInsightsService $goalInsights): JsonResponse
    {
        $data = $request->validate([
            'object_type' => ['required', 'in:transaction,goal'],
            'object_id' => ['required', 'numeric'],
        ]);

        try {
            if ($data['object_type'] === 'transaction') {
                $tx = Transaction::find($data['object_id']);
                if (! $tx) return response()->json(['status' => 'error', 'message' => 'Not found'], 404);
                $fbItems = $txInsights->generateFor($tx);
                $html = view('partials.ai_feedback', ['feedback' => $fbItems[0] ?? null, 'objectType' => 'transaction', 'objectId' => $tx->id])->render();
                return response()->json(['status' => 'success', 'feedback' => $fbItems, 'html' => $html]);
            }
            if ($data['object_type'] === 'goal') {
                $g = Goal::find($data['object_id']);
                if (! $g) return response()->json(['status' => 'error', 'message' => 'Not found'], 404);
                $fbItems = $goalInsights->generateFor($g);
                $html = view('partials.ai_feedback', ['feedback' => $fbItems[0] ?? null, 'objectType' => 'goal', 'objectId' => $g->id])->render();
                return response()->json(['status' => 'success', 'feedback' => $fbItems, 'html' => $html]);
            }
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        return response()->json(['status' => 'success', 'feedback' => null]);
    }
}
