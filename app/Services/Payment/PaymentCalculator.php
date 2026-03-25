<?php

namespace App\Services\Payment;

use App\Enums\CampaignType;
use App\Enums\OrganizationType;
use App\Enums\PaymentGatewayFeeType;
use App\Models\Campaign;
use App\Models\Organization;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayFee;

class PaymentCalculator
{
    /**
     * Calculate all fees for a donation
     */
    public function calculateFees(
        float $amount,
        PaymentGateway $gateway,
        ?Organization $organization = null,
        ?Campaign $campaign = null
    ): array {
        // Gateway Processing Fee (User → Platform)
        $gatewayFee = $this->calculateGatewayProcessingFee($amount, $gateway);

        // Platform Commission (Platform → Organization)
        $platformCommission = $this->calculatePlatformCommission(
            $amount,
            $organization,
            $campaign
        );

        // Net amount to organization
        $netAmount = $amount - $platformCommission;

        // Total amount donor pays
        $totalAmount = $amount + $gatewayFee;

        return [
            'amount' => $amount,
            'gateway_processing_fee' => $gatewayFee,
            'platform_commission' => $platformCommission,
            'net_amount' => $netAmount,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Calculate gateway processing fee
     */
    protected function calculateGatewayProcessingFee(float $amount, PaymentGateway $gateway): float
    {
        $fee = PaymentGatewayFee::where('payment_gateway_id', $gateway->id)
            ->where('fee_type', PaymentGatewayFeeType::GATEWAY_PROCESSING)
            ->where('is_active', true)
            ->first();

        if (! $fee) {
            // Fallback to gateway's default processing fee
            $percentage = $gateway->processing_fee_percentage ?? 0;
            $fixed = $gateway->processing_fee_fixed ?? 0;

            return ($amount * $percentage / 100) + $fixed;
        }

        return $this->calculateFee($amount, $fee->percentage, $fee->fixed_amount);
    }

    /**
     * Calculate platform commission
     */
    protected function calculatePlatformCommission(
        float $amount,
        ?Organization $organization = null,
        ?Campaign $campaign = null
    ): float {
        if (!$organization || !$campaign) {
            return 0; // No platform commission for general donations
        }
        $organizationType = $organization->type ?? OrganizationType::STANDARD;
        $campaignType = $campaign->campaign_type ?? CampaignType::STANDARD;

        $fee = PaymentGatewayFee::where('fee_type', PaymentGatewayFeeType::PLATFORM_COMMISSION)
            ->where('organization_type', $organizationType)
            ->where('campaign_type', $campaignType)
            ->where('is_active', true)
            ->first();

        if (! $fee) {
            // Default platform commission: 5% for standard, 3% for verified, 2.5% for featured
            $percentage = match (true) {
                $campaignType === CampaignType::FEATURED => 2.5,
                $organizationType === OrganizationType::VERIFIED => 3.0,
                default => 5.0,
            };

            return $amount * $percentage / 100;
        }

        return $this->calculateFee($amount, $fee->percentage, $fee->fixed_amount);
    }

    /**
     * Calculate fee from percentage and fixed amount
     */
    protected function calculateFee(float $amount, float $percentage, int $fixed): float
    {
        return ($amount * $percentage / 100) + $fixed;
    }
}
