<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOperationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'compte_id' => ['required', 'uuid', 'exists:comptes,id'],
            'type' => ['required', 'in:depot,retrait,transfert,scan'],
            'montant' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
            'destination_compte_id' => ['nullable', 'uuid', 'exists:comptes,id'],
        ];
    }
}
