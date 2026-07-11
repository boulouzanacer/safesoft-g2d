<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        'boutique_category_id',
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
        'expires_at',
        'is_visible',
    ];

    protected $appends = [
        'logo_url',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'expires_at' => 'date',
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

        static::deleted(function (self $model): void {
            if ($model->isForceDeleting()) {
                return;
            }

            $archivedEmail = self::archivedEmailValue((int) $model->id, $model->email);

            if ((string) $model->email === $archivedEmail) {
                return;
            }

            DB::table('frs')
                ->where('id', $model->id)
                ->update([
                    'email' => $archivedEmail,
                    'updated_at' => now(),
                ]);

            $model->forceFill(['email' => $archivedEmail]);
        });
    }

    public static function archivedEmailValue(int $id, ?string $originalEmail = null): string
    {
        $domain = 'deleted.local';
        $base = 'deleted+'.$id.'+'.now()->timestamp;

        if ($originalEmail) {
            $hash = substr(sha1(mb_strtolower(trim($originalEmail))), 0, 10);
            $base .= '+'.$hash;
        }

        return $base.'@'.$domain;
    }

    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return $this->expires_at->startOfDay()->lt(Carbon::today()->startOfDay());
    }

    public function syncExpirationStatus(): bool
    {
        if ($this->isExpired() && (int) $this->actif === 1) {
            $this->forceFill(['actif' => 0])->save();

            return true;
        }

        return false;
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'id_frs', 'id');
    }

    public function boutiqueCategory(): BelongsTo
    {
        return $this->belongsTo(BoutiqueCategory::class, 'boutique_category_id', 'id');
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
