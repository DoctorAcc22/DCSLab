<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DashboardTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function test_dashboard_is_showing()
    {
        $loggedInUser = $this->developer;

        $this->browse(function (Browser $browser) use($loggedInUser) {
            $browser->loginAs($loggedInUser)
                    ->visit('/dashboard')
                    ->waitForLocation('/dashboard')
                    ->assertSee('Main Dashboard');
        });
    }
}
