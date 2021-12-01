<?php

namespace App\Models;

use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Model;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;

class Unit extends Model
{
    use LogsActivity;
    use SoftDeletes;

    use ScopeableByCompany;

    protected $fillable = [
        'code',
        'name',
        'description',
        'category'
    ];

    protected static $logAttributes = [
        'code',
        'name',
        'description',
        'category'
    ];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'update_at',
        'deleted_at'
    ];

    protected $appends = ['hId'];

    public function getHIdAttribute() : string
    {
        return HashIds::encode($this->attributes['id']);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function product_unit()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
