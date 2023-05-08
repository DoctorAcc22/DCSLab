<?php

namespace Tests\Feature;

use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\ProductGroup;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\APITestCase;

class ProductGroupAPIReadTest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_product_group_api_call_read_expect_successful()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(ProductGroup::factory())
                    )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $ulid = $company->productGroups()->inRandomOrder()->first()->ulid;

        $api = $this->getJson(route('api.get.db.product.product_group.read', $ulid));

        $api->assertSuccessful();
    }

    public function test_product_group_api_call_read_without_ulid_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault())
                    ->create();

        $this->actingAs($user);

        $this->getJson(route('api.get.db.product.product_group.read', null));
    }

    public function test_product_group_api_call_read_with_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(ProductGroup::factory())
                    )->create();

        $this->actingAs($user);

        $ulid = $this->faker->uuid();

        $api = $this->getJson(route('api.get.db.product.product_group.read', $ulid));

        $api->assertStatus(404);
    }
}
