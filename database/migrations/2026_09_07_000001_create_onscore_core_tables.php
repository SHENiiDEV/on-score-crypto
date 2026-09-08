<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Accounts (B2B Clients)
        Schema::create('accounts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('credit_balance')->default(0);
            $table->string('status')->default('active'); // active, suspended, trial
            $table->string('webhook_url')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        // 2. Users update (Add account_id & role)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUlid('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('role')->default('user'); // superadmin, admin, user
            $table->string('status')->default('active');
            $table->timestamp('last_login_at')->nullable();
        });

        // 3. API Clients (Keys & Secrets)
        Schema::create('api_clients', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->string('name');
            $table->string('key_id')->unique(); // e.g. ons_live_...
            $table->string('secret_hash');
            $table->json('scopes')->nullable(); // ["analyses:read", "analyses:write"]
            $table->integer('rate_limit_per_minute')->default(120);
            $table->string('status')->default('active'); // active, revoked
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

        // 4. Immutable Credit Ledger
        Schema::create('credit_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->integer('delta'); // +100 or -3
            $table->string('type'); // topup, deduction, reservation, charge, refund, correction
            $table->integer('balance_after');
            $table->string('reference_id')->nullable(); // analysis_id, batch_id, payment_ref
            $table->string('reason');
            $table->string('actor')->default('system'); // user_id or system
            $table->timestamp('created_at')->useCurrent();

            $table->index(['account_id', 'created_at']);
        });

        // 5. Entities (CEX, Gambling, DEX, etc.)
        Schema::create('entities', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name'); // Bybit, Binance, Stake.com, Rollbit
            $table->string('slug')->unique();
            $table->string('category'); // cex, gambling, dex, bridge, payment_processor, other
            $table->string('subtype')->nullable(); // casino, betting, poker, hybrid, exchange_hotwallet, exchange_coldwallet
            $table->boolean('is_custodial_cex')->default(false); // If true, exclude collective pool balances
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // 6. Entity Addresses (Labeled Wallets & Contracts)
        Schema::create('entity_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('entity_id')->constrained('entities')->cascadeOnDelete();
            $table->string('network'); // tron, ethereum, bsc, solana, bitcoin
            $table->string('address');
            $table->string('normalized_address');
            $table->string('label')->nullable(); // e.g. "Bybit Hot Wallet 4", "Stake TRC20 Deposit Vault"
            $table->string('cluster')->nullable();
            $table->string('source')->default('internal'); // arkham, osint, partner, internal, manual
            $table->decimal('confidence', 3, 2)->default(1.00); // 0.00 to 1.00
            $table->text('evidence')->nullable();
            $table->string('status')->default('active'); // active, deprecated, under_review
            $table->timestamp('last_validated_at')->nullable();
            $table->timestamps();

            $table->unique(['network', 'normalized_address']);
            $table->index(['network', 'normalized_address']);
        });

        // 7. Wallets Registry
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('network'); // tron, ethereum, bsc, solana, bitcoin
            $table->string('address');
            $table->string('normalized_address');
            $table->boolean('is_known_entity')->default(false);
            $table->foreignUlid('entity_id')->nullable()->constrained('entities')->nullOnDelete();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['network', 'normalized_address']);
        });

        // 8. Batch Jobs
        Schema::create('batch_jobs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->integer('total_wallets')->default(0);
            $table->integer('queued_count')->default(0);
            $table->integer('processing_count')->default(0);
            $table->integer('completed_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->string('status')->default('queued'); // queued, processing, completed, partial, failed
            $table->timestamps();
        });

        // 9. Analyses
        Schema::create('analyses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignUlid('api_client_id')->nullable()->constrained('api_clients')->nullOnDelete();
            $table->foreignUlid('batch_job_id')->nullable()->constrained('batch_jobs')->nullOnDelete();
            $table->foreignId('wallet_id')->constrained('wallets')->cascadeOnDelete();
            $table->string('network');
            $table->string('address');
            $table->string('external_player_id')->nullable();
            $table->string('deposit_tx_hash')->nullable();
            $table->decimal('deposit_amount', 24, 8)->nullable();
            $table->string('deposit_asset')->nullable();
            $table->timestamp('deposit_timestamp')->nullable();
            
            $table->string('status')->default('queued'); // queued, processing, completed, partial, failed, cancelled
            $table->integer('cost_credits')->default(3);
            $table->integer('score_value')->nullable(); // 0 - 100
            $table->string('segment')->nullable(); // low_value, regular, good_player, high_value, potential_vip
            $table->decimal('confidence', 3, 2)->nullable();
            $table->string('model_version')->default('WPS-v1.0');
            
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'created_at']);
            $table->index(['network', 'address']);
            $table->index(['score_value', 'segment']);
        });

        // 10. Immutable Analysis Snapshots
        Schema::create('analysis_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('analysis_id')->constrained('analyses')->cascadeOnDelete()->unique();
            $table->json('wallet_overview');
            $table->json('balance_assets');
            $table->json('turnover');
            $table->json('gambling_intelligence');
            $table->json('counterparties');
            $table->json('score_breakdown');
            $table->json('provenance');
            $table->json('warnings')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_snapshots');
        Schema::dropIfExists('analyses');
        Schema::dropIfExists('batch_jobs');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('entity_addresses');
        Schema::dropIfExists('entities');
        Schema::dropIfExists('credit_ledger');
        Schema::dropIfExists('api_clients');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn(['account_id', 'role', 'status', 'last_login_at']);
        });

        Schema::dropIfExists('accounts');
    }
};
