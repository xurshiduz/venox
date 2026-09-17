<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * Archived users must behave like deleted users throughout the system.
     * Controllers that manage the archive can explicitly remove this scope.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('active', function (Builder $builder): void {
            $statusColumn = $builder->getModel()->qualifyColumn('status');
            $builder->where(function (Builder $activeQuery) use ($statusColumn): void {
                $activeQuery->where($statusColumn, 1)->orWhereNull($statusColumn);
            });
        });
    }

    public function scopeWithArchived(Builder $query): Builder
    {
        return $query->withoutGlobalScope('active');
    }

    public function scopeOnlyArchived(Builder $query): Builder
    {
        return $query->withoutGlobalScope('active')->where($this->qualifyColumn('status'), 0);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'm_factory_type',
        'name',
        'email',
        'password',
        'dealer_id',
        'username',
        'warehouse_id',
        'code',
        'dark_mode',
        'phone',
        'status',
        'phone',
        'text_password',
        'iscompact'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function uroles()
    {
        return $this->hasMany('App\Models\ModelHasRole', 'model_id');
    }

    public function dealerid()
    {
        return $this->belongsTo('App\Models\Dealer', 'dealer_id');
    }
    
    public function checkouts()
    {
        return $this->hasMany('App\Models\Checkout', 'manager_id');
    }
    
    public function sessiontime()
    {
        return $this->hasMany('App\Models\Session', 'user_id');
    }
}
