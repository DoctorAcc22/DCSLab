<?php

namespace Tests\Feature;

use App\Actions\Employee\EmployeeActions;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeAccess;
use App\Models\Profile;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmployeeActionsEditTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employeeActions = new EmployeeActions();
    }

    public function test_employee_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
                ->has(Profile::factory()->setStatusActive())
                ->has(Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    ->has(Branch::factory()->setStatusActive()->count(4))
                )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $employee = Employee::factory()->for($company)->for($user)->create();

        $branchCount = $company->branches()->count();
        $accessCount = $this->faker->numberBetween(1, $branchCount);
        $branches = $company->branches()->inRandomOrder()->take($accessCount)->get();
        for ($i = 0; $i < $accessCount; $i++) {
            EmployeeAccess::factory()
                ->for($company)->for($employee)->for($branches[$i])
            ->create();
        }

        $newEmployeeArr = Employee::factory()->for($company)->make()->toArray();
        $newEmployeeUserArr = User::factory()->make()->toArray();
        $newProfileArr = Profile::factory()->for($user)->setStatusActive()->make()->toArray();

        $newBranchCount = $company->branches()->count();
        $newAccessCount = $this->faker->numberBetween(1, $newBranchCount);
        $newBranchIds = $company->branches()->inRandomOrder()->take($newAccessCount)->pluck('id');
        $newAccessesArr = [];
        for ($i = 0; $i < $newAccessCount; $i++) {
            array_push($newAccessesArr, [
                'branch_id' => $newBranchIds[$i],
            ]);
        }

        $result = $this->employeeActions->update(
            $employee,
            $newEmployeeArr,
            $newEmployeeUserArr,
            $newProfileArr,
            $newAccessesArr
        );

        $this->assertInstanceOf(Employee::class, $result);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'company_id' => $newEmployeeArr['company_id'],
            'code' => $newEmployeeArr['code'],
        ]);

        $this->assertDatabaseHas('users', [
            'name' => $newEmployeeUserArr['name'],
            'email' => $user->email,
        ]);

        $this->assertDatabaseHas('profiles', [
            'address' => $newProfileArr['address'],
            'city' => $newProfileArr['city'],
            'postal_code' => $newProfileArr['postal_code'],
            'country' => $newProfileArr['country'],
            'tax_id' => $newProfileArr['tax_id'],
            'ic_num' => $newProfileArr['ic_num'],
            'status' => $newProfileArr['status'],
            'remarks' => $newProfileArr['remarks'],
        ]);

        for ($i = 0; $i < $newAccessCount; $i++) {
            $this->assertDatabaseHas('employee_accesses', [
                'employee_id' => $employee->id,
                'branch_id' => $newBranchIds[$i],
            ]);
        }
    }

    public function test_employee_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
                ->has(Profile::factory()->setStatusActive())
                ->has(Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    ->has(Branch::factory()->setStatusActive()->count(4))
                )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $employee = Employee::factory()->for($company)->for($user)->create();

        $branchCount = $company->branches()->count();
        $accessCount = $this->faker->numberBetween(1, $branchCount);
        $branches = $company->branches()->inRandomOrder()->take($accessCount)->get();
        for ($i = 0; $i < $accessCount; $i++) {
            EmployeeAccess::factory()
                ->for($company)->for($employee)->for($branches[$i])
            ->create();
        }

        $newEmployeeArr = [];
        $newEmployeeUserArr = [];
        $newProfileArr = [];
        $newAccessesArr = [];

        $this->employeeActions->update(
            $employee,
            $newEmployeeArr,
            $newEmployeeUserArr,
            $newProfileArr,
            $newAccessesArr
        );
    }
}