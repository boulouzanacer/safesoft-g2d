<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitDaily extends Model
{
    protected $table = 'visit_daily';

    protected $fillable = [
        'visit_date',
        'client_id',
        'id_frs',
        'visit_plan_id',
        'status',
        'source',
        'notes',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class, 'id_frs', 'id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(VisitPlan::class, 'visit_plan_id', 'id');
    }
}
