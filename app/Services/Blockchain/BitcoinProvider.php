<?php

namespace App\Services\Blockchain;

use App\Services\Blockchain\DTO\TransactionDTO;
use App\Services\Blockchain\DTO\WalletOnchainData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BitcoinProvider implements BlockchainProviderInterface
{
    private string $baseUrl;
    private float $fallbackBtcPrice = 68500.0;

    public function __construct(?string $baseUrl = null)
    {
        $this->baseUrl = $baseUrl ?? config('services.bitcoin.base_url', 'https://mempool.space/api');
    }

    public function getName(): string
    {
        return 'mempool_bitcoin_api';
    }

    public function supportsNetwork(string $network): bool
    {
        return in_array(strtolower($network), ['bitcoin', 'btc']);
    }

    public function fetchWalletData(string $network, string $address): WalletOnchainData
    {
        try {
            $client = Http::timeout(12)->baseUrl($this->baseUrl);

            // 1. Fetch UTXO Chain Stats
            $addrResp = $client->get("/address/{$address}");
            
            $btcBalance = 0.0;
            $txCount = 0;

            if ($addrResp->successful()) {
                $data = $addrResp->json();
                $fundedSats = (float)($data['chain_stats']['funded_txo_sum'] ?? 0);
                $spentSats = (float)($data['chain_stats']['spent_txo_sum'] ?? 0);
                $satsBalance = max(0, $fundedSats - $spentSats);
                $btcBalance = $satsBalance / 100_000_000;
                $txCount = (int)($data['chain_stats']['tx_count'] ?? 0);
            }

            // 2. Fetch Live Price or estimate
            $btcPrice = $this->fetchBtcPrice();
            $btcBalanceUsd = $btcBalance * $btcPrice;

            // 3. Fetch Recent Transactions
            $txResp = $client->get("/address/{$address}/txs");
            $transactions = [];
            $firstSeen = null;
            $lastSeen = null;

            if ($txResp->successful() && is_array($txResp->json())) {
                foreach ($txResp->json() as $tx) {
                    $txid = $tx['txid'] ?? ('tx_' . md5(json_encode($tx)));
                    $blockTime = $tx['status']['block_time'] ?? time();
                    $dt = Carbon::createFromTimestamp($blockTime);

                    // Determine if target address was receiver (in) or sender (out)
                    $inAmountSats = 0;
                    $outAmountSats = 0;
                    $counterparty = 'BTC_Network_Peers';

                    // Check Outputs (received)
                    if (!empty($tx['vout'])) {
                        foreach ($tx['vout'] as $vout) {
                            $toAddr = $vout['scriptpubkey_address'] ?? '';
                            if ($toAddr === $address) {
                                $inAmountSats += (float)($vout['value'] ?? 0);
                            } elseif (!empty($toAddr)) {
                                $counterparty = $toAddr;
                            }
                        }
                    }

                    // Check Inputs (spent)
                    if (!empty($tx['vin'])) {
                        foreach ($tx['vin'] as $vin) {
                            $fromAddr = $vin['prevout']['scriptpubkey_address'] ?? '';
                            if ($fromAddr === $address) {
                                $outAmountSats += (float)($vin['prevout']['value'] ?? 0);
                            } elseif (!empty($fromAddr)) {
                                $counterparty = $fromAddr;
                            }
                        }
                    }

                    $direction = ($inAmountSats >= $outAmountSats) ? 'in' : 'out';
                    $netSats = ($direction === 'in') ? $inAmountSats : $outAmountSats;
                    $amountBtc = $netSats / 100_000_000;
                    $usdVal = $amountBtc * $btcPrice;

                    $transactions[] = new TransactionDTO(
                        txHash: $txid,
                        network: 'bitcoin',
                        fromAddress: ($direction === 'in') ? $counterparty : $address,
                        toAddress: ($direction === 'in') ? $address : $counterparty,
                        amount: $amountBtc,
                        tokenSymbol: 'BTC',
                        usdValue: $usdVal,
                        timestamp: $dt,
                        direction: $direction,
                    );

                    if ($firstSeen === null || $dt->lt($firstSeen)) {
                        $firstSeen = $dt;
                    }
                    if ($lastSeen === null || $dt->gt($lastSeen)) {
                        $lastSeen = $dt;
                    }
                }
            }

            $tokens = [
                'BTC' => [
                    'amount' => $btcBalance,
                    'usd_price' => $btcPrice,
                    'usd_value' => round($btcBalanceUsd, 2),
                ],
            ];

            return new WalletOnchainData(
                address: $address,
                network: 'bitcoin',
                nativeBalance: $btcBalance,
                nativeBalanceUsd: round($btcBalanceUsd, 2),
                visibleTotalBalanceUsd: round($btcBalanceUsd, 2),
                tokens: $tokens,
                transactions: $transactions,
                firstActivityAt: $firstSeen,
                lastActivityAt: $lastSeen ?? Carbon::now(),
                totalTxCount: $txCount ?: count($transactions),
                providerName: 'mempool_bitcoin_api',
                providerConfidence: 0.99
            );
        } catch (\Throwable $e) {
            Log::warning("BitcoinProvider failed for {$address}: " . $e->getMessage());
            throw $e;
        }
    }

    private function fetchBtcPrice(): float
    {
        try {
            $resp = Http::timeout(3)->get('https://mempool.space/api/v1/prices');
            if ($resp->successful() && $price = $resp->json('USD')) {
                return (float)$price;
            }
        } catch (\Throwable $e) {
            // fallback
        }
        return $this->fallbackBtcPrice;
    }
}
