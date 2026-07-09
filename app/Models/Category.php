<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'categories_id';
    public $incrementing = true;
    protected $keyType = 'int';
    const CREATED_AT = 'categories_create_date';
    const UPDATED_AT = 'categories_update_date';

    protected $fillable = [
        'categories_uuid',
        'categories_name',
        'categories_code',
        'categories_create_by',
        'categories_update_by',
        'categories_status'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->categories_uuid)) {
                $model->categories_uuid = (string) Str::uuid();
            }
        });
    }
}
