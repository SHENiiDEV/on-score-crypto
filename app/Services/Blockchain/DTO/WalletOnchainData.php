<?php

namespace App\Services\Blockchain\DTO;

class WalletOnchainData
{
    /**
     * @param TransactionDTO[] $transactions
     * @param array<string, mixed> $tokens
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public string $address,
        public string $network,
        public float $nativeBalance,
        public float $nativeBalanceUsd,
        public float $visibleTotalBalanceUsd,
        public array $tokens,
        public array $transactions,
        public ?\DateTimeInterface $firstActivityAt,
        public ?\DateTimeInterface $lastActivityAt,
        public int $totalTxCount,
        public string $providerName,
        public float $providerConfidence = 1.0,
        public array $meta = [],
    ) {}
}
