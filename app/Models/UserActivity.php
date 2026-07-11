<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    protected $table = 'user_activity';
    protected $primaryKey = 'user_activity_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'user_activity_user_uuid',
        'user_activity_action',
        'user_activity_description',
        'user_activity_ip_address',
        'user_activity_create_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_activity_user_uuid', 'users_uuid');
    }
}
