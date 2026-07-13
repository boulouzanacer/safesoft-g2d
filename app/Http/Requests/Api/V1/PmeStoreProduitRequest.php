<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PmeStoreProduitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference' => ['required', 'string', 'max:100'],
            'designation' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'prix' => ['nullable', 'numeric', 'min:0'],
            'pv_1' => ['nullable', 'numeric', 'min:0'],
            'pv_2' => ['nullable', 'numeric', 'min:0'],
            'pv_3' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'categorie' => ['required', 'string', 'max:100'],
            'abonne_only' => ['nullable', 'boolean'],
            'actif' => ['nullable', 'boolean'],
            'enable_tier_pricing' => ['nullable', 'boolean'],
            'quantity_prices' => ['nullable', 'array', 'min:1'],
            'quantity_prices.*.quantity_min' => ['required_with:quantity_prices', 'integer', 'min:1'],
            'quantity_prices.*.quantity_max' => ['nullable', 'integer', 'min:1'],
            'quantity_prices.*.price' => ['required_with:quantity_prices', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $hasPrix = $this->filled('prix');
            $hasPv1 = $this->filled('pv_1');

            if (! $hasPrix && ! $hasPv1) {
                $validator->errors()->add('pv_1', 'pv_1 ou prix est requis.');
            }

            $enableTierPricing = (int) $this->input('enable_tier_pricing', 0) === 1;
            $tiers = $this->input('quantity_prices', []);

            if (! $enableTierPricing) {
                return;
            }

            if (! is_array($tiers) || count($tiers) === 0) {
                $validator->errors()->add('quantity_prices', 'Ajoutez au moins un palier.');

                return;
            }

            $normalized = [];
            foreach ($tiers as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $min = (int) ($row['quantity_min'] ?? 0);
                $maxRaw = $row['quantity_max'] ?? null;
                $max = ($maxRaw === null || $maxRaw === '') ? null : (int) $maxRaw;

                if ($max !== null && $max < $min) {
                    $validator->errors()->add("quantity_prices.{$index}.quantity_max", 'La quantité max doit être >= à la quantité min.');
                }

                $normalized[] = [
                    'quantity_min' => $min,
                    'quantity_max' => $max,
                ];
            }

            usort($normalized, fn ($a, $b) => $a['quantity_min'] <=> $b['quantity_min']);

            $previousMax = null;
            foreach ($normalized as $index => $tier) {
                if ($index === 0) {
                    $previousMax = $tier['quantity_max'];

                    continue;
                }

                if ($previousMax === null) {
                    $validator->errors()->add('quantity_prices', 'Aucun palier ne peut suivre un palier sans quantité max.');

                    return;
                }

                if ($tier['quantity_min'] <= $previousMax) {
                    $validator->errors()->add('quantity_prices', 'Chevauchement détecté entre paliers.');

                    return;
                }

                $previousMax = $tier['quantity_max'];
            }
        });
    }
}
