<?php

namespace Tests\Unit;

use App\Models\Goal;
use App\Services\AiClient;
use App\Services\FeatureBuilder;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiClientTest extends TestCase
{
    public function test_analyze_goal_returns_feedbacks_on_success()
    {
        Http::fake([
            '*/analyze/goal' => Http::response([
                'feedbacks' => [
                    [
                        'id' => '123',
                        'type' => 'warning',
                        'message' => 'Test warning',
                        'priority' => 1
                    ]
                ]
            ], 200)
        ]);

        $builder = new FeatureBuilder();
        $client = new AiClient($builder);
        
        $goal = new Goal([
            'id' => 1,
            'name' => 'Test Goal',
            'target_amount' => 1000,
            'current_amount' => 500,
            'user_id' => 1
        ]);

        $result = $client->analyzeGoal($goal, '1');

        $this->assertCount(1, $result);
        $this->assertEquals('Test warning', $result[0]['message']);
    }

    public function test_analyze_goal_returns_empty_array_on_failure()
    {
        Http::fake([
            '*/analyze/goal' => Http::response([], 500)
        ]);

        $builder = new FeatureBuilder();
        $client = new AiClient($builder);
        
        $goal = new Goal([
            'id' => 1,
            'name' => 'Test Goal',
            'target_amount' => 1000,
            'current_amount' => 500,
            'user_id' => 1
        ]);

        $result = $client->analyzeGoal($goal, '1');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
