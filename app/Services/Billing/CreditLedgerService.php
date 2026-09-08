<?php

namespace App\Services\Billing;

use App\Models\Account;
use App\Models\CreditLedger;
use Illuminate\Support\Facades\DB;

class CreditLedgerService
{
    /**
     * Reserve credits before starting analysis
     * @throws \Exception
     */
    public function reserveCredits(Account $account, int $amount, string $referenceId, string $actor = 'system'): void
    {
        DB::transaction(function () use ($account, $amount, $referenceId, $actor) {
            /** @var Account $lockedAccount */
            $lockedAccount = Account::where('id', $account->id)->lockForUpdate()->firstOrFail();

            if ($lockedAccount->credit_balance < $amount) {
                throw new \RuntimeException("Insufficient credit balance. Required: {$amount}, Available: {$lockedAccount->credit_balance}");
            }

            $newBalance = $lockedAccount->credit_balance - $amount;
            $lockedAccount->credit_balance = $newBalance;
            $lockedAccount->save();

            CreditLedger::create([
                'account_id' => $lockedAccount->id,
                'delta' => -$amount,
                'type' => 'reservation',
                'balance_after' => $newBalance,
                'reference_id' => $referenceId,
                'reason' => "Reserved {$amount} credits for analysis",
                'actor' => $actor,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Finalize analysis charge (confirms reserved credits)
     */
    public function chargeCredits(Account $account, int $amount, string $referenceId, string $actor = 'system'): void
    {
        CreditLedger::create([
            'account_id' => $account->id,
            'delta' => 0, // already reserved, logging charge confirmation
            'type' => 'charge',
            'balance_after' => $account->fresh()->credit_balance,
            'reference_id' => $referenceId,
            'reason' => "Charged {$amount} credits for successful analysis",
            'actor' => $actor,
            'created_at' => now(),
        ]);
    }

    /**
     * Refund reserved credits upon analysis failure
     */
    public function refundCredits(Account $account, int $amount, string $referenceId, string $reason = 'Analysis failed', string $actor = 'system'): void
    {
        DB::transaction(function () use ($account, $amount, $referenceId, $reason, $actor) {
            /** @var Account $lockedAccount */
            $lockedAccount = Account::where('id', $account->id)->lockForUpdate()->firstOrFail();

            $newBalance = $lockedAccount->credit_balance + $amount;
            $lockedAccount->credit_balance = $newBalance;
            $lockedAccount->save();

            CreditLedger::create([
                'account_id' => $lockedAccount->id,
                'delta' => $amount,
                'type' => 'refund',
                'balance_after' => $newBalance,
                'reference_id' => $referenceId,
                'reason' => "Refunded {$amount} credits: {$reason}",
                'actor' => $actor,
                'created_at' => now(),
            ]);
        });
    }

    /**
     * Admin manual top up / write off
     */
    public function adjustBalance(Account $account, int $delta, string $reason, string $actor): CreditLedger
    {
        return DB::transaction(function () use ($account, $delta, $reason, $actor) {
            /** @var Account $lockedAccount */
            $lockedAccount = Account::where('id', $account->id)->lockForUpdate()->firstOrFail();

            $newBalance = $lockedAccount->credit_balance + $delta;
            if ($newBalance < 0) {
                throw new \RuntimeException("Balance cannot be reduced below zero.");
            }

            $lockedAccount->credit_balance = $newBalance;
            $lockedAccount->save();

            $type = $delta >= 0 ? 'topup' : 'deduction';

            return CreditLedger::create([
                'account_id' => $lockedAccount->id,
                'delta' => $delta,
                'type' => $type,
                'balance_after' => $newBalance,
                'reference_id' => 'ADMIN_ADJ_' . strtoupper(uniqid()),
                'reason' => $reason,
                'actor' => $actor,
                'created_at' => now(),
            ]);
        });
    }
}
