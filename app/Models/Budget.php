<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Budget extends Model
{
    protected $table = 'budgets';
    protected $primaryKey = 'budgets_id';
    public $incrementing = true;
    protected $keyType = 'int';
    const CREATED_AT = 'budgets_create_date';
    const UPDATED_AT = 'budgets_update_date';

    protected $fillable = [
        'budgets_uuid',
        'budgets_categories_uuid',
        'budgets_period_year',
        'budgets_total_budget',
        'budgets_used_budget',
        'budgets_create_by',
        'budgets_update_by',
        'budgets_status'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->budgets_uuid)) {
                $model->budgets_uuid = (string) Str::uuid();
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'budgets_categories_uuid', 'categories_uuid');
    }
}
