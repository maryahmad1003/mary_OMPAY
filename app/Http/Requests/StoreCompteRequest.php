<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'solde' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:actif,suspendu'],
            'type' => ['nullable', 'in:client,marchand'],
            'code_marchand' => ['nullable', 'string', 'unique:comptes,code_marchand'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'L\'identifiant de l\'utilisateur est requis.',
            'user_id.uuid' => 'L\'identifiant de l\'utilisateur doit être un UUID valide.',
            'user_id.exists' => 'L\'utilisateur spécifié n\'existe pas.',
            'solde.numeric' => 'Le solde doit être un nombre.',
            'solde.min' => 'Le solde ne peut pas être négatif.',
            'status.in' => 'Le statut doit être soit actif soit suspendu.',
        ];
    }
}
