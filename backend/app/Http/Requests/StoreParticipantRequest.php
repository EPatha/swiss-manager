<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:200'],
            'fide_id' => ['nullable', 'string', 'max:20', 'regex:/^[0-9]{4,14}$/'],
            'federation_code' => ['nullable', 'string', 'size:3', 'alpha'],
            'title' => ['nullable', 'in:GM,IM,FM,CM,WGM,WIM,WFM,WCM,none'],
            'standard_rating' => ['nullable', 'integer', 'min:0', 'max:4000'],
            'rapid_rating' => ['nullable', 'integer', 'min:0', 'max:4000'],
            'blitz_rating' => ['nullable', 'integer', 'min:0', 'max:4000'],
            'birth_date' => ['nullable', 'date_format:Y-m-d'],
            'gender_code' => ['nullable', 'string', 'max:20'],
            'club' => ['nullable', 'string', 'max:160'],
            'contact_information' => ['nullable', 'string', 'max:255'],
        ];
    }
}
