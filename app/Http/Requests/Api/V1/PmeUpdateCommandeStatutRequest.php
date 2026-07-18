<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PmeUpdateCommandeStatutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statut' => [
                'required',
                'string',
                Rule::in(['en_attente', 'expediee', 'livree', 'annulee']),
            ],
        ];
    }
}
