<?php

namespace App\Http\Requests;

use App\Http\Requests\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->method()) {
            case 'GET':
            case 'POST':
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
            default:
                return [

                ];
        }
    }

    public function messages()
    {
        return [

        ];
    }
}
