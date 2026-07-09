<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Approval extends Model
{
    protected $table = 'approvals';
    protected $primaryKey = 'approvals_id';
    public $incrementing = true;
    protected $keyType = 'int';
    const CREATED_AT = 'approvals_create_date';
    const UPDATED_AT = 'approvals_update_date';

    protected $fillable = [
        'approvals_uuid',
        'approvals_submissions_uuid',
        'approvals_user_uuid',
        'approvals_roles_uuid',
        'approvals_step',
        'approvals_notes',
        'approvals_action_date',
        'approvals_create_by',
        'approvals_update_by',
        'approvals_status'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->approvals_uuid)) {
                $model->approvals_uuid = (string) Str::uuid();
            }
        });
    }

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'approvals_submissions_uuid', 'submissions_uuid');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'approvals_user_uuid', 'users_uuid');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'approvals_roles_uuid', 'roles_uuid');
    }
}
