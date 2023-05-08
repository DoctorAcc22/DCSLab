<?php

namespace Tests\Feature;

use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\Profile;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SupplierAPIDeleteTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_supplier_api_call_delete_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                        ->has(Supplier::factory()
                            ->for(User::factory()
                                ->has(Profile::factory())
                                ->hasAttached(Role::where('name', '=', UserRoles::USER->value)->first())
                                ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
                                ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
                                ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
                            )))->create();

        $this->actingAs($user);

        $supplier = $user->companies()->inRandomOrder()->first()
                    ->suppliers()->inRandomOrder()->first();

        $api = $this->json('POST', route('api.post.db.supplier.supplier.delete', $supplier->ulid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('suppliers', [
            'id' => $supplier->id,
        ]);
    }

    public function test_supplier_api_call_delete_of_nonexistance_ulid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $ulid = $this->faker->uuid();

        $api = $this->json('POST', route('api.post.db.supplier.supplier.delete', $ulid));

        $api->assertStatus(404);
    }

    public function test_supplier_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $api = $this->json('POST', route('api.post.db.supplier.supplier.delete', null));
    }
}