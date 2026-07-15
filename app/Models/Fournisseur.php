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

    public const DEFAULT_STOREFRONT_THEME = 'azure_modern';

    protected $table = 'frs';

    protected $fillable = [
        'nom_frs',
        'storefront_slug',
        'storefront_theme',
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
        'storefront_url',
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

    public function getStorefrontUrlAttribute(): string
    {
        $slug = trim((string) ($this->storefront_slug ?? ''));

        return $slug === '' ? '' : route('storefront.boutique', ['slug' => $slug]);
    }

    public static function storefrontThemeOptions(): array
    {
        return [
            'azure_modern' => [
                'name' => 'Azure Modern',
                'tagline' => 'Clair, premium et polyvalent',
                'description' => 'Un style lumineux bleu glacier ideal pour electronique, services et boutiques generalistes.',
                'preview' => [
                    'from' => '#1D4ED8',
                    'to' => '#0EA5E9',
                    'accent' => '#DBEAFE',
                ],
                'vars' => [
                    '--store-primary' => '#1D4ED8',
                    '--store-primary-dark' => '#1E3A8A',
                    '--store-bg' => '#F4F8FF',
                    '--store-card' => '#FFFFFF',
                    '--store-card-soft' => '#EFF6FF',
                    '--store-text' => '#0F172A',
                    '--store-muted' => '#475569',
                    '--store-border' => '#BFDBFE',
                    '--store-accent' => '#DBEAFE',
                    '--store-accent-text' => '#1D4ED8',
                    '--store-hero-from' => '#1D4ED8',
                    '--store-hero-to' => '#0EA5E9',
                    '--store-button-text' => '#FFFFFF',
                    '--store-shadow' => '0 22px 45px rgba(37, 99, 235, 0.15)',
                    '--store-radius-xl' => '1rem',
                    '--store-radius-2xl' => '1.5rem',
                ],
            ],
            'emerald_bloom' => [
                'name' => 'Emerald Bloom',
                'tagline' => 'Frais, naturel et rassurant',
                'description' => 'Un univers vert premium bien adapte aux cosmétiques, bio, sante et produits naturels.',
                'preview' => [
                    'from' => '#059669',
                    'to' => '#34D399',
                    'accent' => '#D1FAE5',
                ],
                'vars' => [
                    '--store-primary' => '#059669',
                    '--store-primary-dark' => '#065F46',
                    '--store-bg' => '#F2FBF7',
                    '--store-card' => '#FFFFFF',
                    '--store-card-soft' => '#ECFDF5',
                    '--store-text' => '#0F172A',
                    '--store-muted' => '#4B5563',
                    '--store-border' => '#A7F3D0',
                    '--store-accent' => '#D1FAE5',
                    '--store-accent-text' => '#047857',
                    '--store-hero-from' => '#047857',
                    '--store-hero-to' => '#34D399',
                    '--store-button-text' => '#FFFFFF',
                    '--store-shadow' => '0 22px 48px rgba(5, 150, 105, 0.16)',
                    '--store-radius-xl' => '1rem',
                    '--store-radius-2xl' => '1.5rem',
                ],
            ],
            'sunset_pop' => [
                'name' => 'Sunset Pop',
                'tagline' => 'Chaleureux, dynamique et vendeur',
                'description' => 'Des tons corail et ambre qui donnent beaucoup d energie aux boutiques mode et tendances.',
                'preview' => [
                    'from' => '#EA580C',
                    'to' => '#FB7185',
                    'accent' => '#FFE4E6',
                ],
                'vars' => [
                    '--store-primary' => '#EA580C',
                    '--store-primary-dark' => '#9A3412',
                    '--store-bg' => '#FFF7ED',
                    '--store-card' => '#FFFFFF',
                    '--store-card-soft' => '#FFF1F2',
                    '--store-text' => '#431407',
                    '--store-muted' => '#7C2D12',
                    '--store-border' => '#FDBA74',
                    '--store-accent' => '#FFE4E6',
                    '--store-accent-text' => '#BE123C',
                    '--store-hero-from' => '#EA580C',
                    '--store-hero-to' => '#FB7185',
                    '--store-button-text' => '#FFFFFF',
                    '--store-shadow' => '0 24px 50px rgba(234, 88, 12, 0.16)',
                    '--store-radius-xl' => '1rem',
                    '--store-radius-2xl' => '1.5rem',
                ],
            ],
            'violet_luxe' => [
                'name' => 'Violet Luxe',
                'tagline' => 'Elegant, fort et distinctif',
                'description' => 'Une identité violette haut de gamme pour boutiques premium, cadeaux et univers luxe.',
                'preview' => [
                    'from' => '#7C3AED',
                    'to' => '#A855F7',
                    'accent' => '#EDE9FE',
                ],
                'vars' => [
                    '--store-primary' => '#7C3AED',
                    '--store-primary-dark' => '#4C1D95',
                    '--store-bg' => '#F8F5FF',
                    '--store-card' => '#FFFFFF',
                    '--store-card-soft' => '#F5F3FF',
                    '--store-text' => '#1F1635',
                    '--store-muted' => '#5B5570',
                    '--store-border' => '#DDD6FE',
                    '--store-accent' => '#EDE9FE',
                    '--store-accent-text' => '#6D28D9',
                    '--store-hero-from' => '#6D28D9',
                    '--store-hero-to' => '#A855F7',
                    '--store-button-text' => '#FFFFFF',
                    '--store-shadow' => '0 24px 52px rgba(124, 58, 237, 0.18)',
                    '--store-radius-xl' => '1.15rem',
                    '--store-radius-2xl' => '1.75rem',
                ],
            ],
            'rose_boutique' => [
                'name' => 'Rose Boutique',
                'tagline' => 'Doux, tendance et feminin',
                'description' => 'Un rose poudré moderne pour beaute, accessoires, cadeaux et univers lifestyle.',
                'preview' => [
                    'from' => '#DB2777',
                    'to' => '#FB7185',
                    'accent' => '#FCE7F3',
                ],
                'vars' => [
                    '--store-primary' => '#DB2777',
                    '--store-primary-dark' => '#9D174D',
                    '--store-bg' => '#FFF7FB',
                    '--store-card' => '#FFFFFF',
                    '--store-card-soft' => '#FDF2F8',
                    '--store-text' => '#3F1732',
                    '--store-muted' => '#6B3358',
                    '--store-border' => '#FBCFE8',
                    '--store-accent' => '#FCE7F3',
                    '--store-accent-text' => '#BE185D',
                    '--store-hero-from' => '#DB2777',
                    '--store-hero-to' => '#FB7185',
                    '--store-button-text' => '#FFFFFF',
                    '--store-shadow' => '0 24px 50px rgba(219, 39, 119, 0.16)',
                    '--store-radius-xl' => '1rem',
                    '--store-radius-2xl' => '1.65rem',
                ],
            ],
            'graphite_pro' => [
                'name' => 'Graphite Pro',
                'tagline' => 'Sobre, technique et premium',
                'description' => 'Une ambiance graphite tres professionnelle pour auto, materiel, B2B et high-tech.',
                'preview' => [
                    'from' => '#111827',
                    'to' => '#334155',
                    'accent' => '#E2E8F0',
                ],
                'vars' => [
                    '--store-primary' => '#111827',
                    '--store-primary-dark' => '#020617',
                    '--store-bg' => '#F3F4F6',
                    '--store-card' => '#FFFFFF',
                    '--store-card-soft' => '#E5E7EB',
                    '--store-text' => '#0F172A',
                    '--store-muted' => '#475569',
                    '--store-border' => '#CBD5E1',
                    '--store-accent' => '#E2E8F0',
                    '--store-accent-text' => '#0F172A',
                    '--store-hero-from' => '#111827',
                    '--store-hero-to' => '#334155',
                    '--store-button-text' => '#FFFFFF',
                    '--store-shadow' => '0 22px 48px rgba(15, 23, 42, 0.18)',
                    '--store-radius-xl' => '0.95rem',
                    '--store-radius-2xl' => '1.35rem',
                ],
            ],
        ];
    }

    public static function normalizeStorefrontTheme(?string $theme): string
    {
        $normalized = trim((string) $theme);
        $options = self::storefrontThemeOptions();

        return array_key_exists($normalized, $options)
            ? $normalized
            : self::DEFAULT_STOREFRONT_THEME;
    }

    public function storefrontThemeKey(): string
    {
        return self::normalizeStorefrontTheme($this->storefront_theme);
    }

    public function storefrontThemeConfig(): array
    {
        $key = $this->storefrontThemeKey();
        $options = self::storefrontThemeOptions();

        return $options[$key] ?? $options[self::DEFAULT_STOREFRONT_THEME];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (empty($model->token)) {
                $model->token = (string) Str::uuid();
            }

            if (empty($model->storefront_slug)) {
                $model->storefront_slug = self::generateUniqueStorefrontSlug((string) $model->nom_frs);
            }

            $model->storefront_theme = self::normalizeStorefrontTheme($model->storefront_theme);
        });

        static::saving(function (self $model): void {
            if (empty($model->storefront_slug)) {
                $model->storefront_slug = self::generateUniqueStorefrontSlug((string) $model->nom_frs, $model->id ? (int) $model->id : null);
            }

            $model->storefront_theme = self::normalizeStorefrontTheme($model->storefront_theme);
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

    public static function generateUniqueStorefrontSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'boutique';
        }

        $slug = $base;
        $index = 2;

        while (
            self::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('storefront_slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$index;
            $index++;
        }

        return $slug;
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

    public function customDomains(): HasMany
    {
        return $this->hasMany(CustomDomain::class, 'fournisseur_id', 'id');
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
