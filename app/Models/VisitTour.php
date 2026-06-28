<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitTour extends Model
{
    protected $table = 'visit_tours';

    protected $fillable = [
        'id_frs',
        'prevendeur_id',
        'tour_date',
        'status',
        'clients_count',
    ];

    protected $casts = [
        'tour_date' => 'date',
        'clients_count' => 'integer',
    ];

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class, 'id_frs', 'id');
    }

    public function prevendeur(): BelongsTo
    {
        return $this->belongsTo(Prevendeur::class, 'prevendeur_id', 'id');
    }
}
