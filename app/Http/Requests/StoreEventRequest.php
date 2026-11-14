<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'size:2'],
            'event_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'ticket_information' => ['nullable', 'string', 'max:255'],
            'banner_url' => ['nullable', 'string', 'max:255'],
            // Accessibility options
            'ramps' => ['boolean'],
            'accessible_restrooms' => ['boolean'],
            'reserved_spaces' => ['boolean'],
            'wheelchair_accessible' => ['boolean'],
            'sign_language_interpreter' => ['boolean'],
            'audio_description' => ['boolean'],
            'subtitles' => ['boolean'],
            'tactile_paving' => ['boolean'],
            'specialized_assistance' => ['boolean'],
            'additional_notes' => ['nullable', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Erros na criação do evento.',
            'data' => null,
            'errors' => $validator->errors(),
        ], 422));
    }
}
