<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_budget()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::create(["user_id" => $user->id, 'name' => 'اختبار', 'type' => 'expense']);

        $data = [
            'category_id' => $category->id,
            'limit_amount' => 150.50,
            'period_start' => now()->toDateString(),
            'period_end' => now()->addMonth()->toDateString(),
            'status' => 'نشطة',
        ];

        $resp = $this->post(route('budgets.store'), $data);

        $resp->assertRedirect(route('budgets.index'));
        $this->assertDatabaseHas('budgets', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'limit_amount' => 150.50,
        ]);
    }
}
