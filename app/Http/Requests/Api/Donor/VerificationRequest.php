<?php

namespace App\Http\Requests\Api\Donor;

use App\Enums\DonorVerificationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class VerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $donor = Auth::guard('donor')->user();

        // Can only submit if not already verified or pending
        return $donor &&
            ! $donor->is_verification_locked &&
            $donor->verification_status !== DonorVerificationStatus::VERIFIED &&
            $donor->verification_status !== DonorVerificationStatus::PENDING;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // 10MB max
            'id_type' => ['required', 'string', 'in:passport,national_id,driving_license'],
            'additional_documents' => ['nullable', 'array', 'max:5'],
            'additional_documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:1000'],
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
            'id_document.required' => __('ID document is required for verification.'),
            'id_document.mimes' => __('ID document must be a PDF, JPG, or PNG file.'),
            'id_document.max' => __('ID document size cannot exceed :max KB.'),
            'id_type.required' => __('Please select the type of ID document.'),
            'id_type.in' => __('Invalid ID document type selected.'),
            'additional_documents.max' => __('You can upload a maximum of :max additional documents.'),
            'additional_documents.*.mimes' => __('Additional documents must be PDF, JPG, or PNG files.'),
            'additional_documents.*.max' => __('Each additional document cannot exceed :max KB.'),
        ];
    }
}




