<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\CreditWallet;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;

class CreditService
{
    public function ensureWallet(Organization $organization): CreditWallet
    {
        return CreditWallet::firstOrCreate(
            ['organization_id' => $organization->id],
            ['balance' => 0]
        );
    }

    public function getBalance(Organization $organization): int
    {
        return $this->ensureWallet($organization)->balance;
    }

    public function hasCredits(Organization $organization, int $amount = 1): bool
    {
        return $this->getBalance($organization) >= $amount;
    }

    public function addCredits(Organization $organization, int $amount, ?string $remarks = null, ?int $createdBy = null): CreditTransaction
    {
        return DB::transaction(function () use ($organization, $amount, $remarks, $createdBy) {
            $wallet = $this->ensureWallet($organization);
            $wallet->increment('balance', $amount);
            $wallet->refresh();

            return CreditTransaction::create([
                'organization_id' => $organization->id,
                'type' => CreditTransaction::TYPE_CREDIT,
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'remarks' => $remarks ?? 'Credit recharge',
                'created_by' => $createdBy,
            ]);
        });
    }

    public function setBalance(Organization $organization, int $balance, ?string $remarks = null, ?int $createdBy = null): ?CreditTransaction
    {
        return DB::transaction(function () use ($organization, $balance, $remarks, $createdBy) {
            $wallet = $this->ensureWallet($organization);
            $previous = $wallet->balance;

            if ($previous === $balance) {
                return null;
            }

            $wallet->update(['balance' => $balance]);

            $diff = $balance - $previous;

            return CreditTransaction::create([
                'organization_id' => $organization->id,
                'type' => $diff >= 0 ? CreditTransaction::TYPE_CREDIT : CreditTransaction::TYPE_DEBIT,
                'amount' => abs($diff),
                'balance_after' => $balance,
                'remarks' => $remarks ?? 'Admin balance adjustment',
                'created_by' => $createdBy,
            ]);
        });
    }

    public function deductCredit(Organization $organization, ?string $remarks = null): ?CreditTransaction
    {
        return DB::transaction(function () use ($organization, $remarks) {
            $wallet = CreditWallet::where('organization_id', $organization->id)
                ->lockForUpdate()
                ->first();

            if (! $wallet || $wallet->balance < 1) {
                return null;
            }

            $wallet->decrement('balance', 1);
            $wallet->refresh();

            return CreditTransaction::create([
                'organization_id' => $organization->id,
                'type' => CreditTransaction::TYPE_DEBIT,
                'amount' => 1,
                'balance_after' => $wallet->balance,
                'remarks' => $remarks ?? 'Message sent',
            ]);
        });
    }
}
