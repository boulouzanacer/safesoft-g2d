<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cmd1 extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'cmd1';

    protected $fillable = [
        'id_client',
        'id_frs',
        'date_cmd',
        'statut',
        'montant_total',
        'adresse_livraison',
        'tele_shipping',
        'id_wilaya',
        'id_commune',
        'notes',
        'synced_pme',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'id_client', 'id');
    }

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class, 'id_frs', 'id');
    }

    public function wilaya(): BelongsTo
    {
        return $this->belongsTo(Wilaya::class, 'id_wilaya', 'ID_WILAYA');
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'id_commune', 'ID_COMMUNE');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(Cmd2::class, 'id_cmd', 'id');
    }
}
