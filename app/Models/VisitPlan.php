<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitPlan extends Model
{
    protected $table = 'visit_plans';

    protected $fillable = [
        'client_id',
        'id_frs',
        'frequency_type',
        'interval_value',
        'month_occurrence',
        'start_date',
        'end_date',
        'label',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class, 'id_frs', 'id');
    }

    public function days(): HasMany
    {
        return $this->hasMany(VisitPlanDay::class, 'visit_plan_id', 'id');
    }

    public function dailyVisits(): HasMany
    {
        return $this->hasMany(VisitDaily::class, 'visit_plan_id', 'id');
    }
}
