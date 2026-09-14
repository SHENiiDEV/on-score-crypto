<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\ApiClient;
use App\Models\CreditLedger;
use App\Models\Entity;
use App\Models\EntityAddress;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default SuperAdmin
        $admin = User::create([
            'name' => 'OnScore Admin',
            'email' => 'admin@onscore.io',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        // 2. Create Demo B2B Client Account (e.g. Casino Operator "Apex Gaming Group")
        $demoAccount = Account::create([
            'name' => 'Apex Gaming Group',
            'slug' => 'apex-gaming',
            'credit_balance' => 1500,
            'status' => 'active',
            'max_api_keys' => 5,
            'default_rate_limit' => 300,
            'low_balance_threshold' => 250,
            'contact_email' => 'crm@apexgaming.com',
            'self_service_keys' => true,
            'scoring_rules' => [
                'super_vip_threshold_usd' => 400000,
                'potential_vip_threshold_usd' => 100000,
                'high_value_threshold_usd' => 25000,
                'custom_rules' => [
                    ['metric' => 'gambling_turnover_usd', 'operator' => '>=', 'value' => 50000, 'tag' => 'HIGH_ROLLER', 'tier' => null],
                    ['metric' => 'transactions_count', 'operator' => '<', 'value' => 5, 'tag' => 'FRESH_WALLET', 'tier' => null],
                ],
            ],
            'webhook_url' => 'https://api.apexgaming.test/webhooks/onscore',
            'webhook_secret' => 'whsec_' . Str::random(32),
            'settings' => [
                'vip_threshold' => 85,
                'min_turnover_usd' => 10000,
                'alert_channels' => ['telegram', 'slack'],
            ],
        ]);

        User::create([
            'account_id' => $demoAccount->id,
            'name' => 'CRM Manager',
            'email' => 'crm@apexgaming.com',
            'password' => Hash::make('password123'),
            'role' => 'merchant_owner',
            'status' => 'active',
        ]);

        User::create([
            'account_id' => $demoAccount->id,
            'name' => 'VIP Host',
            'email' => 'vip@apexgaming.com',
            'password' => Hash::make('password123'),
            'role' => 'merchant_member',
            'status' => 'active',
        ]);

        // 3. Create Demo API Client for Apex Gaming
        $keyId = 'ons_live_apex_demo_key_998877';
        $plainSecret = 'ons_sec_demo_secret_apex_778899aabbcc';
        ApiClient::create([
            'account_id' => $demoAccount->id,
            'name' => 'Production Casino Core API',
            'key_id' => $keyId,
            'secret_hash' => hash('sha256', $plainSecret),
            'scopes' => ['analyses:read', 'analyses:write', 'batches:read', 'batches:write', 'credits:read'],
            'rate_limit_per_minute' => 300,
            'status' => 'active',
        ]);

        // 4. Record Initial Ledger Top-Up
        CreditLedger::create([
            'account_id' => $demoAccount->id,
            'delta' => 1500,
            'type' => 'topup',
            'balance_after' => 1500,
            'reference_id' => 'INIT_PROVISION_2026',
            'reason' => 'Welcome B2B Credits Pack for Apex Gaming Group',
            'actor' => 'admin@onscore.io',
            'created_at' => now(),
        ]);

        // 5. Seed Entities & Known Addresses (CEX with is_custodial_cex = true, Gambling, DEX)

        // --- CEX: Bybit ---
        $bybit = Entity::create([
            'name' => 'Bybit',
            'slug' => 'bybit',
            'category' => 'cex',
            'subtype' => 'exchange_hotwallet',
            'is_custodial_cex' => true,
            'status' => 'active',
        ]);
        $bybitAddresses = [
            ['network' => 'tron', 'address' => 'TLyqzVGLV1srkB7dToTAnYg9auqwTmTX71', 'label' => 'Bybit Hot Wallet 1 (TRON)'],
            ['network' => 'tron', 'address' => 'TV6MuMXWWmbtBhyLpqGzT6CQdMT9Jwxigo', 'label' => 'Bybit Hot Wallet 2 (TRON)'],
            ['network' => 'tron', 'address' => 'TAUNjq52oPZ9zB9e7tWup2jT2o6u586868', 'label' => 'Bybit Deposit Sweeper Pool'],
            ['network' => 'ethereum', 'address' => '0xf89d7b9c22de6ba847b74e644b4860b0ff0ea0f0', 'label' => 'Bybit Hot Wallet 1 (ETH)'],
            ['network' => 'bsc', 'address' => '0x88a14d17d5ea4a6d1a9386d79042b4eb48184f4f', 'label' => 'Bybit Hot Wallet (BSC)'],
        ];
        foreach ($bybitAddresses as $addr) {
            EntityAddress::create([
                'entity_id' => $bybit->id,
                'network' => $addr['network'],
                'address' => $addr['address'],
                'normalized_address' => in_array($addr['network'], ['ethereum', 'bsc']) ? strtolower($addr['address']) : $addr['address'],
                'label' => $addr['label'],
                'cluster' => 'bybit_cex_infrastructure',
                'source' => 'arkham_intelligence',
                'confidence' => 0.99,
                'status' => 'active',
                'last_validated_at' => now(),
            ]);
        }

        // --- CEX: Binance ---
        $binance = Entity::create([
            'name' => 'Binance',
            'slug' => 'binance',
            'category' => 'cex',
            'subtype' => 'exchange_hotwallet',
            'is_custodial_cex' => true,
            'status' => 'active',
        ]);
        $binanceAddresses = [
            ['network' => 'tron', 'address' => 'TPYmHEhy5n8TCEfYGqW2rPxsghSfzghPDn', 'label' => 'Binance Hot Wallet 20 (TRON)'],
            ['network' => 'tron', 'address' => 'TWd4WrZ9wn84f5x1hZhL4DHvk738ns5jwb', 'label' => 'Binance Hot Wallet 6 (TRON)'],
            ['network' => 'ethereum', 'address' => '0x28c6c06298d514db089934071355e5743bf21d60', 'label' => 'Binance 14 (ETH)'],
            ['network' => 'bsc', 'address' => '0x8894e0a0c962cb723c1976a4421c95949be2d4e3', 'label' => 'Binance Hot Wallet (BSC)'],
        ];
        foreach ($binanceAddresses as $addr) {
            EntityAddress::create([
                'entity_id' => $binance->id,
                'network' => $addr['network'],
                'address' => $addr['address'],
                'normalized_address' => in_array($addr['network'], ['ethereum', 'bsc']) ? strtolower($addr['address']) : $addr['address'],
                'label' => $addr['label'],
                'cluster' => 'binance_cex_infrastructure',
                'source' => 'arkham_intelligence',
                'confidence' => 1.00,
                'status' => 'active',
                'last_validated_at' => now(),
            ]);
        }

        // --- CEX: OKX ---
        $okx = Entity::create([
            'name' => 'OKX',
            'slug' => 'okx',
            'category' => 'cex',
            'subtype' => 'exchange_hotwallet',
            'is_custodial_cex' => true,
            'status' => 'active',
        ]);
        EntityAddress::create([
            'entity_id' => $okx->id,
            'network' => 'tron',
            'address' => 'TYu1oYjW7X9YjYh7v9n48dKjH1n8uKk199',
            'normalized_address' => 'TYu1oYjW7X9YjYh7v9n48dKjH1n8uKk199',
            'label' => 'OKX Hot Wallet 3 (TRON)',
            'cluster' => 'okx_cex_infrastructure',
            'source' => 'internal',
            'confidence' => 0.98,
            'status' => 'active',
            'last_validated_at' => now(),
        ]);

        // --- GAMBLING: Stake.com ---
        $stake = Entity::create([
            'name' => 'Stake.com',
            'slug' => 'stake-com',
            'category' => 'gambling',
            'subtype' => 'casino_sportsbook',
            'is_custodial_cex' => false,
            'status' => 'active',
        ]);
        $stakeAddresses = [
            ['network' => 'tron', 'address' => 'TXa5eQ7Vw8K8qL9pMn1bV4c7xZ3wE9rT2Y', 'label' => 'Stake.com TRON Main Inflow'],
            ['network' => 'tron', 'address' => 'TQn9Y2khEsLJW1ChVWFMSMeSTow5KcbLSE', 'label' => 'Stake.com TRON Deposit Router'],
            ['network' => 'ethereum', 'address' => '0xc564ee9f21ed8a2d8e7e76c07d402d4b88bee580', 'label' => 'Stake.com: Main Wallet'],
            ['network' => 'bsc', 'address' => '0x17c0a969df2194ad9d6f6e890b0e5bc3f58a7410', 'label' => 'Stake.com: BSC Hot Wallet'],
        ];
        foreach ($stakeAddresses as $addr) {
            EntityAddress::create([
                'entity_id' => $stake->id,
                'network' => $addr['network'],
                'address' => $addr['address'],
                'normalized_address' => in_array($addr['network'], ['ethereum', 'bsc']) ? strtolower($addr['address']) : $addr['address'],
                'label' => $addr['label'],
                'cluster' => 'stake_network',
                'source' => 'osint_and_arkham',
                'confidence' => 0.98,
                'status' => 'active',
                'last_validated_at' => now(),
            ]);
        }

        // --- GAMBLING: Rollbit ---
        $rollbit = Entity::create([
            'name' => 'Rollbit',
            'slug' => 'rollbit',
            'category' => 'gambling',
            'subtype' => 'casino_hybrid',
            'is_custodial_cex' => false,
            'status' => 'active',
        ]);
        $rollbitAddresses = [
            ['network' => 'tron', 'address' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t', 'label' => 'Rollbit TRC20 Gateway'],
            ['network' => 'ethereum', 'address' => '0x0d0707963952f2fba59dd06f2b425ace40b492fe', 'label' => 'Rollbit Casino Vault (ETH)'],
            ['network' => 'solana', 'address' => 'RollBitDepositVaultSolana1111111111111111111', 'label' => 'Rollbit Solana Gateway'],
        ];
        foreach ($rollbitAddresses as $addr) {
            EntityAddress::create([
                'entity_id' => $rollbit->id,
                'network' => $addr['network'],
                'address' => $addr['address'],
                'normalized_address' => in_array($addr['network'], ['ethereum', 'bsc']) ? strtolower($addr['address']) : $addr['address'],
                'label' => $addr['label'],
                'cluster' => 'rollbit_cluster',
                'source' => 'manual_research',
                'confidence' => 0.95,
                'status' => 'active',
                'last_validated_at' => now(),
            ]);
        }

        // --- GAMBLING: BC.Game ---
        $bcgame = Entity::create([
            'name' => 'BC.Game',
            'slug' => 'bc-game',
            'category' => 'gambling',
            'subtype' => 'casino_sportsbook',
            'is_custodial_cex' => false,
            'status' => 'active',
        ]);
        EntityAddress::create([
            'entity_id' => $bcgame->id,
            'network' => 'tron',
            'address' => 'TBCGameHotWalletTronCollector9988776655',
            'normalized_address' => 'TBCGameHotWalletTronCollector9988776655',
            'label' => 'BC.Game TRC20 Deposit Pool',
            'cluster' => 'bc_game_cluster',
            'source' => 'internal_analytics',
            'confidence' => 0.96,
            'status' => 'active',
            'last_validated_at' => now(),
        ]);

        // --- GAMBLING: Roobet ---
        $roobet = Entity::create([
            'name' => 'Roobet',
            'slug' => 'roobet',
            'category' => 'gambling',
            'subtype' => 'casino',
            'is_custodial_cex' => false,
            'status' => 'active',
        ]);
        EntityAddress::create([
            'entity_id' => $roobet->id,
            'network' => 'ethereum',
            'address' => '0x3f5ce5fbfe3e9af3971dd833d26ba9b5c936f0be',
            'normalized_address' => '0x3f5ce5fbfe3e9af3971dd833d26ba9b5c936f0be',
            'label' => 'Roobet Ethereum Cold/Hot Router',
            'cluster' => 'roobet_cluster',
            'source' => 'osint',
            'confidence' => 0.94,
            'status' => 'active',
            'last_validated_at' => now(),
        ]);

        // --- GAMBLING: 1xBit / 1xBet Crypto ---
        $onex = Entity::create([
            'name' => '1xBit / 1xBet Crypto',
            'slug' => '1xbit',
            'category' => 'gambling',
            'subtype' => 'sportsbook_betting',
            'is_custodial_cex' => false,
            'status' => 'active',
        ]);
        EntityAddress::create([
            'entity_id' => $onex->id,
            'network' => 'tron',
            'address' => 'T1xBetSportsbookDepositTRC20Vault771122',
            'normalized_address' => 'T1xBetSportsbookDepositTRC20Vault771122',
            'label' => '1xBet TRON Deposit Receiver',
            'cluster' => '1xbet_crypto',
            'source' => 'manual',
            'confidence' => 0.95,
            'status' => 'active',
            'last_validated_at' => now(),
        ]);

        // --- DEX: SunSwap (TRON) & Uniswap (ETH) ---
        $sunswap = Entity::create([
            'name' => 'SunSwap / JustLend',
            'slug' => 'sunswap',
            'category' => 'dex',
            'subtype' => 'amm_dex',
            'is_custodial_cex' => false,
            'status' => 'active',
        ]);
        EntityAddress::create([
            'entity_id' => $sunswap->id,
            'network' => 'tron',
            'address' => 'TKzxdSv2FZKQrEqkKVgp5DcwEXBEKMg2Ax',
            'normalized_address' => 'TKzxdSv2FZKQrEqkKVgp5DcwEXBEKMg2Ax',
            'label' => 'SunSwap V2 Router (TRON)',
            'cluster' => 'sunswap_defi',
            'source' => 'official',
            'confidence' => 1.00,
            'status' => 'active',
            'last_validated_at' => now(),
        ]);
    }
}
