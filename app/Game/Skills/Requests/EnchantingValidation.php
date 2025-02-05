<?php

namespace App\Game\Skills\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnchantingValidation extends FormRequest
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
            'slot_id'   => 'required',
            'affix_ids' => 'required',
            'cost'      => 'required',
        ];
    }

    public function messages() {
        return [
            'slot_id.required'    => 'What item are you trying to enchant?',
            'affix_ids.required'  => 'What enchantment(s) are you trying to attach?',
            'cost.required'       => 'Error. Invalid Input.',
        ];
    }
}
