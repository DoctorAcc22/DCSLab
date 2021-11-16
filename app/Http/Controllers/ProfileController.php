<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\UpdateUserPassword;
use App\Http\Requests\ProfileRequest;
use App\Services\UserService;
use App\Services\RoleService;
use Illuminate\Support\Facades\Auth;

class ProfileController extends BaseController
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

    public function readProfile()
    {
        return $this->userService->readBy('ID', Auth::id());
    }

    public function updateProfile(ProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();
        $user = Auth::user();

        $profile = array (
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'postal_code' => $request['postal_code'],
            'country' => $request['country'],
            'tax_id' => $request['tax_id'],
            'ic_num' => $request['ic_num'],
            'remarks' => $request['remarks'],
        );

        $result = $this->userService->updateProfile($user, $profile, true);

        return is_null($result) ? response()->error():response()->success();
    }

    public function changePassword(ProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();

        $usr = Auth::user();
        $updateActions = new UpdateUserPassword();
        $updateActions->update($usr, $profileRequest->validated());
    }

    public function updateSettings(ProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();

        $usr = Auth::user();
        $settings = [
            'PREFS.THEME' => $request['theme'],
            'PREFS.DATE_FORMAT' => $request['dateFormat'],
            'PREFS.TIME_FORMAT' => $request['timeFormat'],
        ];

        $result = $this->userService->updateSettings($usr, $settings, true);

        if (array_key_exists('apiToken', $request))
            $this->userService->resetTokens($usr->id);

        return is_null($result) ? response()->error():response()->success();
    }

    public function updateRoles(ProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();


    }
}
