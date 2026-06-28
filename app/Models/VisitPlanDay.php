<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitPlanDay extends Model
{
    protected $table = 'visit_plan_days';

    protected $fillable = [
        'visit_plan_id',
        'day_of_week',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(VisitPlan::class, 'visit_plan_id', 'id');
    }
}
