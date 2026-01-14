<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TransactionsCrudTest extends DuskTestCase
{
    /** @test */
    public function transactions_index_loads_for_authenticated_user(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/transactions')
                ->assertPresent('body');
        });
    }
}
