<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    const ROLES = [
        'Super Admin',
        'Management',
        'HOD',
        'Manager',
        'Staff',
    ];

    const DEPARTMENT_HIERARCHY = [
        'SBU' => [
            'Creative' => 'Creative',
            'Digital' => 'Digital',
            'Tech' => 'Tech',
            'PM' => 'PM',
            'Corporate' => 'Corporate',
        ],
        'Sales' => [
            'AM' => 'AM',
            'BD' => 'BD',
        ]
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'supervisor_id',
        'department',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    /**
     * Resolve the HOD Name for this user.
     * 
     * @return string
     */
    public function getHodNameAttribute()
    {
        // 1. Direct supervisor check if supervisor is an HOD
        if ($this->supervisor && $this->supervisor->hasRole('HOD')) {
            return $this->supervisor->name;
        }

        // 2. Department HOD lookup
        if ($this->department) {
            $hod = User::where('department', $this->department)->where('role', 'HOD')->first();
            if ($hod) {
                return $hod->name;
            }
        }

        // 3. Fallback to supervisor name if available, else 'Not Assigned'
        if ($this->supervisor) {
            return $this->supervisor->name;
        }

        return 'Not Assigned';
    }

    /**
     * Check if user has a specific role
     * 
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        // Normalize role strings for comparison (e.g., 'super_admin' -> 'Super Admin')
        // This is a simple implementation. You might want to use a more robust role management system later.

        // Exact match
        if ($this->role === $role) {
            return true;
        }

        // Case-insensitive match normalization
        $normalizedInput = str_replace('_', ' ', strtolower($role));
        $normalizedStored = strtolower($this->role);

        return $normalizedInput === $normalizedStored;
    }

    public function deals()
    {
        return $this->belongsToMany(Deal::class, 'deal_user');
    }
}
