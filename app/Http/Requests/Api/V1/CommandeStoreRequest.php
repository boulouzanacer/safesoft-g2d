<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CommandeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_frs' => ['required', 'integer', 'exists:frs,id'],
            'adresse_livraison' => ['required', 'string'],
            'id_wilaya' => ['required', 'integer', 'exists:wilaya,ID_WILAYA'],
            'id_commune' => ['required', 'integer', 'exists:commune,ID_COMMUNE'],
            'notes' => ['nullable', 'string'],
            'panier' => ['required', 'array', 'min:1'],
            'panier.*.id_produit' => ['required', 'integer', 'exists:produit,id'],
            'panier.*.quantite' => ['required', 'integer', 'min:1'],
        ];
    }
}

