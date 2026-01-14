<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AuthFlowTest extends DuskTestCase
{
    /** @test */
    public function login_page_loads_and_has_form(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertPresent('form');
        });
    }

    /** @test */
    public function register_page_loads_and_has_form(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->assertPresent('form');
        });
    }
}
