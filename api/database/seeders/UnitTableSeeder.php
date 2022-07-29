<?php

namespace Database\Seeders;

use App\Actions\RandomGenerator;
use App\Enums\UnitCategory;
use App\Models\Company;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class UnitTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($countPerCompany = 5, $onlyThisCompanyId = 0, $category = 0)
    {
        if ($onlyThisCompanyId != 0) {
            $c = Company::find($onlyThisCompanyId);

            if ($c) {
                $companies = (new Collection())->push($c->id);
            } else {
                $companies = Company::get()->pluck('id');
            }
        } else {
            $companies = Company::get()->pluck('id');
        }

        foreach ($companies as $c) {
            if ($category == 0) {
                Unit::factory()->count($countPerCompany)->create([
                    'company_id' => $c,
                ]);
            }
            if ($category == UnitCategory::PRODUCTS->value) {
                Unit::factory()->setCategoryToProduct()->count($countPerCompany)->create([
                    'company_id' => $c,
                ]);
            }
            if ($category == UnitCategory::SERVICES->value) {
                Unit::factory()->setCategoryToService()->count($countPerCompany)->create([
                    'company_id' => $c,
                ]);
            }
            if ($category == UnitCategory::PRODUCTS_AND_SERVICES->value) {
                Unit::factory()->setCategoryToProductAndService()->count($countPerCompany)->create([
                    'company_id' => $c,
                ]);
            }
        }
    }
}
