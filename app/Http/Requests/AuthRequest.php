<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email'      => 'required|email|unique:users',
            'password'   => 'required|string|min:3',
            'first_name' => 'required|string|min:2',
            'last_name'  => 'required|string',
        ];
    }
}
