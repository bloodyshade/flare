<?php

namespace App\Game\Adventures\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdventureEmbarkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'attack_type'   => 'string|required',
            'over_flow_set' => 'nullable|integer|exists:inventory_sets,id',
        ];
    }

    public function messages() {
        return [
            'attack_type.required' => 'Error. Invalid Input.',
            'attack_type.string'   => 'Error. Invalid Input.',
        ];
    }
}
