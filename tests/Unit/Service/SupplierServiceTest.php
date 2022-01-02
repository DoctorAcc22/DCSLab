<?php

namespace Tests\Unit\Service;

use App\Services\SupplierService;
use App\Models\Company;
use App\Models\Supplier;
use Illuminate\Support\Facades\Config;
use Vinkla\Hashids\Facades\Hashids;
use App\Actions\RandomGenerator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SupplierServiceTest extends TestCase
{
    use WithFaker;
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(SupplierService::class);
    }

    public function test_example()
    {
        $this->assertTrue(true);
    }
    /*
    public function test_read()
    {
        $selectedCompanyId = Company::inRandomOrder()->get()[0]->id;

        $response = $this->service->read($selectedCompanyId, '', true, 10);

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertTrue(!is_null($response));
    }

    public function test_create()
    {
        $paymentTermType = ['PIA','NET30','EOM','COD','CND'];

        shuffle($paymentTermType);

        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $payment_term_type = $paymentTermType[0];
        $contact = $this->faker->e164PhoneNumber;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $taxable_enterprise = (new RandomGenerator())->generateNumber(0, 1);
        $tax_id = $this->faker->name;
        $remarks = $this->faker->words;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $poc = [
            'name' => $this->faker->name,
            'email' => $this->faker->email 
        ];

        $products = [];

        $this->service->create(
            $company_id,
            $code,
            $name,
            $payment_term_type,
            $contact,
            $address,
            $city,
            $taxable_enterprise,
            $tax_id,
            $remarks,
            $status,
            $poc,
            $products
        );

        $this->assertDatabaseHas('suppliers', [
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name,
            'payment_term_type' => $payment_term_type,
            'contact' => $contact,
            'address' => $address,
            'city' => $city,
            'taxable_enterprise' => $taxable_enterprise,
            'tax_id' => $tax_id,
            'remarks' => $remarks,
            'status' => $status
        ]);
    }

    public function test_update()
    {
        $paymentTermType = ['PIA','NET30','EOM','COD','CND'];

        shuffle($paymentTermType);

        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $payment_term_type = $paymentTermType[0];
        $contact = $this->faker->e164PhoneNumber;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $taxable_enterprise = (new RandomGenerator())->generateNumber(0, 1);
        $tax_id = $this->faker->name;
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $poc = [
            'name' => $this->faker->name,
            'email' => $this->faker->email 
        ];

        $products = [];

        $response = $this->service->create(
            $company_id,
            $code,
            $name,
            $payment_term_type,
            $contact,
            $address,
            $city,
            $taxable_enterprise,
            $tax_id,
            $remarks,
            $status,
            $poc,
            $products
        );

        $rId = Hashids::decode($response->hId)[0];

        shuffle($paymentTermType);

        $code_new = (new RandomGenerator())->generateNumber(1, 9999);
        $name_new = $this->faker->name;
        $payment_term_type_new = $paymentTermType[0];
        $contact_new = $this->faker->e164PhoneNumber;
        $address_new = $this->faker->address;
        $city_new = $this->faker->city;
        $taxable_enterprise_new = (new RandomGenerator())->generateNumber(0, 1);
        $tax_id_new = $this->faker->name;
        $remarks_new = '';
        $status_new = (new RandomGenerator())->generateNumber(0, 1);
        
        $response = $this->service->update(
            $rId, 
            $company_id,
            $code_new,
            $name_new,
            $payment_term_type_new,
            $contact_new,
            $address_new,
            $city_new,
            $taxable_enterprise_new,
            $tax_id_new,
            $remarks_new,
            $status_new
        );

        $this->assertDatabaseHas('suppliers', [
            'id' => $rId,
            'company_id' => $company_id,
            'code' => $code_new,
            'name' => $name_new,
            'payment_term_type' => $payment_term_type_new,
            'contact' => $contact_new,
            'address' => $address_new,
            'city' => $city_new,
            'taxable_enterprise' => $taxable_enterprise_new,
            'tax_id' => $tax_id_new,
            'remarks' => $remarks_new,
            'status' => $status_new
        ]);
    }

    public function test_delete()
    {
        $paymentTermType = ['PIA','NET30','EOM','COD','CND'];

        shuffle($paymentTermType);

        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $payment_term_type = $paymentTermType[0];
        $contact = $this->faker->e164PhoneNumber;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $taxable_enterprise = (new RandomGenerator())->generateNumber(0, 1);
        $tax_id = $this->faker->name;
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $poc = [
            'name' => $this->faker->name,
            'email' => $this->faker->email 
        ];

        $products = [];

        $response = $this->service->create(
            $company_id,
            $code,
            $name,
            $payment_term_type,
            $contact,
            $address,
            $city,
            $taxable_enterprise,
            $tax_id,
            $remarks,
            $status,
            $poc,
            $products
        );

        $rId = Hashids::decode($response->hId)[0];

        $response = $this->service->delete($rId);
        $deleted_at = Supplier::withTrashed()->find($rId)->deleted_at->format('Y-m-d H:i:s');
        
        $this->assertDatabaseHas('suppliers', [
            'id' => $rId,
            'deleted_at' => $deleted_at
        ]);
    }
    */
}