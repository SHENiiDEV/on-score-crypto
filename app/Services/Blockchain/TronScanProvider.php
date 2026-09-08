<?php

namespace App\Services\Blockchain;

use App\Services\Blockchain\DTO\TransactionDTO;
use App\Services\Blockchain\DTO\WalletOnchainData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TronScanProvider implements BlockchainProviderInterface
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct(?string $apiKey = null, ?string $baseUrl = null)
    {
        $this->apiKey = $apiKey ?? config('services.tronscan.api_key', '25e3a4f4-0aae-4b6a-a0c0-0565d0db00a0');
        $this->baseUrl = $baseUrl ?? config('services.tronscan.base_url', 'https://apilist.tronscanapi.com/api');
    }

    public function getName(): string
    {
        return 'tronscan_official_api';
    }

    public function supportsNetwork(string $network): bool
    {
        return in_array(strtolower($network), ['tron', 'trc20', 'trx']);
    }

    public function fetchWalletData(string $network, string $address): WalletOnchainData
    {
        try {
            $headers = [];
            if (!empty($this->apiKey)) {
                $headers['TRON-PRO-API-KEY'] = $this->apiKey;
            }

            $client = Http::timeout(12)
                ->withHeaders($headers)
                ->baseUrl($this->baseUrl);

            // 1. Fetch Account Info from TronScan
            $accResp = $client->get('/accountv2', [
                'address' => $address,
            ]);

            $trxBalance = 0.0;
            $usdtBalance = 0.0;
            $totalTxCount = 0;
            $firstSeen = null;
            $lastSeen = null;

            if ($accResp->successful()) {
                $accData = $accResp->json();
                $rawSun = (float)($accData['balance'] ?? 0);
                $trxBalance = $rawSun / 1_000_000;
                $totalTxCount = (int)($accData['totalTransactionCount'] ?? $accData['transactions'] ?? 0);

                if (!empty($accData['date_created'])) {
                    $firstSeen = Carbon::createFromTimestampMs($accData['date_created']);
                }
                if (!empty($accData['latest_operation_time'])) {
                    $lastSeen = Carbon::createFromTimestampMs($accData['latest_operation_time']);
                }

                // Extract withPriceTokens for USDT balance
                if (!empty($accData['withPriceTokens'])) {
                    foreach ($accData['withPriceTokens'] as $t) {
                        $sym = strtoupper($t['tokenAbbr'] ?? $t['tokenName'] ?? '');
                        if (in_array($sym, ['USDT', 'USDT-TRC20', 'TETHER USD']) || ($t['tokenId'] ?? '') === 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t') {
                            $usdtBalance = (float)($t['amount'] ?? 0);
                        }
                    }
                }
            }

            $trxPriceUsd = 0.25;
            $trxBalanceUsd = $trxBalance * $trxPriceUsd;
            $visibleTotalUsd = $trxBalanceUsd + $usdtBalance;

            $tokens = [
                'USDT' => [
                    'amount' => $usdtBalance,
                    'usd_price' => 1.0,
                    'usd_value' => round($usdtBalance, 2),
                ],
                'TRX' => [
                    'amount' => $trxBalance,
                    'usd_price' => $trxPriceUsd,
                    'usd_value' => round($trxBalanceUsd, 2),
                ],
            ];

            // 2. Fetch TRC-20 Transfers History
            $transfersResp = $client->get('/token_trc20/transfers', [
                'limit' => 50,
                'relatedAddress' => $address,
            ]);

            $transactions = [];
            if ($transfersResp->successful() && !empty($transfersResp->json('token_transfers'))) {
                foreach ($transfersResp->json('token_transfers') as $item) {
                    $from = $item['from_address'] ?? '';
                    $to = $item['to_address'] ?? '';
                    $symbol = $item['tokenInfo']['tokenAbbr'] ?? 'USDT';
                    $decimals = (int)($item['tokenInfo']['tokenDecimal'] ?? 6);
                    $rawVal = (float)($item['quant'] ?? 0);
                    $amount = $rawVal / (10 ** $decimals);
                    $ts = $item['block_ts'] ?? (time() * 1000);
                    $dt = Carbon::createFromTimestampMs($ts);

                    $direction = (strcasecmp($to, $address) === 0) ? 'in' : 'out';
                    $usdVal = ($symbol === 'USDT') ? $amount : ($amount * $trxPriceUsd);

                    $transactions[] = new TransactionDTO(
                        txHash: $item['transaction_id'] ?? ('tx_' . md5($from . $to . $ts)),
                        network: 'tron',
                        fromAddress: $from,
                        toAddress: $to,
                        amount: $amount,
                        tokenSymbol: $symbol,
                        usdValue: $usdVal,
                        timestamp: $dt,
                        direction: $direction,
                        contractAddress: $item['contract_address'] ?? null,
                        isContractCall: true
                    );

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
                visibleTotalBalanceUsd: round($visibleTotalUsd, 2),
                tokens: $tokens,
                transactions: $transactions,
                firstActivityAt: $firstSeen,
                lastActivityAt: $lastSeen ?? Carbon::now(),
                totalTxCount: $totalTxCount ?: count($transactions),
                providerName: 'tronscan_official_api',
                providerConfidence: 0.99
            );
        } catch (\Throwable $e) {
            Log::warning("TronScanProvider error for {$address}: " . $e->getMessage());
            throw $e;
        }
    }
}
