<?php

namespace App\Game\Core\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UseManyItemsValidation extends FormRequest
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
            'slot_ids' => 'required',
        ];
    }

    public function messages() {
        return [
            'slot_ids.required' => 'You must select some items to use.',
        ];
    }
}
