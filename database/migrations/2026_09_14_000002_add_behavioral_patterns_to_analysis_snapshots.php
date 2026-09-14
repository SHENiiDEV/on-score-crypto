<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analysis_snapshots', function (Blueprint $table) {
            $table->json('behavioral_patterns')->nullable()->after('gambling_intelligence');
        });
    }

    public function down(): void
    {
        Schema::table('analysis_snapshots', function (Blueprint $table) {
            $table->dropColumn('behavioral_patterns');
        });
    }
};
