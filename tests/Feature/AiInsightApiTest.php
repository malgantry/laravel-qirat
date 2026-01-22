<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Goal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiInsightApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_dashboard_ai_insights_endpoint()
    {
        $response = $this->actingAs($this->user)->getJson(route('ai.insights.dashboard'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'txFeedback',
                'goalFeedback'
            ]);
    }

    public function test_reports_ai_insights_endpoint()
    {
        $response = $this->actingAs($this->user)->getJson(route('ai.insights.reports', [
            'start' => now()->startOfMonth()->toDateString(),
            'end' => now()->endOfMonth()->toDateString()
        ]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'aiInsights'
            ]);
    }

    public function test_transactions_batch_ai_insights_endpoint()
    {
        $tx = Transaction::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->actingAs($this->user)->getJson(route('ai.insights.transactions', [
            'ids' => [$tx->id]
        ]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'feedback'
            ]);
    }

    public function test_goals_ai_insights_endpoint()
    {
        Goal::factory()->create([
            'user_id' => $this->user->id,
            'target_amount' => 1000,
            'current_amount' => 500,
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->user)->getJson(route('ai.insights.goals'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'feedback'
            ]);
    }

    public function test_endpoints_require_authentication()
    {
        $this->getJson(route('ai.insights.dashboard'))->assertStatus(401);
    }
}
