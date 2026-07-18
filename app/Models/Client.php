<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Client extends Authenticatable
{
    use HasFactory;
    use SoftDeletes;
    use Notifiable;
    use HasApiTokens;

    protected $table = 'client';

    protected $fillable = [
        'code_client',
        'nom',
        'email',
        'email_verified_at',
        'email_verification_code_hash',
        'email_verification_expires_at',
        'password',
        'telephone',
        'adresse',
        'id_wilaya',
        'id_commune',
        'type_client',
        'tarif',
        'achat_client',
        'versement_client',
        'solde_client',
        'id_frs',
        'prevendeur_id',
        'synced_pme',
        'actif',
    ];

    public static function normalizeFullName(?string $name): string
    {
        return trim((string) preg_replace('/\s+/', ' ', trim((string) $name)));
    }

    public function getDisplayNameAttribute(): string
    {
        return static::normalizeFullName((string) ($this->attributes['nom'] ?? ''));
    }

    public function getFirstNameAttribute(): string
    {
        $displayName = $this->display_name;
        if ($displayName === '') {
            return '';
        }

        return explode(' ', $displayName)[0] ?? '';
    }

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_verification_expires_at' => 'datetime',
        'achat_client' => 'float',
        'versement_client' => 'float',
        'solde_client' => 'float',
        'synced_pme' => 'integer',
        'prevendeur_id' => 'integer',
    ];

    public function scopeSimpleRoot(Builder $query): Builder
    {
        return $query
            ->where('type_client', 'simple')
            ->whereNull('id_frs');
    }

    public static function findSimpleByEmail(string $email): ?self
    {
        return static::query()
            ->simpleRoot()
            ->where('email', $email)
            ->orderByDesc('id')
            ->first();
    }

    public static function findForFournisseurByEmail(int $frsId, string $email): ?self
    {
        return static::query()
            ->where('id_frs', $frsId)
            ->where('email', $email)
            ->orderByRaw("CASE WHEN type_client = 'abonne' THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->first();
    }

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class, 'id_frs', 'id');
    }

    public function prevendeur(): BelongsTo
    {
        return $this->belongsTo(Prevendeur::class, 'prevendeur_id', 'id');
    }

    public function wilaya(): BelongsTo
    {
        return $this->belongsTo(Wilaya::class, 'id_wilaya', 'ID_WILAYA');
    }

    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'id_commune', 'ID_COMMUNE');
    }

    public function visitPlans(): HasMany
    {
        return $this->hasMany(VisitPlan::class, 'client_id', 'id');
    }
}
