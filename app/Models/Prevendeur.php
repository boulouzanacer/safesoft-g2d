<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prevendeur extends Model
{
    protected $table = 'prevendeurs';

    protected $fillable = [
        'id_frs',
        'nom',
        'telephone',
        'email',
        'notes',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class, 'id_frs', 'id');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'prevendeur_id', 'id');
    }

    public function visitPlans(): HasMany
    {
        return $this->hasMany(VisitPlan::class, 'prevendeur_id', 'id');
    }

    public function dailyVisits(): HasMany
    {
        return $this->hasMany(VisitDaily::class, 'prevendeur_id', 'id');
    }
}
