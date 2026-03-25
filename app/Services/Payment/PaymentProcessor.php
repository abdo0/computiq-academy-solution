<?php

namespace App\Services\Payment;

use App\Enums\ActivityAction;
use App\Enums\DonationStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\DonorActivityLog;
use App\Models\PaymentGateway;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentProcessor
{
    public function __construct(
        protected PaymentCalculator $calculator
    ) {}

    /**
     * Process a donation payment
     */
    public function processDonation(
        float $amount,
        ?Campaign $campaign = null,
        PaymentGateway $gateway,
        ?Donor $donor = null,
        array $donorData = [],
        array $metadata = []
    ): array {
        return DB::transaction(function () use ($amount, $campaign, $gateway, $donor, $metadata) {
            // Get organization from campaign
            $organization = $campaign?->organization;

            // Check if organization can receive donations
            if ($organization && ! $organization->canReceiveDonations()) {
                throw new \Exception(__('Organization cannot receive donations due to compliance restrictions'));
            }

            // Calculate all fees
            $fees = $this->calculator->calculateFees($amount, $gateway, $organization, $campaign);

            // Get payment method by gateway type or default to 'card'
            $paymentMethodCode = $gateway->type->value ?? 'card';
            $paymentMethod = PaymentMethod::where('code', $paymentMethodCode)->first();

            // Create Donation first
            $donation = Donation::create([
                'donor_id' => $donor?->id,
                'campaign_id' => $campaign?->id,
                'amount' => $amount,
                'status' => DonationStatus::PENDING,
                'payment_method_id' => $paymentMethod?->id,
                'is_anonymous' => $metadata['is_anonymous'] ?? false,
                'message' => $metadata['message'] ?? null,
            ]);

            // Create Transaction linked to Donation
            $transaction = Transaction::create([
                'type' => TransactionType::DONATION,
                'donation_id' => $donation->id,
                'payment_gateway_id' => $gateway->id,
                'amount' => $fees['amount'],
                'gateway_processing_fee' => $fees['gateway_processing_fee'],
                'platform_commission' => $fees['platform_commission'],
                'net_amount' => $fees['net_amount'],
                'total_amount' => $fees['total_amount'],
                'status' => TransactionStatus::PENDING,
                'payment_method_id' => $paymentMethod?->id,
                'notes' => $metadata['notes'] ?? null,
            ]);

            // Update Donation with transaction_id
            $donation->update(['transaction_id' => $transaction->id]);

            Log::info('Donation and Transaction created', [
                'donation_id' => $donation->id,
                'donation_ref' => $donation->donation_ref,
                'transaction_id' => $transaction->id,
                'transaction_ref' => $transaction->transaction_ref,
                'amount' => $amount,
                'campaign_id' => $campaign?->id,
            ]);

            // Log donor activity if donor exists (client-side donation)
            if ($donor) {
                DonorActivityLog::log(
                    ActivityAction::CREATED,
                    $campaign ? __('Donation of :amount :currency made to campaign ":campaign"', [
                        'amount' => number_format($amount, 2),
                        'currency' => settings('currency', 'USD'),
                        'campaign' => $campaign->title,
                    ]) : __('General donation of :amount :currency made', [
                        'amount' => number_format($amount, 2),
                        'currency' => settings('currency', 'USD'),
                    ]),
                    $donor,
                    [
                        'donation_id' => $donation->id,
                        'donation_ref' => $donation->donation_ref,
                        'campaign_id' => $campaign?->id,
                        'campaign_title' => $campaign?->title,
                        'amount' => $amount,
                        'currency' => settings('currency', 'USD'),
                        'payment_method' => $donation->paymentMethod?->name ?? __('N/A'),
                        'is_anonymous' => $donation->is_anonymous,
                    ]
                );
            }

            return [
                'donation' => $donation,
                'transaction' => $transaction,
            ];
        });
    }

    /**
     * Update transaction status
     */
    public function updateStatus(Transaction $transaction, string $status, array $data = []): Transaction
    {
        return DB::transaction(function () use ($transaction, $status, $data) {
            $transaction->update([
                'status' => $status,
                'gateway_transaction_id' => $data['gateway_transaction_id'] ?? $transaction->gateway_transaction_id,
                'gateway_response' => $data['gateway_response'] ?? $transaction->gateway_response,
                'failure_reason' => $data['failure_reason'] ?? $transaction->failure_reason,
            ]);

            // If completed, update donation, campaign raised amount, and organization wallet
            if ($status === TransactionStatus::COMPLETED->value) {
                if ($transaction->donation && $transaction->donation->donor) {
                    $donation = $transaction->donation;
                    $donation->update(['status' => DonationStatus::PAID]);
                    
                    if ($donation->campaign) {
                        $this->updateCampaignRaisedAmount($donation);

                        // Add money to organization wallet
                        $organization = $donation->campaign->organization;
                        if ($organization && $organization->wallet) {
                            // Add net_amount to wallet (money after fees)
                            $organization->wallet->addBalance($transaction->net_amount);
                        }
                    }

                    // Log to donor activity logs (client-side payment completion)
                    DonorActivityLog::log(
                        ActivityAction::APPROVED,
                        __('Donation :ref of :amount :currency has been successfully paid', [
                            'ref' => $donation->donation_ref,
                            'amount' => number_format($donation->amount, 2),
                            'currency' => settings('currency', 'USD'),
                        ]),
                        $donation->donor,
                        [
                            'donation_id' => $donation->id,
                            'donation_ref' => $donation->donation_ref,
                            'transaction_id' => $transaction->id,
                            'transaction_ref' => $transaction->transaction_ref,
                            'campaign_id' => $donation->campaign_id,
                            'amount' => $donation->amount,
                            'currency' => settings('currency', 'USD'),
                        ]
                    );
                }
            }

            return $transaction->fresh();
        });
    }

    /**
     * Update campaign raised amount
     */
    protected function updateCampaignRaisedAmount(Donation $donation): void
    {
        $donation->campaign->increment('raised_amount', $donation->amount);
    }

    /**
     * Process refund
     */
    public function processRefund(Transaction $transaction, ?float $amount = null, ?string $refundReason = null): Transaction
    {
        return DB::transaction(function () use ($transaction, $amount, $refundReason) {
            $refundAmount = $amount ?? $transaction->amount;

            // Validate refund amount
            if ($refundAmount > $transaction->amount) {
                throw new \Exception(__('Refund amount cannot exceed original transaction amount'));
            }

            // Check if transaction can be refunded
            if ($transaction->status !== TransactionStatus::COMPLETED) {
                throw new \Exception(__('Only completed transactions can be refunded'));
            }

            // Create refund transaction record
            $refundTransaction = Transaction::create([
                'type' => TransactionType::REFUND,
                'donation_id' => $transaction->donation_id,
                'payment_gateway_id' => $transaction->payment_gateway_id,
                'amount' => $refundAmount,
                'gateway_processing_fee' => 0, // Refunds typically don't have fees
                'platform_commission' => 0,
                'net_amount' => -$refundAmount, // Negative to represent money going out
                'total_amount' => -$refundAmount,
                'status' => TransactionStatus::COMPLETED, // Refund is immediately completed
                'payment_method_id' => $transaction->payment_method_id,
                'gateway_transaction_id' => null, // Will be set by gateway if needed
                'notes' => __('Refund for transaction :ref. Original transaction: :original_ref', [
                    'ref' => $transaction->transaction_ref,
                    'original_ref' => $transaction->transaction_ref,
                ]).($refundReason ? "\n".__('Refund Reason').': '.$refundReason : ''),
            ]);

            // Update original transaction status
            $transaction->update([
                'status' => TransactionStatus::REFUNDED,
                'notes' => ($transaction->notes ?? '')."\n".__('Refunded: :amount :currency on :date', [
                    'amount' => number_format($refundAmount, 2),
                    'currency' => settings('currency', 'USD'),
                    'date' => now()->toDateTimeString(),
                ]).($refundReason ? "\n".__('Refund Reason').': '.$refundReason : ''),
            ]);

            // Update donation if exists
            if ($transaction->donation && $transaction->donation->donor) {
                $donation = $transaction->donation;
                $donation->update(['status' => DonationStatus::REFUNDED]);

                // Decrease campaign raised amount
                if ($donation->campaign) {
                    $donation->campaign->decrement('raised_amount', $refundAmount);

                    // Subtract refund amount from organization wallet
                    $organization = $donation->campaign->organization;
                    if ($organization && $organization->wallet) {
                        // Subtract the net_amount that was originally added to wallet
                        // Use the transaction's net_amount, not the refund amount
                        $netAmountToRefund = $transaction->net_amount;
                        if ($organization->wallet->available_balance >= $netAmountToRefund) {
                            $organization->wallet->subtractBalance($netAmountToRefund);
                        } else {
                            throw new \Exception(__('Insufficient available balance in organization wallet for refund'));
                        }
                    }
                }

                // Log to donor activity logs
                DonorActivityLog::log(
                    ActivityAction::REJECTED,
                    __('Donation :ref of :amount :currency has been refunded', [
                        'ref' => $donation->donation_ref,
                        'amount' => number_format($refundAmount, 2),
                        'currency' => settings('currency', 'USD'),
                    ]),
                    $donation->donor,
                    [
                        'donation_id' => $donation->id,
                        'donation_ref' => $donation->donation_ref,
                        'transaction_id' => $transaction->id,
                        'transaction_ref' => $transaction->transaction_ref,
                        'refund_transaction_id' => $refundTransaction->id,
                        'refund_transaction_ref' => $refundTransaction->transaction_ref,
                        'campaign_id' => $donation->campaign_id,
                        'refund_amount' => $refundAmount,
                        'currency' => settings('currency', 'USD'),
                        'refund_reason' => $refundReason,
                    ]
                );
            }

            return $refundTransaction;
        });
    }

    /**
     * Process wallet top-up
     */
    public function processWalletTopup(
        float $amount,
        Donor $donor,
        PaymentGateway $gateway,
        array $metadata = []
    ): Transaction {
        return DB::transaction(function () use ($amount, $donor, $gateway, $metadata) {
            // Calculate gateway fees only (no platform commission for top-ups)
            $gatewayFee = ($amount * 0.025) + 500; // Simplified: 2.5% + 500 USD
            $totalAmount = $amount + $gatewayFee;

            // Get payment method by gateway type
            $paymentMethodCode = $gateway->type->value ?? 'card';
            $paymentMethod = PaymentMethod::where('code', $paymentMethodCode)->first();

            // Create Transaction for wallet top-up
            $transaction = Transaction::create([
                'type' => TransactionType::WALLET_TOPUP,
                'donor_wallet_id' => $donor->wallet->id,
                'payment_gateway_id' => $gateway->id,
                'amount' => $amount,
                'gateway_processing_fee' => $gatewayFee,
                'platform_commission' => 0,
                'net_amount' => $amount,
                'total_amount' => $totalAmount,
                'status' => TransactionStatus::PENDING,
                'payment_method_id' => $paymentMethod?->id,
                'notes' => $metadata['notes'] ?? null,
            ]);

            // When completed, add to wallet balance
            if ($transaction->status === TransactionStatus::COMPLETED) {
                $donor->wallet->increment('balance', $amount);
                $donor->wallet->update(['last_transaction_at' => now()]);
            }

            return $transaction;
        });
    }
}
