<?php

namespace App\Http\Requests;

use App\Rules\uniqueCode;
use App\Rules\validDropDownValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $companyId = $this->has('company_id') ? Hashids::decode($this['company_id'])[0]:null;

        $nullableArr = [
            '' => 'nullable',
            'remarks' => 'nullable',
            'unit_id.*' => 'nullable',
            'product_units_code.*' => 'nullable',
            'is_base.*' => 'nullable',
            'is_primary_unit.*' => 'nullable',
            'conv_value.*' => 'nullable',
            'supplier_id' => 'nullable',
            'use_serial_number' => 'nullable',
            'taxable_supplies' => 'nullable',
            'rate_supplies' => 'nullable',
            'price_include_vat' => 'nullable',
            'has_expiry_date' => 'nullable'
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch($currentRouteMethod) {
            case 'store':
                $rules_store = [
                    'company_id' => ['required', 'bail'],
                    'code' => ['required', 'max:255', new uniqueCode(table: 'products', companyId: $companyId)],
                    'name' => 'required|min:3|max:255',
                    'product_group_id' => 'required',
                    'brand_id' => 'required',
                    'point' => 'required|numeric',
                    'status' => ['required', new validDropDownValue('ACTIVE_STATUS')],
                    'product_type' => [new validDropDownValue('PRODUCT_TYPE')]
                ];
                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', 'bail'],
                    'code' => ['required', 'max:255', new uniqueCode(table: 'products', companyId: $companyId, exceptId: $this->route('id'))],
                    'name' => 'required|min:3|max:255',
                    'product_group_id' => 'required',
                    'brand_id' => 'required',
                    'point' => 'required|numeric',
                    'status' => ['required', new validDropDownValue('ACTIVE_STATUS')],
                    'product_type' => [new validDropDownValue('PRODUCT_TYPE')]
                ];
                return array_merge($rules_update, $nullableArr);
            default:
                return [
                    '' => 'required'
                ];
        }
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.company'),
        ];
    }
}
