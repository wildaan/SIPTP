<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'payments_id';
    public $incrementing = true;
    protected $keyType = 'int';
    const CREATED_AT = 'payments_create_date';
    const UPDATED_AT = 'payments_update_date';

    protected $fillable = [
        'payments_uuid',
        'payments_submissions_uuid',
        'payments_finance_user_uuid',
        'payments_date',
        'payments_amount_paid',
        'payments_method',
        'payments_transfer_proof_path',
        'payments_notes',
        'payments_create_by',
        'payments_update_by',
        'payments_status'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->payments_uuid)) {
                $model->payments_uuid = (string) Str::uuid();
            }
        });
    }

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'payments_submissions_uuid', 'submissions_uuid');
    }

    public function financeUser()
    {
        return $this->belongsTo(User::class, 'payments_finance_user_uuid', 'users_uuid');
    }
}
