<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

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
            'produits.*.prix' => ['required', 'numeric'],
            'produits.*.stock' => ['required', 'integer', 'min:0'],
            'produits.*.categorie' => ['required', 'string', 'max:100'],
        ];
    }
}

