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

    public function canViewAllMarketGroups(): bool
    {
        return $this->hasRole('Admin') || $this->can('view all market groups');
    }

    /**
     * Chuẩn hóa tên quyền / role về dạng không dấu, viết thường, không khoảng trắng
     */
    public static function normalizeRoleString(?string $str): string
    {
        if (empty($str)) {
            return '';
        }
        $str = mb_strtolower(trim($str), 'UTF-8');
        $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/u', 'a', $str);
        $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/u', 'e', $str);
        $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/u', 'i', $str);
        $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/u', 'o', $str);
        $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/u', 'u', $str);
        $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/u', 'y', $str);
        $str = preg_replace('/(đ)/u', 'd', $str);
        return preg_replace('/[^a-z0-9]/', '', $str);
    }

    /**
     * Người có quyền 'approve accountant order', vai trò 'Admin' hoặc vai trò 'Kế toán' mới được duyệt ở Kế toán.
     */
    public function canApproveAccountantOrder(): bool
    {
        if ($this->hasRole('Admin') || $this->can('approve accountant order')) {
            return true;
        }

        $names = collect([$this->role?->name])
            ->concat($this->roles ? $this->roles->pluck('name') : [])
            ->concat($this->getAllPermissions() ? $this->getAllPermissions()->pluck('name') : [])
            ->filter()
            ->unique();

        foreach ($names as $name) {
            $norm = self::normalizeRoleString($name);
            if (str_contains($norm, 'ketoan') || str_contains($norm, 'accountant') || str_contains($norm, 'admin')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Người có quyền 'approve technical order', vai trò 'Admin' hoặc vai trò 'Kỹ thuật' mới được duyệt ở Kỹ thuật.
     */
    public function canApproveTechnicalOrder(): bool
    {
        if ($this->hasRole('Admin') || $this->can('approve technical order')) {
            return true;
        }

        $names = collect([$this->role?->name])
            ->concat($this->roles ? $this->roles->pluck('name') : [])
            ->concat($this->getAllPermissions() ? $this->getAllPermissions()->pluck('name') : [])
            ->filter()
            ->unique();

        foreach ($names as $name) {
            $norm = self::normalizeRoleString($name);
            if (str_contains($norm, 'kythuat') || str_contains($norm, 'technical') || str_contains($norm, 'admin')) {
                return true;
            }
        }

        return false;
    }
}

