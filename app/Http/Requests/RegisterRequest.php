<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:client,volunteer,admin,organizer'],
            'admin_code' => ['nullable', 'string'],
            'cpf' => ['nullable', 'string', 'max:14', 'unique:users,cpf'],
            'phone' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            // Client specific fields
            'accessibility_needs' => ['nullable', 'string'],
            'bio' => ['nullable', 'string'],
            // Volunteer specific fields
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'experience' => ['nullable', 'string'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome completo é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.unique' => 'Este e-mail já está em uso.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve conter no mínimo 6 caracteres.',
            'role.required' => 'Selecione o tipo de perfil (Cliente ou Voluntário).',
            'role.in' => 'Tipo de perfil inválido.',
            'cpf.unique' => 'Este CPF já está cadastrado no sistema.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Erros de validação encontrados.',
            'data' => null,
            'errors' => $validator->errors(),
        ], 422));
    }
}
