<?php

namespace App\Http\Requests\Api\Organization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PayoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $organization = Auth::guard('organization')->user();

        return $organization && $organization->hasAvailableBalance();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string', 'in:bank_transfer,mobile_money'],
            'bank_name' => ['required_if:payment_method,bank_transfer', 'nullable', 'string', 'max:255'],
            'account_number' => ['required_if:payment_method,bank_transfer', 'nullable', 'string', 'max:50'],
            'account_holder_name' => ['required_if:payment_method,bank_transfer', 'nullable', 'string', 'max:255'],
            'iban' => ['nullable', 'string', 'max:50'],
            'swift_code' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $organization = Auth::guard('organization')->user();
            $amount = $this->input('amount');

            // Check if organization can withdraw this amount
            if ($organization && ! $organization->canWithdraw($amount)) {
                $validator->errors()->add('amount', __('Insufficient balance. Available balance: :balance', [
                    'balance' => number_format($organization->available_balance, 2),
                ]));
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
            'amount.required' => __('Payout amount is required.'),
            'amount.min' => __('Payout amount must be at least :min.'),
            'payment_method.required' => __('Please select a payment method.'),
            'payment_method.in' => __('Invalid payment method selected.'),
            'bank_name.required_if' => __('Bank name is required for bank transfers.'),
            'account_number.required_if' => __('Account number is required for bank transfers.'),
            'account_holder_name.required_if' => __('Account holder name is required for bank transfers.'),
        ];
    }
}




