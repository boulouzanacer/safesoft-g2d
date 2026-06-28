<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class Fournisseur extends Authenticatable
{
    use HasFactory;
    use SoftDeletes;
    use Notifiable;

    protected $table = 'frs';

    protected $fillable = [
        'nom_frs',
        'email',
        'password',
        'telephone',
        'logo_path',
        'adresse',
        'id_wilaya',
        'id_commune',
        'latitude',
        'longitude',
        'token',
        'actif',
        'is_visible',
    ];

    protected $appends = [
        'logo_url',
    ];

    protected $hidden = [
        'password',
    ];

    public function getLogoUrlAttribute(): string
    {
        $raw = trim((string) ($this->logo_path ?? ''));
        if ($raw === '') {
            return '';
        }

        $lower = strtolower($raw);
        if (str_starts_with($lower, 'http://') || str_starts_with($lower, 'https://')) {
            return $raw;
        }

        if (str_starts_with($raw, '/')) {
            return url($raw);
        }

        return Storage::url($raw);
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->token)) {
                $model->token = (string) Str::uuid();
            }
        });
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'id_frs', 'id');
    }

    public function prevendeurs(): HasMany
    {
        return $this->hasMany(Prevendeur::class, 'id_frs', 'id');
    }

    public function produits(): HasMany
    {
        return $this->hasMany(Produit::class, 'id_frs', 'id');
    }

    public function cmd1(): HasMany
    {
        return $this->hasMany(Cmd1::class, 'id_frs', 'id');
    }

    public function visitTours(): HasMany
    {
        return $this->hasMany(VisitTour::class, 'id_frs', 'id');
    }
}
