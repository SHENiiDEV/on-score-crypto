<?php

namespace App\Services\Blockchain;

use App\Services\Blockchain\DTO\TransactionDTO;
use App\Services\Blockchain\DTO\WalletOnchainData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EtherscanProvider implements BlockchainProviderInterface
{
    private string $apiKey;
    private string $baseUrl;

    // Common ERC-20 token contracts on Ethereum Mainnet
    private const CONTRACT_USDT = '0xdac17f958d2ee523a2206206994597c13d831ec7';
    private const CONTRACT_USDC = '0xa0b86991c6218b36c1d19d4a2e9eb0ce3606eb48';
    private const CONTRACT_DAI  = '0x6b175474e89094c44da98b954eedeac495271d0f';

    public function __construct(?string $apiKey = null, ?string $baseUrl = null)
    {
        $this->apiKey = $apiKey ?? config('services.etherscan.api_key', 'TCMFF1ZWPKXQ6V4PETA2FQRCBGCC2WT686');
        $this->baseUrl = $baseUrl ?? config('services.etherscan.base_url', 'https://api.etherscan.io/v2/api');
    }

    public function getName(): string
    {
        return 'etherscan_official_api';
    }

    public function supportsNetwork(string $network): bool
    {
        return in_array(strtolower($network), ['ethereum', 'eth', 'erc20']);
    }

    public function fetchWalletData(string $network, string $address): WalletOnchainData
    {
        $address = strtolower(trim($address));

        try {
            $client = Http::timeout(12)->baseUrl($this->baseUrl);

            // 1. Fetch current ETH USD Price
            $ethPriceUsd = 2500.0;
            try {
                $priceResp = $client->get('', [
                    'chainid' => 1,
                    'module' => 'stats',
                    'action' => 'ethprice',
                    'apikey' => $this->apiKey,
                ]);
                if ($priceResp->successful() && $priceResp->json('status') === '1') {
                    $ethPriceUsd = (float) ($priceResp->json('result.ethusd') ?? 2500.0);
                }
            } catch (\Throwable $e) {
                Log::debug("Etherscan price fetch fallback: {$e->getMessage()}");
            }

            // 2. Fetch Native ETH Balance (in Wei)
            $ethBalance = 0.0;
            $balResp = $client->get('', [
                'chainid' => 1,
                'module' => 'account',
                'action' => 'balance',
                'address' => $address,
                'tag' => 'latest',
                'apikey' => $this->apiKey,
            ]);

            if ($balResp->successful() && $balResp->json('status') === '1') {
                $weiStr = (string) $balResp->json('result');
                $ethBalance = (float) bcdiv($weiStr, '1000000000000000000', 8);
            }

            $ethValueUsd = round($ethBalance * $ethPriceUsd, 2);

            // 3. Fetch Token Balances (USDT & USDC ERC-20)
            $usdtBalance = $this->fetchTokenBalance($client, $address, self::CONTRACT_USDT, 6);
            $usdcBalance = $this->fetchTokenBalance($client, $address, self::CONTRACT_USDC, 6);
            $daiBalance  = $this->fetchTokenBalance($client, $address, self::CONTRACT_DAI, 18);

            $tokens = [
                'ETH' => [
                    'amount' => $ethBalance,
                    'usd_price' => $ethPriceUsd,
                    'usd_value' => $ethValueUsd,
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
                'DAI' => [
                    'amount' => $daiBalance,
                    'usd_price' => 1.0,
                    'usd_value' => round($daiBalance, 2),
                ],
            ];

            $visibleTotalBalanceUsd = round($ethValueUsd + $usdtBalance + $usdcBalance + $daiBalance, 2);

            // 4. Fetch Normal Transactions
            $txResp = $client->get('', [
                'chainid' => 1,
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
                    $valEth = (float) bcdiv((string)($tx['value'] ?? 0), '1000000000000000000', 6);
                    $valUsd = round($valEth * $ethPriceUsd, 2);

                    $transactions[] = new TransactionDTO(
                        txHash: $tx['hash'],
                        network: 'ethereum',
                        fromAddress: $from,
                        toAddress: $to,
                        amount: $valEth,
                        tokenSymbol: 'ETH',
                        usdValue: $valUsd,
                        timestamp: $dt,
                        direction: $direction,
                        contractAddress: null,
                        isContractCall: !empty($tx['input']) && $tx['input'] !== '0x'
                    );
                }
            }

            // 5. Fetch ERC-20 Token Transfers
            $tokenTxResp = $client->get('', [
                'chainid' => 1,
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
                    $symbol = strtoupper($tx['tokenSymbol'] ?? 'ERC20');
                    $decimals = (int) ($tx['tokenDecimal'] ?? 18);
                    $divisor = bcpow('10', (string) max(1, $decimals));
                    $tokenAmt = (float) bcdiv((string)($tx['value'] ?? 0), $divisor, 4);

                    $valUsd = in_array($symbol, ['USDT', 'USDC', 'DAI', 'BUSD']) 
                        ? round($tokenAmt, 2) 
                        : round($tokenAmt * ($symbol === 'WETH' ? $ethPriceUsd : 1.0), 2);

                    $transactions[] = new TransactionDTO(
                        txHash: $tx['hash'],
                        network: 'ethereum',
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
                network: 'ethereum',
                nativeBalance: $ethBalance,
                nativeBalanceUsd: $ethValueUsd,
                visibleTotalBalanceUsd: $visibleTotalBalanceUsd,
                tokens: $tokens,
                transactions: $transactions,
                firstActivityAt: $firstActivity ?? Carbon::now(),
                lastActivityAt: $lastActivity ?? Carbon::now(),
                totalTxCount: count($transactions),
                providerName: 'etherscan_official_api',
                providerConfidence: 0.98
            );
        } catch (\Throwable $e) {
            Log::warning("EtherscanProvider failed for {$address}: " . $e->getMessage());
            throw $e;
        }
    }

    private function fetchTokenBalance($client, string $address, string $contract, int $decimals): float
    {
        try {
            $resp = $client->get('', [
                'chainid' => 1,
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

