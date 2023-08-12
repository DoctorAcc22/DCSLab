<?php

namespace App\Actions\Employee;

use App\Actions\Randomizer\RandomizerActions;
use App\Actions\User\UserActions;
use App\Enums\RecordStatus;
use App\Enums\UserRoles;
use App\Models\Employee;
use App\Models\EmployeeAccess;
use App\Models\Role;
use App\Models\User;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EmployeeActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(
        array $employeeArr,
        array $userArr,
        array $profileArr,
        array $accessesArr
    ): Employee {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $rolesArr = [];
            array_push($rolesArr, Role::where('name', '=', UserRoles::POS_EMPLOYEE->value)->first()->id);

            $userActions = app(UserActions::class);

            $user = $userActions->create(
                $userArr,
                $rolesArr,
                $profileArr
            );

            $employee = new Employee();
            $employee->company_id = $employeeArr['company_id'];
            $employee->user_id = $user->id;
            $employee->code = $employeeArr['code'];
            $employee->join_date = $employeeArr['join_date'];
            $employee->status = $employeeArr['status'];

            $employee->save();

            $newAccesses = [];
            foreach ($accessesArr as $access) {
                $newAccess = new EmployeeAccess();
                $newAccess->employee_id = $employee->id;
                $newAccess->company_id = $employee['company_id'];
                $newAccess->branch_id = $access['branch_id'];

                array_push($newAccesses, $newAccess);
            }
            $employee->employeeAccesses()->saveMany($newAccesses);

            DB::commit();

            $this->flushCache();

            return $employee;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function readAny(
        int $companyId,
        string $search,
        bool $paginate,
        int $page = 1,
        int $perPage = 10,
        array $with = [],
        bool $withTrashed = false,
        bool $useCache = true
    ): Paginator|Collection {
        $timer_start = microtime(true);
        $recordsCount = 0;

        try {
            $cacheSearch = empty($search) ? '[empty]' : $search;
            $cacheKey = 'readAny_'.$companyId.'_'.$cacheSearch.'-'.$paginate.'-'.$page.'-'.$perPage;
            if ($useCache) {
                $cacheResult = $this->readFromCache($cacheKey);

                if (! is_null($cacheResult)) {
                    return $cacheResult;
                }
            }

            $result = null;

            if (! $companyId) {
                return null;
            }

            $relationship = ['company', 'user.profile', 'employeeAccesses.branch'];
            $relationship = count($with) > 0 ? $with : $relationship;
            $query = Employee::with($relationship);

            $query = $query->whereCompanyId($companyId);

            if (! empty($search)) {
                $query = $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', '%'.$search.'%')
                        ->orWhere('join_date', 'like', '%'.$search.'%')
                        ->orWhere('user', 'like', '%'.$search.'%')
                        ->orWhereHas('user', function ($query) use ($search) {
                            $query->where('name', 'like', '%'.$search.'%');
                        });
                });
            }

            if ($withTrashed) {
                $query = $query->withTrashed();
            }

            if ($paginate) {
                $perPage = is_numeric($perPage) ? $perPage : Config::get('dcslab.PAGINATION_LIMIT');
                $result = $query->paginate(abs($perPage));
            } else {
                $result = $query->get();
            }

            $recordsCount = $result->count();

            $this->saveToCache($cacheKey, $result);

            return $result;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time, $recordsCount);
        }
    }

    public function read(Employee $employee): Employee
    {
        return $employee->with('company', 'user.profile', 'employeeAccesses')->first();
    }

    public function update(
        Employee $employee,
        array $employeeArr,
        array $userArr,
        array $profileArr,
        array $accessesArr
    ): Employee {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $userActions = app(UserActions::class);
            $userActions->update(
                user: $employee->user,
                userArr: $userArr,
                profileArr: $profileArr
            );

            $employee->update([
                'code' => $employeeArr['code'],
                'status' => $employeeArr['status'],
            ]);

            $employee->employeeAccesses()->delete();

            if (! empty($accessesArr)) {
                $newAccesses = [];

                foreach ($accessesArr as $access) {
                    $newAccess = new EmployeeAccess();
                    $newAccess->employee_id = $employee->id;
                    $newAccess->company_id = $employee['company_id'];
                    $newAccess->branch_id = $access['branch_id'];

                    array_push($newAccesses, $newAccess);
                }

                $employee->employeeAccesses()->saveMany($newAccesses);
            }

            DB::commit();

            $this->flushCache();

            return $employee->refresh();
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(Employee $employee): bool
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        $retval = false;
        try {
            $employee->employeeAccesses()->delete();

            $user = User::find($employee->user_id);
            $user->profile->status = RecordStatus::INACTIVE->value;

            $retval = $employee->delete();

            DB::commit();

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function generateUniqueCodeForProduct(): string
    {
        $rand = app(RandomizerActions::class);
        $code = $rand->generateAlpha().$rand->generateNumeric();

        return $code;
    }

    public function isUniqueCode(string $code, int $companyId, ?int $exceptId = null): bool
    {
        $result = Employee::whereCompanyId($companyId)->where('code', '=', $code);

        if ($exceptId) {
            $result = $result->where('id', '<>', $exceptId);
        }

        return $result->count() == 0 ? true : false;
    }
}
