<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone', // This is your Mobile Number
        'password',
        'role_id',
        'designation',
        'status',
        'district_id',
        'is_verified',
        'otp',
        'aadhaar_number',
        'address',
        'projects',
        'ward_number',
        'gender'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];
    /**
     * Check if user is a Customer (Role 3)
     */
    public function isCustomer()
    {
        return $this->role_id === 3;
    }

    /**
     * Relationship for Customer Projects (using extra_user)
     */
    public function customerProjects()
    {
        return $this->hasMany(\App\Models\Project::class, 'extra_user', 'id');
    }

    /**
     * Relationship for Customer Trees (using extra_usertree)
     */
    public function customerTrees()
    {
        return $this->hasMany(\App\Models\MtTree::class, 'extra_usertree', 'id');
    }

    /**
     * Relationship for Wallets
     */
    public function wallets()
    {
        return $this->hasMany(\App\Models\Wallet::class);
    }

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

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id');
    }
}
