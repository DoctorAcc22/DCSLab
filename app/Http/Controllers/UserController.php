<?php

namespace App\Http\Controllers;

use App\Actions\RandomGenerator;
use App\Http\Requests\UserRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use App\Services\RoleService;
use Exception;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

class UserController extends BaseController
{
    private $userService;
    private $roleService;

    public function __construct(UserService $userService, RoleService $roleService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->userService = $userService;
        $this->roleService = $roleService;
    }

    public function read(UserRequest $userRequest)
    {
        $request = $userRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->userService->read($search, $paginate, $page, $perPage);
        } catch(Exception $e) {
            $errorMsg = $e->getMessage();
        }
        
        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = UserResource::collection($result);
            return $response;    
        }
    }

    public function getAllRoles()
    {
        $excludeRole = [
        ];

        $roles = null;
        $errorMsg = '';

        try {
            $roles = $this->roleService->read(exclude: $excludeRole);
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
        }
        
        if (is_null($roles)) {
            return response()->error($errorMsg);
        } else {
            $response = RoleResource::collection($roles);
            return $response;    
        }
    }

    public function store(UserRequest $userRequest)
    {
        //Throw Error
        //throw New \Exception('Test Exception From Controller');
        
        //Throw Empty Response Error (HttpStatus 500)
        //return response()->error();

        //Custom Validation Error 1 Message (HttpStatus 422)
        //return response()->error('Custom Validation Error 1 Message', 422);

        //Custom Validation With Multiple Error (HttpStatus 422)
        //return response()->error(['name' => ['Custom Validation With Multiple Error'], 'email' => ['Custom Validation With Multiple Error']], 422);

        $request = $userRequest->validated();

        $request['password'] = (new RandomGenerator())->generateAlphaNumeric(10);

        $user = [
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => $request['password']
        ];

        $profile = array (
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'postal_code' => $request['postal_code'],
            'country' => $request['country'],
            'tax_id' => $request['tax_id'],
            'ic_num' => $request['ic_num'],
            'status' => $request['status'],
            'remarks' => $request['remarks'],
        );

        if (array_key_exists('img_path', $request)) {
            $image = $request['img_path'];
            $filename = time().".".$image->getClientOriginalExtension();

            $file = $image->storePubliclyAs('usr', $filename, 'public');
            $profile['img_path'] = $file;
        }

        $roles = [];
        foreach ($request['roles'] as $r) {
            array_push($rolesId, Hashids::decode($r)[0]);
        }

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->userService->create(
                $user,
                $roles,
                $profile
            );    
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(User $user, UserRequest $userRequest)
    {
        $request = $userRequest->validated();

        $userArr = array (
            'name' => $request['name']
        );

        $profile = array (
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'postal_code' => $request['postal_code'],
            'country' => $request['country'],
            'tax_id' => $request['tax_id'],
            'ic_num' => $request['ic_num'],
            'status' => $request['status'],
            'remarks' => $request['remarks'],
        );

        $roles = [];
        foreach ($request['roles'] as $r) {
            array_push($roles, Hashids::decode($r)[0]);
        }

        $settings = [
            'PREFS.THEME' => $request['theme'],
            'PREFS.DATE_FORMAT' => $request['dateFormat'],
            'PREFS.TIME_FORMAT' => $request['timeFormat'],
        ];

        if (array_key_exists('img_path', $request)) {
            $image = $request['img_path'];
            $filename = time().".".$image->getClientOriginalExtension();

            $file = $image->storePubliclyAs('usr', $filename, 'public');
            $profile['img_path'] = $file;
        }

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->userService->update(
                $user,
                $userArr,
                $roles,
                $profile,
                $settings
            );

            if (array_key_exists('apiToken', $request))
                $this->userService->resetTokens($user);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function resetPassword($id)
    {
        $usr = $this->userService->readBy('ID', $id);

        $this->userService->resetPassword($usr['email']);
    }
}
