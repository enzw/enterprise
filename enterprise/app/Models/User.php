<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'default_role',
        'current_role',
        'available_roles',
        'subsidiary_id',
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
            'available_roles' => 'json',
        ];
    }

    public function subsidiary()
    {
        return $this->belongsTo(Subsidiary::class);
    }

    public function canAccessRole($role)
    {
        $roles = is_array($this->available_roles) ? $this->available_roles : json_decode($this->available_roles, true);
        return in_array($role, $roles);
    }

    public function switchRole($role)
    {
        if ($this->canAccessRole($role)) {
            $this->current_role = $role;
            $this->save();
            return true;
        }
        return false;
    }
}
