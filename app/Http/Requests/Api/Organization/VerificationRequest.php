<?php

namespace App\Http\Requests\Api\Organization;

use App\Enums\OrganizationVerificationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class VerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $organization = Auth::guard('organization')->user();

        // Can submit/edit if not locked and not already verified
        // Allow editing even when status is pending
        return $organization &&
            ! $organization->is_verification_locked &&
            $organization->verification_status !== OrganizationVerificationStatus::VERIFIED;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Organization information
            'registration_number' => ['required', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:100'],

            // Contact person information
            'contact_person_name' => ['required', 'string', 'max:255'],
            'contact_person_position' => ['required', 'string', 'max:255'],
            'contact_person_phone' => ['required', 'string', 'max:50'],
            'contact_person_email' => ['required', 'email', 'max:255'],

            // Location information
            'country_id' => ['required', 'exists:countries,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'city' => ['required', 'string', 'max:100'],
            'address_line' => ['required', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],

            // Organization documents
            // Make registration_certificate optional if editing (when status is pending)
            'registration_certificate' => [
                function ($attribute, $value, $fail) {
                    $organization = Auth::guard('organization')->user();
                    $existingVerification = \App\Models\OrganizationVerification::where('organization_id', $organization->id)->first();

                    // If no existing verification or no existing document, registration_certificate is required
                    if (! $existingVerification || ! $existingVerification->registration_certificate_path) {
                        if (! $value) {
                            $fail(__('Registration certificate is required.'));
                        }
                    }

                    // If file is provided, validate it
                    if ($value) {
                        $extension = strtolower($value->getClientOriginalExtension());
                        $mimeType = $value->getMimeType();
                        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                        $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];

                        if (! in_array($extension, $allowedExtensions) && ! in_array($mimeType, $allowedMimeTypes)) {
                            $fail(__('Registration certificate must be a PDF, JPG, or PNG file.'));
                        }
                    }
                },
                'nullable',
                'file',
                'max:10240',
            ],
            'tax_certificate' => [
                'nullable',
                'file',
                function ($attribute, $value, $fail) {
                    if (! $value) {
                        return;
                    }
                    $extension = strtolower($value->getClientOriginalExtension());
                    $mimeType = $value->getMimeType();
                    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                    $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];

                    if (! in_array($extension, $allowedExtensions) && ! in_array($mimeType, $allowedMimeTypes)) {
                        $fail(__('Tax certificate must be a PDF, JPG, or PNG file.'));
                    }
                },
                'max:10240',
            ],
            'license_document' => [
                'nullable',
                'file',
                function ($attribute, $value, $fail) {
                    if (! $value) {
                        return;
                    }
                    $extension = strtolower($value->getClientOriginalExtension());
                    $mimeType = $value->getMimeType();
                    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                    $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];

                    if (! in_array($extension, $allowedExtensions) && ! in_array($mimeType, $allowedMimeTypes)) {
                        $fail(__('License document must be a PDF, JPG, or PNG file.'));
                    }
                },
                'max:10240',
            ],
            'bank_statement' => [
                'nullable',
                'file',
                function ($attribute, $value, $fail) {
                    if (! $value) {
                        return;
                    }
                    $extension = strtolower($value->getClientOriginalExtension());
                    $mimeType = $value->getMimeType();
                    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                    $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];

                    if (! in_array($extension, $allowedExtensions) && ! in_array($mimeType, $allowedMimeTypes)) {
                        $fail(__('Bank statement must be a PDF, JPG, or PNG file.'));
                    }
                },
                'max:10240',
            ],
            'board_resolution' => [
                'nullable',
                'file',
                function ($attribute, $value, $fail) {
                    if (! $value) {
                        return;
                    }
                    $extension = strtolower($value->getClientOriginalExtension());
                    $mimeType = $value->getMimeType();
                    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                    $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];

                    if (! in_array($extension, $allowedExtensions) && ! in_array($mimeType, $allowedMimeTypes)) {
                        $fail(__('Board resolution must be a PDF, JPG, or PNG file.'));
                    }
                },
                'max:10240',
            ],

            // Additional documents
            'additional_documents' => ['nullable', 'array', 'max:10'],
            'additional_documents.*' => [
                'file',
                function ($attribute, $value, $fail) {
                    if (! $value) {
                        return;
                    }
                    $extension = strtolower($value->getClientOriginalExtension());
                    $mimeType = $value->getMimeType();
                    $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                    $allowedMimeTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];

                    if (! in_array($extension, $allowedExtensions) && ! in_array($mimeType, $allowedMimeTypes)) {
                        $fail(__('Additional documents must be PDF, JPG, or PNG files.'));
                    }
                },
                'max:10240',
            ],

            // Additional information
            'additional_information' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'registration_certificate.required' => __('Organization registration certificate is required.'),
            'registration_certificate.mimes' => __('Registration certificate must be a PDF, JPG, or PNG file.'),
            'registration_certificate.max' => __('Registration certificate size cannot exceed :max KB.'),
            'tax_certificate.mimes' => __('Tax certificate must be a PDF, JPG, or PNG file.'),
            'license_document.mimes' => __('License document must be a PDF, JPG, or PNG file.'),
            'bank_statement.mimes' => __('Bank statement must be a PDF, JPG, or PNG file.'),
            'additional_documents.max' => __('You can upload a maximum of :max additional documents.'),
            'additional_documents.*.mimes' => __('Additional documents must be PDF, JPG, or PNG files.'),
            'additional_documents.*.max' => __('Each additional document cannot exceed :max KB.'),
        ];
    }
}
