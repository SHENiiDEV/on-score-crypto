<?php

namespace App\Services\Blockchain;

use App\Services\Blockchain\DTO\WalletOnchainData;

interface BlockchainProviderInterface
{
    public function getName(): string;

    public function supportsNetwork(string $network): bool;

    /**
     * @throws \Exception
     */
    public function fetchWalletData(string $network, string $address): WalletOnchainData;
}
