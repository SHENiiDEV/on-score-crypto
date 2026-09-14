<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->unsignedInteger('max_api_keys')->default(5)->after('status');
            $table->unsignedInteger('default_rate_limit')->default(120)->after('max_api_keys');
            $table->unsignedInteger('low_balance_threshold')->default(100)->after('default_rate_limit');
            $table->string('contact_email')->nullable()->after('low_balance_threshold');
            $table->boolean('self_service_keys')->default(true)->after('contact_email');
        });

        Schema::table('api_clients', function (Blueprint $table) {
            $table->string('created_by')->nullable()->after('status');
            $table->timestamp('revoked_at')->nullable()->after('created_by');
            $table->string('last_used_ip', 45)->nullable()->after('last_used_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn([
                'max_api_keys',
                'default_rate_limit',
                'low_balance_threshold',
                'contact_email',
                'self_service_keys',
            ]);
        });

        Schema::table('api_clients', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'revoked_at', 'last_used_ip']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('must_change_password');
        });
    }
};
