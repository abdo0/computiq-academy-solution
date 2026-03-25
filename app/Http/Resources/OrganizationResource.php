<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class OrganizationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $verification = $this->verification;

        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'email' => $this->email,
            'phone' => $this->phone,
            'countryCode' => $this->country_code,
            'address' => $this->address,
            'locale' => $this->locale ?? 'ar',
            'logo' => $this->logo ? Storage::url($this->logo) : null,
            'status' => $this->status?->value,
            'isVerified' => $this->isVerified(),
            'verificationStatus' => $this->verification_status?->value,
            'verificationTier' => $this->verification_tier?->value,
            'verificationSubmittedAt' => $this->verification_submitted_at?->toIso8601String(),
            'verificationReviewedAt' => $this->verification_reviewed_at?->toIso8601String(),
            'isVerificationLocked' => (bool) $this->is_verification_locked,
            'verificationNotes' => $this->verification_notes,
            'canSubmitVerification' => ! $this->is_verification_locked &&
                $this->verification_status !== \App\Enums\OrganizationVerificationStatus::VERIFIED,
            'verification' => $verification ? [
                'registration_number' => $verification->registration_number,
                'tax_id' => $verification->tax_id,
                'contact_person_name' => $verification->contact_person_name,
                'contact_person_position' => $verification->contact_person_position,
                'contact_person_phone' => $verification->contact_person_phone,
                'contact_person_email' => $verification->contact_person_email,
                'country_id' => $verification->country_id,
                'state_id' => $verification->state_id,
                'city' => $verification->city,
                'address_line' => $verification->address_line,
                'postal_code' => $verification->postal_code,
                'additional_information' => $verification->additional_information,
                'documents' => [
                    'registration_certificate' => $verification->registration_certificate_path ? (Route::has('organization.verification.download') ? route('organization.verification.download', ['file' => urlencode($verification->registration_certificate_path)]) : null) : null,
                    'tax_certificate' => $verification->tax_certificate_path ? (Route::has('organization.verification.download') ? route('organization.verification.download', ['file' => urlencode($verification->tax_certificate_path)]) : null) : null,
                    'license_document' => $verification->license_document_path ? (Route::has('organization.verification.download') ? route('organization.verification.download', ['file' => urlencode($verification->license_document_path)]) : null) : null,
                    'bank_statement' => $verification->bank_statement_path ? (Route::has('organization.verification.download') ? route('organization.verification.download', ['file' => urlencode($verification->bank_statement_path)]) : null) : null,
                    'board_resolution' => $verification->board_resolution_path ? (Route::has('organization.verification.download') ? route('organization.verification.download', ['file' => urlencode($verification->board_resolution_path)]) : null) : null,
                ],
                'additional_documents' => $verification->additional_documents ? array_map(function ($doc) {
                    return Route::has('organization.verification.download') ? route('organization.verification.download', ['file' => urlencode($doc)]) : null;
                }, $verification->additional_documents) : [],
            ] : null,
            'totalReceived' => $this->total_received,
            'totalPaidOut' => $this->total_paid_out,
            'availableBalance' => $this->available_balance,
            'campaignsCount' => $this->whenCounted('campaigns'),
            'emailVerifiedAt' => $this->email_verified_at?->toIso8601String(),
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
