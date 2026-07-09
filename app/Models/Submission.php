<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Submission extends Model
{
    protected $table = 'submission';
    protected $primaryKey = 'submissions_id';
    public $incrementing = true;
    protected $keyType = 'int';
    const CREATED_AT = 'submissions_create_date';
    const UPDATED_AT = 'submissions_update_date';

    protected $fillable = [
        'submissions_uuid',
        'submissions_category_uuid',
        'submissions_submissions_number',
        'submissions_user_uuid',
        'submissions_date',
        'submissions_amount',
        'submissions_description',
        'submissions_reject_reason',
        'submissions_create_by',
        'submissions_update_by',
        'submissions_status'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->submissions_uuid)) {
                $model->submissions_uuid = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'submissions_user_uuid', 'users_uuid');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'submissions_category_uuid', 'categories_uuid');
    }

    public function documents()
    {
        return $this->hasMany(DocumentSubmission::class, 'document_submission_submission_uuid', 'submissions_uuid');
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'approvals_submissions_uuid', 'submissions_uuid')->orderBy('approvals_step');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'payments_submissions_uuid', 'submissions_uuid');
    }
}
