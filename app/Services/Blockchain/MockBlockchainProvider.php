<?php

namespace App\Services\Blockchain;

use App\Services\Blockchain\DTO\TransactionDTO;
use App\Services\Blockchain\DTO\WalletOnchainData;
use Carbon\Carbon;

class MockBlockchainProvider implements BlockchainProviderInterface
{
    public function getName(): string
    {
        return 'mock_chain_fixtures';
    }

    public function supportsNetwork(string $network): bool
    {
        return in_array(strtolower($network), ['tron', 'ethereum', 'bsc', 'solana', 'bitcoin']);
    }

    public function fetchWalletData(string $network, string $address): WalletOnchainData
    {
        $net = strtolower($network);

        // Case 1: Bybit Hot Wallet (CEX Pool)
        if (strcasecmp($address, 'TLyqzVGLV1srkB7dToTAnYg9auqwTmTX71') === 0 || str_contains(strtolower($address), 'bybit')) {
            return $this->generateBybitHotWalletFixture($net, $address);
        }

        // Case 2: VIP Whale Player
        if (str_contains(strtolower($address), 'vip') || str_contains(strtolower($address), 'whale') || str_ends_with($address, '7788')) {
            return $this->generateVipWhaleFixture($net, $address);
        }

        // Case 3: Regular Player
        if (str_contains(strtolower($address), 'regular') || str_ends_with($address, '33')) {
            return $this->generateRegularPlayerFixture($net, $address);
        }

        // Case 4: Default / Deterministic dynamic fixture based on address hash
        return $this->generateDynamicFixture($net, $address);
    }

    private function generateBybitHotWalletFixture(string $network, string $address): WalletOnchainData
    {
        $now = Carbon::now();
        $transactions = [];

        // Pool has hundreds of thousands of transactions
        for ($i = 1; $i <= 40; $i++) {
            $transactions[] = new TransactionDTO(
                txHash: 'tx_bybit_sweep_' . md5($address . $i),
                network: $network,
                fromAddress: 'TUserDepositSweeper_' . $i,
                toAddress: $address,
                amount: 15000 + ($i * 1200),
                tokenSymbol: 'USDT',
                usdValue: 15000 + ($i * 1200),
                timestamp: $now->copy()->subHours($i * 2),
                direction: 'in'
            );
        }

        return new WalletOnchainData(
            address: $address,
            network: $network,
            nativeBalance: 45000000.0,
            nativeBalanceUsd: 11250000.0,
            visibleTotalBalanceUsd: 485000000.0,
            tokens: [
                'USDT' => ['amount' => 473750000.0, 'usd_price' => 1.0, 'usd_value' => 473750000.0],
                'TRX' => ['amount' => 45000000.0, 'usd_price' => 0.25, 'usd_value' => 11250000.0],
            ],
            transactions: $transactions,
            firstActivityAt: $now->copy()->subYears(5),
            lastActivityAt: $now->copy()->subMinutes(3),
            totalTxCount: 2849200,
            providerName: 'mock_chain_fixtures',
            providerConfidence: 1.0,
            meta: ['is_exchange_fixture' => true]
        );
    }

    private function generateVipWhaleFixture(string $network, string $address): WalletOnchainData
    {
        $now = Carbon::now();
        $stakeAddress = ($network === 'tron') ? 'TXa5eQ7Vw8K8qL9pMn1bV4c7xZ3wE9rT2Y' : '0xc564ee9f21ed8a2d8e7e76c07d402d4b88bee580';
        $rollbitAddress = ($network === 'tron') ? 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t' : '0x0d0707963952f2fba59dd06f2b425ace40b492fe';
        $bybitAddress = 'TLyqzVGLV1srkB7dToTAnYg9auqwTmTX71';

        $transactions = [
            // CEX withdrawal from Bybit into this personal wallet (Seed funding)
            new TransactionDTO('tx_vip_01', $network, $bybitAddress, $address, 85000, 'USDT', 85000, $now->copy()->subDays(3), 'in'),
            
            // Stake deposits & withdrawals
            new TransactionDTO('tx_vip_02', $network, $address, $stakeAddress, 25000, 'USDT', 25000, $now->copy()->subDays(2), 'out'),
            new TransactionDTO('tx_vip_03', $network, $stakeAddress, $address, 42000, 'USDT', 42000, $now->copy()->subDays(1)->subHours(12), 'in'),
            new TransactionDTO('tx_vip_04', $network, $address, $stakeAddress, 30000, 'USDT', 30000, $now->copy()->subDays(1), 'out'),

            // Rollbit deposits
            new TransactionDTO('tx_vip_05', $network, $address, $rollbitAddress, 15000, 'USDT', 15000, $now->copy()->subHours(18), 'out'),
            new TransactionDTO('tx_vip_06', $network, $rollbitAddress, $address, 28000, 'USDT', 28000, $now->copy()->subHours(6), 'in'),

            // Historical big movements
            new TransactionDTO('tx_vip_07', $network, $address, $stakeAddress, 50000, 'USDT', 50000, $now->copy()->subDays(45), 'out'),
            new TransactionDTO('tx_vip_08', $network, $stakeAddress, $address, 110000, 'USDT', 110000, $now->copy()->subDays(40), 'in'),
            new TransactionDTO('tx_vip_09', $network, $bybitAddress, $address, 120000, 'USDT', 120000, $now->copy()->subDays(120), 'in'),
        ];

        $usdtBalance = 165000.0;
        $nativeBalance = 120000.0;
        $nativeUsd = $nativeBalance * 0.25;

        return new WalletOnchainData(
            address: $address,
            network: $network,
            nativeBalance: $nativeBalance,
            nativeBalanceUsd: round($nativeUsd, 2),
            visibleTotalBalanceUsd: round($usdtBalance + $nativeUsd, 2),
            tokens: [
                'USDT' => ['amount' => $usdtBalance, 'usd_price' => 1.0, 'usd_value' => $usdtBalance],
                'TRX' => ['amount' => $nativeBalance, 'usd_price' => 0.25, 'usd_value' => $nativeUsd],
            ],
            transactions: $transactions,
            firstActivityAt: $now->copy()->subDays(480),
            lastActivityAt: $now->copy()->subHours(6),
            totalTxCount: 246,
            providerName: 'mock_chain_fixtures',
            providerConfidence: 0.98
        );
    }

    private function generateRegularPlayerFixture(string $network, string $address): WalletOnchainData
    {
        $now = Carbon::now();
        $stakeAddress = ($network === 'tron') ? 'TQn9Y2khEsLJW1ChVWFMSMeSTow5KcbLSE' : '0xc564ee9f21ed8a2d8e7e76c07d402d4b88bee580';
        $bcGameAddress = 'TBCGameHotWalletTronCollector9988776655';

        $transactions = [
            new TransactionDTO('tx_reg_01', $network, 'TLyqzVGLV1srkB7dToTAnYg9auqwTmTX71', $address, 600, 'USDT', 600, $now->copy()->subDays(2), 'in'),
            new TransactionDTO('tx_reg_02', $network, $address, $stakeAddress, 250, 'USDT', 250, $now->copy()->subDays(2)->addHours(1), 'out'),
            new TransactionDTO('tx_reg_03', $network, $stakeAddress, $address, 450, 'USDT', 450, $now->copy()->subDays(1), 'in'),
            new TransactionDTO('tx_reg_04', $network, $address, $bcGameAddress, 300, 'USDT', 300, $now->copy()->subHours(10), 'out'),
            new TransactionDTO('tx_reg_05', $network, $address, $stakeAddress, 200, 'USDT', 200, $now->copy()->subDays(20), 'out'),
        ];

        $usdtBalance = 850.0;
        $nativeBalance = 320.0;
        $nativeUsd = $nativeBalance * 0.25;

        return new WalletOnchainData(
            address: $address,
            network: $network,
            nativeBalance: $nativeBalance,
            nativeBalanceUsd: round($nativeUsd, 2),
            visibleTotalBalanceUsd: round($usdtBalance + $nativeUsd, 2),
            tokens: [
                'USDT' => ['amount' => $usdtBalance, 'usd_price' => 1.0, 'usd_value' => $usdtBalance],
                'TRX' => ['amount' => $nativeBalance, 'usd_price' => 0.25, 'usd_value' => $nativeUsd],
            ],
            transactions: $transactions,
            firstActivityAt: $now->copy()->subDays(90),
            lastActivityAt: $now->copy()->subHours(10),
            totalTxCount: 28,
            providerName: 'mock_chain_fixtures',
            providerConfidence: 0.95
        );
    }

    private function generateDynamicFixture(string $network, string $address): WalletOnchainData
    {
        $now = Carbon::now();
        $hashNum = hexdec(substr(md5($address), 0, 4)) % 100;
        
        $isGambler = ($hashNum > 30);
        $balance = round($hashNum * 18.5, 2);
        $transactions = [];

        if ($isGambler) {
            $stakeAddress = ($network === 'tron') ? 'TXa5eQ7Vw8K8qL9pMn1bV4c7xZ3wE9rT2Y' : '0xc564ee9f21ed8a2d8e7e76c07d402d4b88bee580';
            $transactions[] = new TransactionDTO('tx_dyn_01', $network, 'TLyqzVGLV1srkB7dToTAnYg9auqwTmTX71', $address, $balance * 2, 'USDT', $balance * 2, $now->copy()->subDays(5), 'in');
            $transactions[] = new TransactionDTO('tx_dyn_02', $network, $address, $stakeAddress, round($balance * 0.6, 2), 'USDT', round($balance * 0.6, 2), $now->copy()->subDays(3), 'out');
            $transactions[] = new TransactionDTO('tx_dyn_03', $network, $stakeAddress, $address, round($balance * 0.9, 2), 'USDT', round($balance * 0.9, 2), $now->copy()->subDays(1), 'in');
        } else {
            $transactions[] = new TransactionDTO('tx_dyn_01', $network, 'TPeerWalletA112233', $address, $balance + 10, 'USDT', $balance + 10, $now->copy()->subDays(12), 'in');
            $transactions[] = new TransactionDTO('tx_dyn_02', $network, $address, 'TPeerWalletB445566', 15, 'USDT', 15, $now->copy()->subDays(8), 'out');
        }

        return new WalletOnchainData(
            address: $address,
            network: $network,
            nativeBalance: 50.0,
            nativeBalanceUsd: 12.5,
            visibleTotalBalanceUsd: $balance + 12.5,
            tokens: [
                'USDT' => ['amount' => $balance, 'usd_price' => 1.0, 'usd_value' => $balance],
                'TRX' => ['amount' => 50.0, 'usd_price' => 0.25, 'usd_value' => 12.5],
            ],
            transactions: $transactions,
            firstActivityAt: $now->copy()->subDays(45),
            lastActivityAt: $now->copy()->subDays(1),
            totalTxCount: count($transactions),
            providerName: 'mock_chain_fixtures',
            providerConfidence: 0.90
        );
    }
}
