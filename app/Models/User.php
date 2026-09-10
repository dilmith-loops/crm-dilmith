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
        'Finance Admin',
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
     * Resolve the Associated HOD User instance for this user.
     * 
     * @return \App\Models\User|null
     */
    public function getAssociatedHodAttribute()
    {
        // 1. Direct supervisor check if supervisor is an HOD
        if ($this->supervisor && $this->supervisor->hasRole('HOD')) {
            return $this->supervisor;
        }

        // 2. Department HOD lookup
        if ($this->department) {
            $hod = User::where('department', $this->department)->where('role', 'HOD')->first();
            if ($hod) {
                return $hod;
            }
        }

        // 3. Fallback to supervisor if supervisor has role HOD or Manager
        if ($this->supervisor) {
            return $this->supervisor;
        }

        return null;
    }

    /**
     * Resolve the HOD Name for this user.
     * 
     * @return string
     */
    public function getHodNameAttribute()
    {
        return $this->associated_hod ? $this->associated_hod->name : 'Not Assigned';
    }

    /**
     * Check if user has a specific role
     * 
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        // Exact match
        if ($this->role === $role) {
            return true;
        }

        // Case-insensitive match normalization
        $normalizedInput = str_replace('_', ' ', strtolower(trim($role)));
        $normalizedStored = str_replace('_', ' ', strtolower(trim($this->role)));

        if ($normalizedInput === $normalizedStored) {
            return true;
        }

        // Equate Super Admin and Finance Admin
        if (in_array($normalizedInput, ['super admin', 'finance admin']) && in_array($normalizedStored, ['super admin', 'finance admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Check if user has administrative privileges (Finance Admin, Management, or legacy Super Admin).
     *
     * @return bool
     */
    public function hasAdminPrivileges(): bool
    {
        return in_array($this->role, ['Finance Admin', 'Super Admin', 'Management']) ||
               $this->hasRole('finance_admin') ||
               $this->hasRole('management');
    }

    public function deals()
    {
        return $this->belongsToMany(Deal::class, 'deal_user');
    }
}
