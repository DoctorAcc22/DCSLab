<?php

namespace App\Http\Requests;

use App\Enums\PaymentTermType;
use App\Enums\RecordStatus;
use App\Models\Supplier;
use App\Rules\isValidCompany;
use App\Rules\isValidProduct;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Vinkla\Hashids\Facades\Hashids;

class SupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $supplier = $this->route('supplier');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'list':
                return $user->can('viewAny', Supplier::class) ? true : false;
            case 'read':
                return $user->can('view', Supplier::class, $supplier) ? true : false;
            case 'store':
                return $user->can('create', Supplier::class) ? true : false;
            case 'update':
                return $user->can('update', Supplier::class, $supplier) ? true : false;
            case 'delete':
                return $user->can('delete', Supplier::class, $supplier) ? true : false;
            default:
                return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $nullableArr = [
            'address' => ['nullable', 'max:255'],
            'contact' => ['nullable', 'max:255'],
            'city' => ['nullable', 'max:255'],
            'tax_id' => ['nullable', 'max:255'],
            'remarks' => ['nullable', 'max:255'],
            'pic_name' => ['nullable', 'max:255'],
            'productIds.*' => ['nullable', new isValidProduct($this->company_id)],
            'mainProducts.*' => ['nullable', new isValidProduct($this->company_id)],
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'list':
                $rules_list = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'search' => ['present', 'string'],
                    'paginate' => ['required', 'boolean'],
                    'page' => ['required_if:paginate,true', 'numeric'],
                    'perPage' => ['required_if:paginate,true', 'numeric'],
                    'refresh' => ['nullable', 'boolean'],
                ];

                return $rules_list;
            case 'read':
                $rules_read = [

                ];

                return $rules_read;
            case 'store':
                $rules_store = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'max:255'],
                    'status' => [new Enum(RecordStatus::class)],
                    'payment_term_type' => [new Enum(PaymentTermType::class)],
                    'payment_term' => ['required', 'numeric'],
                    'taxable_enterprise' => ['required', 'boolean'],
                    'email' => ['required', 'email'],
                ];

                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'max:255'],
                    'status' => [new Enum(RecordStatus::class)],
                    'payment_term_type' => [new Enum(PaymentTermType::class)],
                    'payment_term' => ['required', 'numeric'],
                    'taxable_enterprise' => ['required', 'boolean'],
                    'email' => ['required', 'email'],
                ];

                return array_merge($rules_update, $nullableArr);
            default:
                return [
                    '' => 'required',
                ];
        }
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.supplier.company'),
        ];
    }

    public function validationData()
    {
        $additionalArray = [];

        return array_merge($this->all(), $additionalArray);
    }

    public function prepareForValidation()
    {
        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'list':
                $this->merge([
                    'company_id' => $this->has('companyId') ? Hashids::decode($this['companyId'])[0] : '',
                    'paginate' => $this->has('paginate') ? filter_var($this->paginate, FILTER_VALIDATE_BOOLEAN) : true,
                ]);
                break;
            case 'read':
                $this->merge([]);
                break;
            case 'store':
            case 'update':
                $productIds = [];
                if ($this->has('product_hIds')) {
                    for ($i = 0; $i < count($this->product_hIds); $i++) {
                        array_push($productIds, Hashids::decode($this['product_hIds'][$i])[0]);
                    }
                }

                $mainProducts = [];
                if ($this->has('main_product_hIds')) {
                    for ($i = 0; $i < count($this->main_product_hIds); $i++) {
                        array_push($mainProducts, Hashids::decode($this['main_product_hIds'][$i])[0]);
                    }
                }

                $this->merge([
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',
                    'taxable_enterprise' => $this->has('taxable_enterprise') ? filter_var($this->taxable_enterprise, FILTER_VALIDATE_BOOLEAN) : false,
                    'payment_term_type' => PaymentTermType::isValid($this->payment_term_type) ? PaymentTermType::resolveToEnum($this->payment_term_type)->value : '',
                    'status' => RecordStatus::isValid($this->status) ? RecordStatus::resolveToEnum($this->status)->value : -1,
                    'productIds' => $productIds,
                    'mainProducts' => $mainProducts,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
