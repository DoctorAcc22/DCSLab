<?php

namespace App\Models;

use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Model;

use App\Models\Company;
use App\Models\Supplier;
use App\Models\Product;

class SupplierProduct extends Model
{
    use BootableModel;

    protected $table = 'supplier_products';

    protected $fillable = [
        'main_product',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
