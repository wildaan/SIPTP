<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'roles_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'roles_uuid',
        'roles_code',
        'roles_name',
        'roles_create_date',
        'roles_create_by',
        'roles_update_date',
        'roles_update_by',
        'roles_status'
    ];
}
