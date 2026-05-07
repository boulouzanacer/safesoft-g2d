<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ProduitIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'frs_id' => ['nullable', 'integer'],
            'categorie' => ['nullable', 'string'],
            'search' => ['nullable', 'string'],
            'page' => ['nullable', 'integer'],
        ];
    }
}

