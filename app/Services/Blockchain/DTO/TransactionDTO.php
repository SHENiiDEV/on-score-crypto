<?php

namespace App\Services\Blockchain\DTO;

class TransactionDTO
{
    public function __construct(
        public string $txHash,
        public string $network,
        public string $fromAddress,
        public string $toAddress,
        public float $amount,
        public string $tokenSymbol,
        public float $usdValue,
        public \DateTimeInterface $timestamp,
        public string $direction, // 'in' or 'out'
        public ?string $contractAddress = null,
        public bool $isContractCall = false,
    ) {}

    public function toArray(): array
    {
        return [
            'tx_hash' => $this->txHash,
            'network' => $this->network,
            'from' => $this->fromAddress,
            'to' => $this->toAddress,
            'amount' => $this->amount,
            'token' => $this->tokenSymbol,
            'usd_value' => round($this->usdValue, 2),
            'timestamp' => $this->timestamp->format(\DateTimeInterface::ATOM),
            'direction' => $this->direction,
            'contract_address' => $this->contractAddress,
        ];
    }
}
