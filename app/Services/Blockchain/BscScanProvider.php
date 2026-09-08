<?php

namespace App\Services\Blockchain;

use App\Services\Blockchain\DTO\TransactionDTO;
use App\Services\Blockchain\DTO\WalletOnchainData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BscScanProvider implements BlockchainProviderInterface
{
    private string $apiKey;
    private string $baseUrl;
    private int $chainId;

    // Common BEP-20 token contracts on BNB Smart Chain (BSC)
    private const CONTRACT_USDT = '0x55d398326f99059ff775485246999027b3197955'; // Binance-Peg BSC-USD (18 decimals)
    private const CONTRACT_USDC = '0x8ac76a51cc950d9822d68b83fe1ad97b32cd580d'; // Binance-Peg USDC (18 decimals)

    public function __construct(?string $apiKey = null, ?string $baseUrl = null, ?int $chainId = null)
    {
        $this->apiKey = $apiKey ?? config('services.bscscan.api_key', 'TCMFF1ZWPKXQ6V4PETA2FQRCBGCC2WT686');
        $this->baseUrl = $baseUrl ?? config('services.bscscan.base_url', 'https://api.etherscan.io/v2/api');
        $this->chainId = $chainId ?? (int)config('services.bscscan.chainid', 56);
    }

    public function getName(): string
    {
        return 'bscscan_official_api';
    }

    public function supportsNetwork(string $network): bool
    {
        return in_array(strtolower(trim($network)), ['bsc', 'bnb', 'bep20']);
    }

    public function fetchWalletData(string $network, string $address): WalletOnchainData
    {
        $address = strtolower(trim($address));

        try {
            $client = Http::timeout(12)->baseUrl($this->baseUrl);

            // 1. Fetch BNB Price (or default ~600)
            $bnbPriceUsd = 600.0;
            try {
                $priceResp = $client->get('', [
                    'chainid' => $this->chainId,
                    'module' => 'stats',
                    'action' => 'bnbprice',
                    'apikey' => $this->apiKey,
                ]);
                if ($priceResp->successful() && $priceResp->json('status') === '1') {
                    $bnbPriceUsd = (float) ($priceResp->json('result.ethusd') ?? $priceResp->json('result.bnbusd') ?? 600.0);
                }
            } catch (\Throwable $e) {
                Log::debug("BscScan price fetch fallback: {$e->getMessage()}");
            }

            // 2. Fetch Native BNB Balance
            $bnbBalance = 0.0;
            $balResp = $client->get('', [
                'chainid' => $this->chainId,
                'module' => 'account',
                'action' => 'balance',
                'address' => $address,
                'tag' => 'latest',
                'apikey' => $this->apiKey,
            ]);

            if ($balResp->successful() && $balResp->json('status') === '1') {
                $weiStr = (string) $balResp->json('result');
                $bnbBalance = (float) bcdiv($weiStr, '1000000000000000000', 8);
            }

            $bnbValueUsd = round($bnbBalance * $bnbPriceUsd, 2);

            // 3. Fetch BEP-20 Token Balances (USDT & USDC on BSC use 18 decimals)
            $usdtBalance = $this->fetchTokenBalance($client, $address, self::CONTRACT_USDT, 18);
            $usdcBalance = $this->fetchTokenBalance($client, $address, self::CONTRACT_USDC, 18);

            $tokens = [
                'BNB' => [
                    'amount' => $bnbBalance,
                    'usd_price' => $bnbPriceUsd,
                    'usd_value' => $bnbValueUsd,
                ],
                'USDT' => [
                    'amount' => $usdtBalance,
                    'usd_price' => 1.0,
                    'usd_value' => round($usdtBalance, 2),
                ],
                'USDC' => [
                    'amount' => $usdcBalance,
                    'usd_price' => 1.0,
                    'usd_value' => round($usdcBalance, 2),
                ],
            ];

            $visibleTotalBalanceUsd = round($bnbValueUsd + $usdtBalance + $usdcBalance, 2);

            // 4. Fetch Normal Transactions
            $txResp = $client->get('', [
                'chainid' => $this->chainId,
                'module' => 'account',
                'action' => 'txlist',
                'address' => $address,
                'startblock' => 0,
                'endblock' => 99999999,
                'page' => 1,
                'offset' => 50,
                'sort' => 'desc',
                'apikey' => $this->apiKey,
            ]);

            $transactions = [];
            $firstActivity = null;
            $lastActivity = null;

            if ($txResp->successful() && is_array($txResp->json('result'))) {
                $rawTxs = $txResp->json('result');
                foreach ($rawTxs as $tx) {
                    if (!isset($tx['hash'])) continue;

                    $timestamp = isset($tx['timeStamp']) ? (int) $tx['timeStamp'] : time();
                    $dt = Carbon::createFromTimestamp($timestamp);

                    if (!$lastActivity || $dt->greaterThan($lastActivity)) {
                        $lastActivity = $dt;
                    }
                    if (!$firstActivity || $dt->lessThan($firstActivity)) {
                        $firstActivity = $dt;
                    }

                    $from = strtolower($tx['from'] ?? '');
                    $to = strtolower($tx['to'] ?? '');
                    $direction = ($to === $address) ? 'in' : 'out';
                    $valBnb = (float) bcdiv((string)($tx['value'] ?? 0), '1000000000000000000', 6);
                    $valUsd = round($valBnb * $bnbPriceUsd, 2);

                    $transactions[] = new TransactionDTO(
                        txHash: $tx['hash'],
                        network: 'bsc',
                        fromAddress: $from,
                        toAddress: $to,
                        amount: $valBnb,
                        tokenSymbol: 'BNB',
                        usdValue: $valUsd,
                        timestamp: $dt,
                        direction: $direction,
                        contractAddress: null,
                        isContractCall: !empty($tx['input']) && $tx['input'] !== '0x'
                    );
                }
            }

            // 5. Fetch BEP-20 Token Transfers
            $tokenTxResp = $client->get('', [
                'chainid' => $this->chainId,
                'module' => 'account',
                'action' => 'tokentx',
                'address' => $address,
                'page' => 1,
                'offset' => 50,
                'sort' => 'desc',
                'apikey' => $this->apiKey,
            ]);

            if ($tokenTxResp->successful() && is_array($tokenTxResp->json('result'))) {
                $rawTokenTxs = $tokenTxResp->json('result');
                foreach ($rawTokenTxs as $tx) {
                    if (!isset($tx['hash'])) continue;

                    $timestamp = isset($tx['timeStamp']) ? (int) $tx['timeStamp'] : time();
                    $dt = Carbon::createFromTimestamp($timestamp);

                    if (!$lastActivity || $dt->greaterThan($lastActivity)) {
                        $lastActivity = $dt;
                    }
                    if (!$firstActivity || $dt->lessThan($firstActivity)) {
                        $firstActivity = $dt;
                    }

                    $from = strtolower($tx['from'] ?? '');
                    $to = strtolower($tx['to'] ?? '');
                    $direction = ($to === $address) ? 'in' : 'out';
                    $symbol = strtoupper($tx['tokenSymbol'] ?? 'BEP20');
                    $decimals = (int) ($tx['tokenDecimal'] ?? 18);
                    $divisor = bcpow('10', (string) max(1, $decimals));
                    $tokenAmt = (float) bcdiv((string)($tx['value'] ?? 0), $divisor, 4);

                    $valUsd = in_array($symbol, ['USDT', 'USDC', 'BUSD', 'DAI']) 
                        ? round($tokenAmt, 2) 
                        : round($tokenAmt * ($symbol === 'WBNB' ? $bnbPriceUsd : 1.0), 2);

                    $transactions[] = new TransactionDTO(
                        txHash: $tx['hash'],
                        network: 'bsc',
                        fromAddress: $from,
                        toAddress: $to,
                        amount: $tokenAmt,
                        tokenSymbol: $symbol,
                        usdValue: $valUsd,
                        timestamp: $dt,
                        direction: $direction,
                        contractAddress: $tx['contractAddress'] ?? null,
                        isContractCall: true
                    );
                }
            }

            return new WalletOnchainData(
                address: $address,
                network: 'bsc',
                nativeBalance: $bnbBalance,
                nativeBalanceUsd: $bnbValueUsd,
                visibleTotalBalanceUsd: $visibleTotalBalanceUsd,
                tokens: $tokens,
                transactions: $transactions,
                firstActivityAt: $firstActivity ?? Carbon::now(),
                lastActivityAt: $lastActivity ?? Carbon::now(),
                totalTxCount: count($transactions),
                providerName: 'bscscan_official_api',
                providerConfidence: 0.98
            );
        } catch (\Throwable $e) {
            Log::warning("BscScanProvider failed for {$address}: " . $e->getMessage());
            throw $e;
        }
    }

    private function fetchTokenBalance($client, string $address, string $contract, int $decimals): float
    {
        try {
            $resp = $client->get('', [
                'chainid' => $this->chainId,
                'module' => 'account',
                'action' => 'tokenbalance',
                'contractaddress' => $contract,
                'address' => $address,
                'tag' => 'latest',
                'apikey' => $this->apiKey,
            ]);

            if ($resp->successful() && $resp->json('status') === '1') {
                $rawVal = (string) $resp->json('result');
                $divisor = bcpow('10', (string) $decimals);
                return (float) bcdiv($rawVal, $divisor, 4);
            }
        } catch (\Throwable $e) {
            Log::debug("Token balance fetch error for {$contract}: {$e->getMessage()}");
        }

        return 0.0;
    }
}
