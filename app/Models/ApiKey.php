<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    public const TYPE_ADMINISTRATION = 'administration';
    public const TYPE_CREATE_FOURNISSEUR = 'create_fournisseur';

    protected $fillable = [
        'type',
        'api_key',
        'actif',
        'last_used_at',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public static function typeOptions(): array
    {
        return [
            self::TYPE_ADMINISTRATION => 'Administration',
            self::TYPE_CREATE_FOURNISSEUR => 'Create boutique',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeOptions()[$this->type] ?? $this->type;
    }

    public function getMaskedKeyAttribute(): string
    {
        $value = (string) $this->api_key;
        if (strlen($value) <= 12) {
            return $value;
        }

        return substr($value, 0, 6).'...'.substr($value, -6);
    }
}
