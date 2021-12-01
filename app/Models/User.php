<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laratrust\Traits\LaratrustUserTrait;
use Cmgmyr\Messenger\Traits\Messagable;
use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;

use Laravel\Sanctum\HasApiTokens;
use Vinkla\Hashids\Facades\Hashids;
use Spatie\Activitylog\ActivityLogger;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable implements MustVerifyEmail
{
    use LaratrustUserTrait;
    use HasFactory, HasApiTokens, Notifiable;
    use LogsActivity;
    use PivotEventTrait;
    use Messagable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected static $logAttributes = ['name'];

    protected static $logOnlyDirty = true;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'password',
        'password_changed_at',
        'remember_token',
        'email_verified_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'created_at',
        'updated_at',
        'pivot',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['hId', 'emailVerified', 'selectedRoles', 'selectedSettings', 'passwordExpiryDay'];

    public function getHIdAttribute() : string
    {
        return HashIds::encode($this->attributes['id']);
    }

    public function getEmailVerifiedAttribute()
    {
        return !is_null($this->email_verified_at);
    }

    public function getSelectedRolesAttribute()
    {
        return $this->roles()->get()->pluck('hId');
    }

    public function getSelectedSettingsAttribute()
    {
        $settings = array();
        foreach ($this->settings as $s) {
            $skey = '';
            switch ($s->key) {
                case 'PREFS.THEME':
                    $skey = 'theme';
                    break;
                case 'PREFS.DATE_FORMAT':
                    $skey = 'dateFormat';
                    break;
                case 'PREFS.TIME_FORMAT':
                    $skey = 'timeFormat';
                    break;
                default:
                    break;
            }
            $settings[$skey] = $s->value;
        }
        return $settings;
    }

    public function getPasswordExpiryDayAttribute()
    {
        if (is_null($this->password_changed_at))
            return 0;

        return Carbon::now()->diffInDays(Carbon::parse($this->password_changed_at)->addDays(Config::get('const.DEFAULT.PASSWORD_EXPIRY_DAYS')));
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }

    public function supplier()
    {
        return $this->hasMany(Supplier::class);
    }

    public function getSetting($key)
    {
        return $this->settings()->where('key', $key)->pluck('value')->first();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public static function boot()
    {
        parent::boot();

        static::pivotAttached(function ($model, $relationName, $pivotIds, $pivotIdsAttributes) {
            $logger = app(ActivityLogger::class)->useLog($model->getLogNameToUse('pivotAttached'));

            foreach ($pivotIds as $pivotId) {
                $properties = [
                    'relationName' => $relationName,
                    'pivot_id' => $pivotId,
                    'pivotData' => empty($pivotIdsAttributes[$pivotId]) ? [] : $pivotIdsAttributes[$pivotId],
                ];

                $logger->performedOn($model)->withProperties($properties);

                if (method_exists($model, 'tapActivity')) {
                    $logger->tap([$model, 'tapActivity'], 'pivotAttached');
                }

                $logger->log($model->getDescriptionForEvent('pivotAttached'));
            }
        });

        static::pivotDetached(function ($model, $relationName, $pivotIds) {
            $logger = app(ActivityLogger::class)->useLog($model->getLogNameToUse('pivotDetached'));

            foreach ($pivotIds as $pivotId) {
                $properties = [
                    'relationName' => $relationName,
                    'pivot_id' => $pivotId,
                    'pivotData' => empty($pivotIdsAttributes[$pivotId]) ? [] : $pivotIdsAttributes[$pivotId],
                ];

                $logger->performedOn($model)->withProperties($properties);

                $logger->causedBy($model);

                if (method_exists($model, 'tapActivity')) {
                    $logger->tap([$model, 'tapActivity'], 'pivotDetached');
                }

                $logger->log($model->getDescriptionForEvent('pivotDetached'));
            }
        });

        static::pivotUpdated(function ($model, $relationName, $pivotIds) {
            $logger = app(ActivityLogger::class)->useLog($model->getLogNameToUse('pivotUpdated'));

            foreach ($pivotIds as $pivotId) {
                $properties = [
                    'relationName' => $relationName,
                    'pivot_id' => $pivotId,
                    'pivotData' => empty($pivotIdsAttributes[$pivotId]) ? [] : $pivotIdsAttributes[$pivotId],
                ];

                $logger->performedOn($model)->withProperties($properties);

                $logger->causedBy($model);

                if (method_exists($model, 'tapActivity')) {
                    $logger->tap([$model, 'tapActivity'], 'pivotUpdated');
                }

                $logger->log($model->getDescriptionForEvent('pivotUpdated'));
            }
        });
    }
}
