<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentSubmission extends Model
{
    protected $table = 'document_submission';
    protected $primaryKey = 'document_submission_id';
    public $incrementing = true;
    protected $keyType = 'int';
    const CREATED_AT = 'document_submission_create_date';
    const UPDATED_AT = 'document_submission_update_date';

    protected $fillable = [
        'document_submission_submission_uuid',
        'document_submission_file_name',
        'document_submission_file_path',
        'document_submission_file_size',
        'document_submission_file_type',
        'document_submission_create_by',
        'document_submission_update_by',
        'document_submission_status'
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'document_submission_submission_uuid', 'submissions_uuid');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'document_submission_create_by', 'users_uuid');
    }
}
