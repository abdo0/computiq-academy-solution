<?php

namespace App\Http\Requests\Employee;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('employee')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Personal Information
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'third_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'no_of_kids' => ['nullable', 'integer', 'min:0'],
            'religion' => ['nullable', 'string', 'max:50'],

            // Contact Information
            'phone' => ['required', 'string', 'max:20'],
            'home_email' => ['nullable', 'email', 'max:100'],
            'home_phone' => ['nullable', 'string', 'max:20'],
            'cell_phone' => ['nullable', 'string', 'max:20'],

            // Emergency Contact
            'emergency_contact_person' => ['nullable', 'string', 'max:100'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:50'],
            'emergency_contact' => ['nullable', 'string', 'max:20'],
            'emergency_home_phone' => ['nullable', 'string', 'max:20'],
            'alter_emergency_contact' => ['nullable', 'string', 'max:100'],
            'sos' => ['nullable', 'string', 'max:50'],

            // Address Information
            'present_address_state' => ['nullable', 'string', 'max:100'],
            'present_address_city' => ['nullable', 'string', 'max:100'],
            'present_address_post_code' => ['nullable', 'string', 'max:20'],
            'present_address_address' => ['nullable', 'string', 'max:500'],
            'permanent_address_state' => ['nullable', 'string', 'max:100'],
            'permanent_address_city' => ['nullable', 'string', 'max:100'],
            'permanent_address_post_code' => ['nullable', 'string', 'max:20'],
            'permanent_address_address' => ['nullable', 'string', 'max:500'],

            // Medical Information
            'blood_group' => ['nullable', 'string', 'max:10'],
            'health_condition' => ['nullable', 'string', 'max:100'],
            'disabilities_desc' => ['nullable', 'string', 'max:150'],

            // Security
            'current_password' => ['nullable', 'required_with:password', 'current_password:employee'],
            'password' => ['nullable', 'confirmed', Password::default()],
            'password_confirmation' => ['nullable', 'required_with:password'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => __('First Name'),
            'middle_name' => __('Middle Name'),
            'third_name' => __('Third Name'),
            'last_name' => __('Last Name'),
            'date_of_birth' => __('Date of Birth'),
            'no_of_kids' => __('Number of Children'),
            'religion' => __('Religion'),
            'phone' => __('Phone'),
            'home_email' => __('Home Email'),
            'home_phone' => __('Home Phone'),
            'cell_phone' => __('Cell Phone'),
            'emergency_contact_person' => __('Emergency Contact Person'),
            'emergency_contact_relationship' => __('Relationship'),
            'emergency_contact' => __('Emergency Contact Number'),
            'emergency_home_phone' => __('Emergency Home Phone'),
            'alter_emergency_contact' => __('Alternative Emergency Contact'),
            'sos' => __('SOS Number'),
            'present_address_state' => __('Present Address State'),
            'present_address_city' => __('Present Address City'),
            'present_address_post_code' => __('Present Address Post Code'),
            'present_address_address' => __('Present Address'),
            'permanent_address_state' => __('Permanent Address State'),
            'permanent_address_city' => __('Permanent Address City'),
            'permanent_address_post_code' => __('Permanent Address Post Code'),
            'permanent_address_address' => __('Permanent Address'),
            'blood_group' => __('Blood Group'),
            'health_condition' => __('Health Condition'),
            'disabilities_desc' => __('Disabilities Description'),
            'current_password' => __('Current Password'),
            'password' => __('New Password'),
            'password_confirmation' => __('Confirm New Password'),
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.current_password' => __('The current password is incorrect.'),
            'password.confirmed' => __('The password confirmation does not match.'),
            'date_of_birth.before' => __('The date of birth must be before today.'),
            'no_of_kids.min' => __('The number of children must be at least 0.'),
        ];
    }
}
