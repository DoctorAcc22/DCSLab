<?php

namespace App\Rules;

use App\Models\Unit;
use Illuminate\Contracts\Validation\Rule;

class isValidUnit implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($company_id)
    {
        $this->company_id = $company_id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $unitIds = Unit::where('company_id', '=', $this->company_id)->pluck('id');
        return $unitIds->contains($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('rules.valid_unit');
    }
}
