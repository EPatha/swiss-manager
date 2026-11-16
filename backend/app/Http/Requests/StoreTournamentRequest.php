<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTournamentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'slug' => ['required', 'string', 'max:180', 'alpha_dash', 'unique:tournaments,slug'],
            'timezone' => ['required', 'timezone'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'round_count' => ['required', 'integer', 'min:1', 'max:99'],
            'venue' => ['nullable', 'string', 'max:255'],
            'pairing_system' => ['required', 'in:fide_dutch'],
            'ruleset_version' => ['required', 'string', 'max:80'],
            'scoring_config' => ['required', 'array'],
            'tie_break_config' => ['required', 'array'],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }
}
