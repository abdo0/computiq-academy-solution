<?php

namespace App\Http\Requests\Api\Donor;

use App\Enums\TransactionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RefundRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $donor = Auth::guard('donor')->user();
        $transaction = $this->route('transaction');

        // Check if the donor owns this transaction
        return $donor && $transaction->donation?->donor_id === $donor->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $transaction = $this->route('transaction');

            // Check if transaction is completed (only completed transactions can be refunded)
            if ($transaction->status !== TransactionStatus::COMPLETED) {
                $validator->errors()->add('transaction', __('Only completed transactions can be refunded.'));
                return;
            }

            // Check if refund was already requested
            if ($transaction->status === TransactionStatus::REFUND_REQUESTED) {
                $validator->errors()->add('transaction', __('A refund has already been requested for this transaction.'));
                return;
            }

            // Check if already refunded
            if ($transaction->status === TransactionStatus::REFUNDED) {
                $validator->errors()->add('transaction', __('This transaction has already been refunded.'));
                return;
            }

            // Check if refund request is within allowed time period
            $maxDays = (int) (settings('refund_max_days') ?? 0);
            
            if ($maxDays > 0) {
                // Get completion date - use updated_at when status changed to completed
                $completedAt = $transaction->updated_at;
                
                // Calculate days since completion (positive number = days in the past)
                $daysSinceCompletion = $completedAt->diffInDays(now());

                if ($daysSinceCompletion > $maxDays) {
                    $validator->errors()->add(
                        'transaction',
                        __('Refund requests are only allowed within :days days of payment completion. This transaction was completed :days_ago days ago.', [
                            'days' => $maxDays,
                            'days_ago' => $daysSinceCompletion,
                        ])
                    );
                }
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
            'reason.required' => __('Please provide a reason for the refund request.'),
            'reason.max' => __('Reason cannot exceed :max characters.'),
        ];
    }
}


