<?php

namespace App\Http\Requests;

use App\Enums\RecordStatus;
use App\Models\Employee;
use App\Rules\isValidBranch;
use App\Rules\isValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Vinkla\Hashids\Facades\Hashids;

class EmployeeRequest extends FormRequest
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
        $employee = $this->route('employee');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'list':
                return $user->can('viewAny', Employee::class) ? true : false;
            case 'read':
                return $user->can('view', Employee::class, $employee) ? true : false;
            case 'store':
                return $user->can('create', Employee::class) ? true : false;
            case 'update':
                return $user->can('update', Employee::class, $employee) ? true : false;
            case 'delete':
                return $user->can('delete', Employee::class, $employee) ? true : false;
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
            'city' => ['nullable', 'max:255'],
            'postal_code' => ['nullable', 'max:10'],
            'img_path' => ['nullable'],
            'remarks' => ['nullable', 'max:255'],
            'accessBranchIds.*' => ['nullable', new isValidBranch($this->company_id)],
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
                    'name' => ['required', 'min:3', 'max:255'],
                    'email' => ['required', 'email', 'max:255'],
                    'country' => ['required'],
                    'tax_id' => ['required'],
                    'ic_num' => ['required', 'min:12', 'max:255'],
                    'join_date' => ['required'],
                    'status' => [new Enum(RecordStatus::class)],
                ];

                $employeeRequest = array_merge($rules_store, $nullableArr);

                return $employeeRequest;
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'code' => ['required', 'max:255'],
                    'name' => ['required', 'min:3', 'max:255'],
                    'country' => ['required'],
                    'tax_id' => ['required'],
                    'ic_num' => ['required', 'min:12', 'max:255'],
                    'status' => [new Enum(RecordStatus::class)],
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
            'company_id' => trans('validation_attributes.employee.company'),
            'code' => trans('validation_attributes.employee.code'),
            'name' => trans('validation_attributes.employee.name'),
            'email' => trans('validation_attributes.employee.email'),
            'address' => trans('validation_attributes.employee.address'),
            'city' => trans('validation_attributes.employee.city'),
            'postal_code' => trans('validation_attributes.employee.postal_code'),
            'country' => trans('validation_attributes.employee.country'),
            'tax_id' => trans('validation_attributes.employee.tax_id'),
            'ic_num' => trans('validation_attributes.employee.ic_num'),
            'join_date' => trans('validation_attributes.employee.join_date'),
            'remarks' => trans('validation_attributes.employee.remarks'),
            'status' => trans('validation_attributes.employee.status'),
            'accessBranchIds.*' => trans('validation_attributes.employee.branch'),
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
                $accessBranchIds = [];
                if ($this->has('access_branch_hIds')) {
                    for ($i = 0; $i < count($this->access_branch_hIds); $i++) {
                        array_push($accessBranchIds, Hashids::decode($this->access_branch_hIds[$i])[0]);
                    }
                }

                $this->merge([
                    'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0] : '',
                    'status' => RecordStatus::isValid($this->status) ? RecordStatus::resolveToEnum($this->status)->value : -1,
                    'accessBranchIds' => $accessBranchIds,
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }
    }
}
