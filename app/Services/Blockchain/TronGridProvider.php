<?php

namespace App\Services\Blockchain;

use App\Services\Blockchain\DTO\TransactionDTO;
use App\Services\Blockchain\DTO\WalletOnchainData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TronGridProvider implements BlockchainProviderInterface
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct(?string $apiKey = null, ?string $baseUrl = null)
    {
        $this->apiKey = $apiKey ?? config('services.trongrid.api_key', '');
        $this->baseUrl = $baseUrl ?? config('services.trongrid.base_url', 'https://api.trongrid.io');
    }

    public function getName(): string
    {
        return 'trongrid_mainnet';
    }

    public function supportsNetwork(string $network): bool
    {
        return in_array(strtolower($network), ['tron', 'trc20', 'trx']);
    }

    public function fetchWalletData(string $network, string $address): WalletOnchainData
    {
        try {
            $client = Http::timeout(10)->baseUrl($this->baseUrl);
            if (!empty($this->apiKey)) {
                $client = $client->withHeaders(['TRON-PRO-API-KEY' => $this->apiKey]);
            }

            // 1. Fetch Account Info (TRX Balance & Assets)
            $accountResp = $client->get("/v1/accounts/{$address}");
            
            $trxBalance = 0.0;
            $usdtBalance = 0.0;
            $tokens = [];

            if ($accountResp->successful() && !empty($accountResp->json('data.0'))) {
                $accData = $accountResp->json('data.0');
                $trxBalance = (float)($accData['balance'] ?? 0) / 1_000_000;

                // TRC20 Token Balances
                if (!empty($accData['trc20'])) {
                    foreach ($accData['trc20'] as $token) {
                        foreach ($token as $contract => $val) {
                            // USDT Contract on TRON: TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t
                            if (strcasecmp($contract, 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t') === 0) {
                                $usdtBalance = (float)$val / 1_000_000;
                            }
                        }
                    }
                }
            }

            $trxPriceUsd = 0.25; // Approx current TRX/USD rate
            $trxBalanceUsd = $trxBalance * $trxPriceUsd;
            $totalVisibleUsd = $trxBalanceUsd + $usdtBalance;

            $tokens = [
                'TRX' => [
                    'amount' => $trxBalance,
                    'usd_price' => $trxPriceUsd,
                    'usd_value' => round($trxBalanceUsd, 2),
                ],
                'USDT' => [
                    'amount' => $usdtBalance,
                    'usd_price' => 1.0,
                    'usd_value' => round($usdtBalance, 2),
                ],
            ];

            // 2. Fetch TRC-20 Transfer History
            $transfersResp = $client->get("/v1/accounts/{$address}/transactions/trc20", [
                'limit' => 50,
            ]);

            $transactions = [];
            $firstSeen = null;
            $lastSeen = null;

            if ($transfersResp->successful() && !empty($transfersResp->json('data'))) {
                foreach ($transfersResp->json('data') as $item) {
                    $from = $item['from'] ?? '';
                    $to = $item['to'] ?? '';
                    $symbol = $item['token_info']['symbol'] ?? 'USDT';
                    $decimals = (int)($item['token_info']['decimals'] ?? 6);
                    $rawAmount = (float)($item['value'] ?? 0);
                    $amount = $rawAmount / (10 ** $decimals);
                    $timestampMs = $item['block_timestamp'] ?? (time() * 1000);
                    $dt = Carbon::createFromTimestampMs($timestampMs);

                    $direction = (strcasecmp($to, $address) === 0) ? 'in' : 'out';
                    $usdVal = ($symbol === 'USDT') ? $amount : ($amount * $trxPriceUsd);

                    $tx = new TransactionDTO(
                        txHash: $item['transaction_id'] ?? ('tx_' . md5($from . $to . $timestampMs)),
                        network: 'tron',
                        fromAddress: $from,
                        toAddress: $to,
                        amount: $amount,
                        tokenSymbol: $symbol,
                        usdValue: $usdVal,
                        timestamp: $dt,
                        direction: $direction,
                        contractAddress: $item['token_info']['address'] ?? null,
                        isContractCall: true
                    );

                    $transactions[] = $tx;

                    if ($firstSeen === null || $dt->lt($firstSeen)) {
                        $firstSeen = $dt;
                    }
                    if ($lastSeen === null || $dt->gt($lastSeen)) {
                        $lastSeen = $dt;
                    }
                }
            }

            return new WalletOnchainData(
                address: $address,
                network: 'tron',
                nativeBalance: $trxBalance,
                nativeBalanceUsd: round($trxBalanceUsd, 2),
                visibleTotalBalanceUsd: round($totalVisibleUsd, 2),
                tokens: $tokens,
                transactions: $transactions,
                firstActivityAt: $firstSeen,
                lastActivityAt: $lastSeen ?? Carbon::now(),
                totalTxCount: count($transactions),
                providerName: 'trongrid_mainnet',
                providerConfidence: 0.95
            );
        } catch (\Throwable $e) {
            Log::warning("TronGridProvider failed for {$address}: " . $e->getMessage());
            throw $e;
        }
    }
}
