<?php

namespace App\Http\Requests\V1;

use App\Exceptions\InputValidationException;
use App\Http\Requests\Request as FormRequest;
use Illuminate\Contracts\Validation\Validator;

class OrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'u_id'     => 'required',
            'order_id' => 'required'
        ];
    }

    /**
     * Throw the failed validation exception.
     *
     * @param  \Illuminate\Contracts\Validation\Validator $validator
     *
     * @return void
     *
     * @throws \App\Exceptions\InputValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new InputValidationException;
    }
}
