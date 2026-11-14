<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreVolunteerRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'volunteer_id' => ['required', 'exists:users,id'],
            'event_id' => ['required', 'exists:events,id'],
            'message' => ['nullable', 'string', 'max:1000'],
            'meeting_point' => ['nullable', 'string', 'max:255'],
            'meeting_time' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'volunteer_id.required' => 'O voluntário deve ser selecionado.',
            'volunteer_id.exists' => 'Voluntário não encontrado.',
            'event_id.required' => 'O evento deve ser selecionado.',
            'event_id.exists' => 'Evento não encontrado.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Erros ao solicitar acompanhamento.',
            'data' => null,
            'errors' => $validator->errors(),
        ], 422));
    }
}
