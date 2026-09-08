<?php

namespace App\Services\Blockchain;

use App\Services\Blockchain\DTO\TransactionDTO;
use App\Services\Blockchain\DTO\WalletOnchainData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SolanaProvider implements BlockchainProviderInterface
{
    private string $rpcUrl;
    private string $apiKey;

    // Common SPL Token Mints on Solana Mainnet
    private const MINT_USDT = 'Es9vMFrzaCERmJfrF4H2FYD4KCoNkY11McCe8BenwNYB';
    private const MINT_USDC = 'EPjFWdd5AufqSSqeM2qN1xzybapC8G4wEGGkZwyTDt1v';

    public function __construct(?string $apiKey = null, ?string $rpcUrl = null)
    {
        $this->apiKey = $apiKey ?? config('services.solana.api_key', 'af45c17e-02d2-4231-838f-d4e1e002477c');
        $this->rpcUrl = $rpcUrl ?? config('services.solana.rpc_url', 'https://mainnet.helius-rpc.com/?api-key=af45c17e-02d2-4231-838f-d4e1e002477c');
    }

    public function getName(): string
    {
        return 'helius_solana_rpc';
    }

    public function supportsNetwork(string $network): bool
    {
        return in_array(strtolower(trim($network)), ['solana', 'sol']);
    }

    public function fetchWalletData(string $network, string $address): WalletOnchainData
    {
        $address = trim($address);

        try {
            $client = Http::timeout(12);

            // 1. Fetch current SOL Price
            $solPriceUsd = 140.0;
            try {
                $priceResp = $client->get('https://api.coingecko.com/api/v3/simple/price', [
                    'ids' => 'solana',
                    'vs_currencies' => 'usd',
                ]);
                if ($priceResp->successful() && $priceResp->json('solana.usd') !== null) {
                    $solPriceUsd = (float)$priceResp->json('solana.usd');
                }
            } catch (\Throwable $e) {
                Log::debug("Solana price fetch fallback: {$e->getMessage()}");
            }

            // 2. Fetch Native SOL Balance (in Lamports)
            $solBalance = 0.0;
            $balResp = $client->post($this->rpcUrl, [
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'getBalance',
                'params' => [$address],
            ]);

            if ($balResp->successful() && $balResp->json('result.value') !== null) {
                $lamports = (float)$balResp->json('result.value');
                $solBalance = $lamports / 1e9;
            }

            $solValueUsd = round($solBalance * $solPriceUsd, 2);

            // 3. Fetch SPL Token Accounts
            $usdtBalance = 0.0;
            $usdcBalance = 0.0;

            $tokensResp = $client->post($this->rpcUrl, [
                'jsonrpc' => '2.0',
                'id' => 2,
                'method' => 'getTokenAccountsByOwner',
                'params' => [
                    $address,
                    ['programId' => 'TokenkegQfeZyiNwAJbNbGKPFXCWuBvf9Ss623VQ5DA'],
                    ['encoding' => 'jsonParsed'],
                ],
            ]);

            if ($tokensResp->successful() && is_array($tokensResp->json('result.value'))) {
                foreach ($tokensResp->json('result.value') as $accountInfo) {
                    $parsed = $accountInfo['account']['data']['parsed']['info'] ?? null;
                    if (!$parsed) continue;

                    $mint = $parsed['mint'] ?? '';
                    $amount = (float)($parsed['tokenAmount']['uiAmount'] ?? 0);

                    if ($mint === self::MINT_USDT) {
                        $usdtBalance += $amount;
                    } elseif ($mint === self::MINT_USDC) {
                        $usdcBalance += $amount;
                    }
                }
            }

            $tokens = [
                'SOL' => [
                    'amount' => $solBalance,
                    'usd_price' => $solPriceUsd,
                    'usd_value' => $solValueUsd,
                ],
                'USDT' => [
                    'amount' => round($usdtBalance, 2),
                    'usd_price' => 1.0,
                    'usd_value' => round($usdtBalance, 2),
                ],
                'USDC' => [
                    'amount' => round($usdcBalance, 2),
                    'usd_price' => 1.0,
                    'usd_value' => round($usdcBalance, 2),
                ],
            ];

            $visibleTotalBalanceUsd = round($solValueUsd + $usdtBalance + $usdcBalance, 2);

            // 4. Fetch Recent Transaction Signatures
            $sigResp = $client->post($this->rpcUrl, [
                'jsonrpc' => '2.0',
                'id' => 3,
                'method' => 'getSignaturesForAddress',
                'params' => [
                    $address,
                    ['limit' => 25],
                ],
            ]);

            $transactions = [];
            $firstActivity = null;
            $lastActivity = null;

            if ($sigResp->successful() && is_array($sigResp->json('result'))) {
                $sigs = $sigResp->json('result');
                foreach ($sigs as $sig) {
                    $blockTime = $sig['blockTime'] ?? null;
                    $dt = $blockTime ? Carbon::createFromTimestamp($blockTime) : Carbon::now();

                    if (!$lastActivity || $dt->greaterThan($lastActivity)) {
                        $lastActivity = $dt;
                    }
                    if (!$firstActivity || $dt->lessThan($firstActivity)) {
                        $firstActivity = $dt;
                    }

                    $transactions[] = new TransactionDTO(
                        txHash: $sig['signature'] ?? ('sol_sig_' . uniqid()),
                        network: 'solana',
                        fromAddress: $address,
                        toAddress: 'solana_network',
                        amount: 0.0,
                        tokenSymbol: 'SOL',
                        usdValue: 0.0,
                        timestamp: $dt,
                        direction: 'out',
                        contractAddress: null,
                        isContractCall: false
                    );
                }
            }

            return new WalletOnchainData(
                address: $address,
                network: 'solana',
                nativeBalance: $solBalance,
                nativeBalanceUsd: $solValueUsd,
                visibleTotalBalanceUsd: $visibleTotalBalanceUsd,
                tokens: $tokens,
                transactions: $transactions,
                firstActivityAt: $firstActivity ?? Carbon::now()->subMonths(1),
                lastActivityAt: $lastActivity ?? Carbon::now(),
                totalTxCount: count($transactions),
                providerName: 'helius_solana_rpc',
                providerConfidence: 0.98
            );
        } catch (\Throwable $e) {
            Log::warning("SolanaProvider failed for {$address}: " . $e->getMessage());
            throw $e;
        }
    }
}
