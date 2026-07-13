<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PmeSyncProduitsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'produits' => ['required', 'array', 'min:1'],
            'produits.*.reference' => ['required', 'string', 'max:100'],
            'produits.*.designation' => ['required', 'string', 'max:255'],
            'produits.*.description' => ['nullable', 'string'],
            'produits.*.prix' => ['nullable', 'numeric', 'min:0'],
            'produits.*.pv_1' => ['nullable', 'numeric', 'min:0'],
            'produits.*.pv_2' => ['nullable', 'numeric', 'min:0'],
            'produits.*.pv_3' => ['nullable', 'numeric', 'min:0'],
            'produits.*.stock' => ['required', 'integer', 'min:0'],
            'produits.*.categorie' => ['required', 'string', 'max:100'],
            'produits.*.abonne_only' => ['nullable', 'boolean'],
            'produits.*.actif' => ['nullable', 'boolean'],
            'produits.*.enable_tier_pricing' => ['nullable', 'boolean'],
            'produits.*.quantity_prices' => ['nullable', 'array', 'min:1'],
            'produits.*.quantity_prices.*.quantity_min' => ['required_with:produits.*.quantity_prices', 'integer', 'min:1'],
            'produits.*.quantity_prices.*.quantity_max' => ['nullable', 'integer', 'min:1'],
            'produits.*.quantity_prices.*.price' => ['required_with:produits.*.quantity_prices', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $items = $this->input('produits', []);
            if (! is_array($items)) {
                return;
            }

            foreach ($items as $i => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $hasPrix = array_key_exists('prix', $item) && $item['prix'] !== null && $item['prix'] !== '';
                $hasPv1 = array_key_exists('pv_1', $item) && $item['pv_1'] !== null && $item['pv_1'] !== '';

                if (! $hasPrix && ! $hasPv1) {
                    $v->errors()->add("produits.{$i}.pv_1", 'pv_1 ou prix est requis.');
                }

                $enableTierPricing = (int) ($item['enable_tier_pricing'] ?? 0) === 1;
                $tiers = $item['quantity_prices'] ?? [];

                if (! $enableTierPricing) {
                    continue;
                }

                if (! is_array($tiers) || count($tiers) === 0) {
                    $v->errors()->add("produits.{$i}.quantity_prices", 'Ajoutez au moins un palier.');

                    continue;
                }

                $normalized = [];
                foreach ($tiers as $tierIndex => $tier) {
                    if (! is_array($tier)) {
                        continue;
                    }

                    $min = (int) ($tier['quantity_min'] ?? 0);
                    $maxRaw = $tier['quantity_max'] ?? null;
                    $max = ($maxRaw === null || $maxRaw === '') ? null : (int) $maxRaw;

                    if ($max !== null && $max < $min) {
                        $v->errors()->add("produits.{$i}.quantity_prices.{$tierIndex}.quantity_max", 'La quantité max doit être >= à la quantité min.');
                    }

                    $normalized[] = [
                        'quantity_min' => $min,
                        'quantity_max' => $max,
                    ];
                }

                usort($normalized, fn ($a, $b) => $a['quantity_min'] <=> $b['quantity_min']);

                $previousMax = null;
                foreach ($normalized as $normalizedIndex => $normalizedTier) {
                    if ($normalizedIndex === 0) {
                        $previousMax = $normalizedTier['quantity_max'];

                        continue;
                    }

                    if ($previousMax === null) {
                        $v->errors()->add("produits.{$i}.quantity_prices", 'Aucun palier ne peut suivre un palier sans quantité max.');

                        break;
                    }

                    if ($normalizedTier['quantity_min'] <= $previousMax) {
                        $v->errors()->add("produits.{$i}.quantity_prices", 'Chevauchement détecté entre paliers.');

                        break;
                    }

                    $previousMax = $normalizedTier['quantity_max'];
                }
            }
        });
    }
}
