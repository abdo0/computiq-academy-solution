<?php

namespace App\Http\Requests\Api\Organization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UpdateEmailRequest extends FormRequest
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
        $organization = $this->user('organization');

        return [
            'new_email' => ['required', 'string', 'email', 'max:255', Rule::unique('organizations', 'email')->ignore($organization->id)],
            'current_password' => ['required', 'string'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $organization = $this->user('organization');

            if (! Hash::check($this->current_password, $organization->password)) {
                $validator->errors()->add('current_password', __('The current password is incorrect.'));
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'new_email.required' => __('New email address is required.'),
            'new_email.email' => __('Please enter a valid email address.'),
            'new_email.unique' => __('This email is already registered.'),
            'current_password.required' => __('Current password is required.'),
            'current_password.invalid' => __('The current password is incorrect.'),
        ];
    }
}
