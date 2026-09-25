<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_code',
        'name',
        'email',
        'password',
        'role_id',
        'avatar',
        'phone',
        'description',
    ];

    /**
     * Tự động sinh mã nhân viên nếu chưa có khi tạo mới.
     */
    protected static function booted()
    {
        static::created(function ($user) {
            if (empty($user->user_code)) {
                $user->updateQuietly([
                    'user_code' => 'NV' . str_pad($user->id, 4, '0', STR_PAD_LEFT)
                ]);
            }
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class);
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class);
    }

    public function marketGroups()
    {
        return $this->belongsToMany(MarketGroup::class, 'market_group_user');
    }
}
