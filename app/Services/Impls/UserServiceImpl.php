<?php

namespace App\Services\Impls;

use App\Actions\RandomGenerator;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

use Illuminate\Mail\Message;

use App\Models\User;
use App\Models\Role;
use App\Models\Profile;
use App\Models\Setting;

use App\Services\UserService;

class UserServiceImpl implements UserService
{
    public function register($name, $email, $password, $terms)
    {
        if ($name == trim($name) && strpos($name, ' ') !== false) {
            $pieces = explode(" ", $name);
            $first_name = $pieces[0];
            $last_name = $pieces[1];
        } else {
            $first_name = $name;
            $last_name = '';
        }

        $profile = array (
            'first_name' => $first_name,
            'last_name' => $last_name,
            'status' => 1,
        );

        $rolesId = array(Role::where('name', Config::get('const.DEFAULT.ROLE.USER'))->first()->id);

        $usr = $this->create(
            $name,
            $email,
            $password,
            $rolesId,
            $profile
        );

        return $usr;
    }

    public function create($name, $email, $password = '', $rolesId, $profile)
    {
        DB::beginTransaction();

        try {
            //throw New \Exception('Test Exception From Services');

            $usr = new User();
            $usr->name = $name;
            $usr->email = $email;

            if (empty($password)) {
                $usr->password = (new RandomGenerator())->generateAlphaNumeric(5);
                $usr->password_changed_at = null;
            } else {
                $usr->password = Hash::make($password);
                $usr->password_changed_at = Carbon::now();
            }

            $usr->save();

            $pa = new Profile();

            $pa->first_name = array_key_exists('first_name', $profile) ? $profile['first_name']:null;
            $pa->last_name = array_key_exists('last_name', $profile) ? $profile['last_name']:null;
            $pa->address = array_key_exists('address', $profile) ? $profile['address']:null;
            $pa->city = array_key_exists('city', $profile) ? $profile['city']:null;
            $pa->postal_code = array_key_exists('postal_code', $profile) ? $profile['postal_code']:null;
            $pa->country = array_key_exists('country', $profile) ? $profile['country']:null;
            $pa->tax_id = array_key_exists('tax_id', $profile) ? $profile['tax_id']:null;
            $pa->ic_num = array_key_exists('ic_num', $profile) ? $profile['ic_num']:null;
            $pa->status = array_key_exists('status', $profile) ? $profile['status']:null;
            $pa->img_path = array_key_exists('img_path', $profile) ? $profile['img_path']:null;
            $pa->remarks = array_key_exists('remarks', $profile) ? $profile['remarks']:null;

            $usr->profile()->save($pa);

            $settings = $this->createDefaultSetting();
            $usr->settings()->saveMany($settings);

            $usr->attachRoles($rolesId);

            if (env('AUTO_VERIFY_EMAIL', true))
                $usr->markEmailAsVerified();

            DB::commit();

            return $usr;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        }
    }

    public function flushCache($id)
    {
        Cache::tags([$id])->flush();
    }

    public function read($search = '', $paginate = true, $perPage = 10)
    {
        if (empty($search)) {
            $usr = User::with('roles', 'profile', 'settings')->latest();
        } else {
            $usr = User::with('profile')
                    ->where('email', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhereHas('profile', function ($query) use($search) {
                        $query->where('first_name', 'like', '%'.$search.'%')
                                ->orWhere('last_name', 'like', '%'.$search.'%');
                    })->latest();
        }

        if ($paginate) {
            $perPage = is_numeric($perPage) ? $perPage : Config::get('const.DEFAULT.PAGINATION_LIMIT');
            return $usr->paginate($perPage);
        } else {
            return $usr->get();
        }
    }

    public function readBy($key, $value)
    {
        switch(strtoupper($key)) {
            case 'ID':
                if (!Config::get('const.DEFAULT.DATA_CACHE.ENABLED'))
                    return User::with('roles', 'profile', 'companies')->find($value);

                return Cache::tags([$value])->remember('readByID'.$value, Config::get('const.DEFAULT.DATA_CACHE.CACHE_TIME.1_HOUR'), function() use ($value) {
                    return User::with('roles', 'profile', 'companies')->find($value);
                });
            case 'EMAIL':
                return User::where('email', '=', $value)->first();
            default:
                return null;
        }
    }

    public function update($id, $name, $rolesId, $profile, $settings)
    {
        DB::beginTransaction();

        try {
            $usr = User::find($id);

            $this->updateUser($usr, $name, false);
            $this->updateProfile($usr, $profile, false);
            $this->updateRoles($usr, $rolesId, false);
            $this->updateSettings($usr, $settings, false);

            DB::commit();

            return $usr;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        }
    }

    public function updateUser($user, $name, $useTransactions = true)
    {
        !$useTransactions ?: DB::beginTransaction();

        try {
            //DB::enableQueryLog();

            $retval = $user->update([
                'name' => $name,
            ]);

            //$queryLog = DB::getQueryLog();

            !$useTransactions ?: DB::commit();

            return $retval;
        } catch (Exception $e) {
            !$useTransactions ?: DB::rollBack();
            Log::debug($e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        }
    }

    public function updateProfile($user, $profile, $useTransactions = true)
    {
        !$useTransactions ?: DB::beginTransaction();

        try {
            if ($profile != null) {
                $pa = $user->profile()->first();

                $retval = $pa->update([
                    'first_name' => $profile['first_name'],
                    'last_name' => $profile['last_name'],
                    'address' => $profile['address'],
                    'city' => $profile['city'],
                    'postal_code' => $profile['postal_code'],
                    'country' => empty($profile['country']) ? $pa->country:$profile['country'],
                    'status' => array_key_exists('status', $profile ) ? $profile['status']:$pa->status,
                    'tax_id' => $profile['tax_id'],
                    'ic_num' => $profile['ic_num'],
                    'img_path' => array_key_exists('img_path', $profile ) ? $profile['img_path']:$pa->img_path,
                    'remarks' => $profile['remarks']
                ]);
            }

            !$useTransactions ?: DB::commit();

            return $retval;
        } catch (Exception $e) {
            !$useTransactions ?: DB::rollBack();
            Log::debug($e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        }
    }

    public function updateRoles($user, $rolesId, $useTransactions = true)
    {
        !$useTransactions ?: DB::beginTransaction();

        try {
            !$useTransactions ?: DB::commit();

            $retval = $user->syncRoles($rolesId);

            return $retval;
        } catch (Exception $e) {
            !$useTransactions ?: DB::rollBack();
            Log::debug($e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        }
    }

    public function updateSettings($user, $settings, $useTransactions = true)
    {
        !$useTransactions ?: DB::beginTransaction();

        try {
            !$useTransactions ?: DB::commit();

            $retval = 0;
            foreach ($settings as $key => $value) {
                $setting = $user->settings()->where('key', $key)->first();
                if (!$setting || $value == null) continue;
                if ($setting->value != $value) {
                    $retval += $setting->update([
                        'value' => $value,
                    ]);
                }
            }

            return $retval;
        } catch (Exception $e) {
            !$useTransactions ?: DB::rollBack();
            Log::debug($e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        }
    }

    public function resetPassword($email)
    {
        $response = Password::sendResetLink(['email' => $email], function (Message $message) {
            $message->subject('Reset Password');
        });
    }

    public function resetTokens($id)
    {
        $usr = User::find($id);

        $usr->tokens()->delete();
    }

    public function createDefaultSetting()
    {
        $list = array (
            new Setting(array(
                'type' => 'KEY_VALUE',
                'key' => 'PREFS.THEME',
                'value' => 'side-menu-light-full',
            )),
            new Setting(array(
                'type' => 'KEY_VALUE',
                'key' => 'PREFS.DATE_FORMAT',
                'value' => 'yyyy_MM_dd',
            )),
            new Setting(array(
                'type' => 'KEY_VALUE',
                'key' => 'PREFS.TIME_FORMAT',
                'value' => 'hh_mm_ss',
            )),
        );

        return $list;
    }
}
