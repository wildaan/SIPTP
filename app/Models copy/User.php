<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'users_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'users_uuid',
        'users_roles_uuid',
        'users_email',
        'users_user_name',
        'users_password',
        'users_create_date',
        'users_create_by',
        'users_update_date',
        'users_update_by',
        'users_status',
        'users_is_admin'
    ];

    protected $hidden = [
        'users_password',
    ];

    /**
     * Get the password for the user.
     * Override authenticatable method to use custom password column.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->users_password;
    }

    /**
     * Relasi ke tabel roles via uuid
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'users_roles_uuid', 'roles_uuid');
    }
}
