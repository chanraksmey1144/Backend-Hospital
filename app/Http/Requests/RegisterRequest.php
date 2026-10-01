<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id'            => 'nullable|string|max:24|unique:users,id',
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users,email',
            'password'      => 'required|string|min:8',
            'role'          => 'nullable|in:ADMIN,DOCTOR,NURSE,RECEPTIONIST,PHARMACIST,ACCOUNTANT,PATIENT',
            'department_id' => 'nullable|string|max:24',
            'avatar'        => 'nullable|string|max:255',
            'locale'        => 'nullable|in:en,km',
            'timezone'      => 'nullable|string|max:50',
        ];
    }
}
