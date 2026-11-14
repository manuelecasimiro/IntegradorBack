<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'request_id' => ['required', 'exists:volunteer_requests,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'request_id.required' => 'A solicitação concluída é necessária.',
            'rating.required' => 'A nota de 1 a 5 estrelas é obrigatória.',
            'rating.min' => 'A nota mínima é 1.',
            'rating.max' => 'A nota máxima é 5.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Erro ao registrar avaliação.',
            'data' => null,
            'errors' => $validator->errors(),
        ], 422));
    }
}
