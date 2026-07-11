<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BoutiqueCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function fournisseurs(): HasMany
    {
        return $this->hasMany(Fournisseur::class, 'boutique_category_id', 'id');
    }
}
