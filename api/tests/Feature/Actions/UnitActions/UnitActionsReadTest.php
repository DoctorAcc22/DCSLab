<?php

namespace Tests\Feature;

use App\Actions\Unit\UnitActions;
use App\Models\Company;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UnitActionsReadTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->unitActions = new UnitActions();
    }

    public function test_unit_actions_call_read_expect_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Unit::factory()->count(5))
                    )->create();

        $unit = $user->companies()->inRandomOrder()->first()
                ->units()->inRandomOrder()->first();

        $result = $this->unitActions->read($unit);

        $this->assertInstanceOf(Unit::class, $result);
    }
}