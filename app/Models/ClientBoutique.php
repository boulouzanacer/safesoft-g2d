<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientBoutique extends Model
{
    use HasFactory;

    protected $table = 'client_boutiques';

    protected $fillable = [
        'global_client_id',
        'fournisseur_id',
        'fournisseur_client_id',
    ];

    public function globalClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'global_client_id', 'id');
    }

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id', 'id');
    }

    public function fournisseurClient(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'fournisseur_client_id', 'id');
    }
}
