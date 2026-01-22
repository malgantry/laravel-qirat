<?php

namespace Tests\Feature;

use App\Models\Goal;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrudFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_goal_crud()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        // Create
        $response = $this->post(route('goals.store'), [
            'name' => 'اختبار هدف',
            'target_amount' => 1000,
            'current_amount' => 100,
            'deadline' => now()->addDays(30)->toDateString(),
        ]);
        $response->assertRedirect(route('goals.index'));
        $this->assertDatabaseHas('goals', ['name' => 'اختبار هدف', 'user_id' => $user->id]);

        $goal = Goal::where('user_id', $user->id)->where('name', 'اختبار هدف')->first();
        $this->assertNotNull($goal);

        // Update
        $response = $this->put(route('goals.update', $goal), [
            'name' => 'هدف محدث',
            'target_amount' => 2000,
            'current_amount' => 150,
        ]);
        $response->assertRedirect(route('goals.index'));
        $this->assertDatabaseHas('goals', ['id' => $goal->id, 'name' => 'هدف محدث']);

        // Delete
        $response = $this->delete(route('goals.destroy', $goal));
        $response->assertRedirect(route('goals.index'));
        $this->assertDatabaseMissing('goals', ['id' => $goal->id]);
    }

    public function test_transaction_crud()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        // Create transaction
        $response = $this->post(route('transactions.store'), [
            'type' => 'expense',
            'category' => 'اختبار',
            'amount' => 50.25,
            'occurred_at' => now()->toDateString(),
            'note' => 'اختبار حفظ',
        ]);
        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', ['note' => 'اختبار حفظ', 'user_id' => $user->id]);

        $tx = Transaction::where('user_id', $user->id)->where('note', 'اختبار حفظ')->first();
        $this->assertNotNull($tx);

        // Update
        $response = $this->put(route('transactions.update', $tx), [
            'type' => 'expense',
            'category' => 'اختبار-تحديث',
            'amount' => 75.5,
            'occurred_at' => now()->toDateString(),
            'note' => 'معدل تحديث',
        ]);
        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', ['id' => $tx->id, 'note' => 'معدل تحديث']);

        // Delete
        $response = $this->delete(route('transactions.destroy', $tx));
        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseMissing('transactions', ['id' => $tx->id]);
    }
}
