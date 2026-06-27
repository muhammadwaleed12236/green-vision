<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasPermission(string $slug): bool
    {
        $role = $this->role;
        if (!$role && $this->usertype) {
            $role = Role::where('slug', $this->usertype)->first();
        }

        if (!$role) {
            return $this->usertype === 'admin';
        }

        if ($role->slug === 'super-admin') {
            return true;
        }

        return $role->permissions()
            ->where(function($q) use ($slug) {
                $q->where('slug', $slug);
                $parts = explode('.', $slug);
                if (count($parts) > 1) {
                    array_pop($parts);
                    $parentSlug = implode('.', $parts);
                    $q->orWhere('slug', $parentSlug);
                }
            })
            ->exists();
    }

    public function getUsertypeAttribute($value)
    {
        if ($value === 'user') {
            return 'admin';
        }
        return $value;
    }
}
