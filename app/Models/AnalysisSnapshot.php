<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisSnapshot extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'analysis_id',
        'wallet_overview',
        'balance_assets',
        'turnover',
        'gambling_intelligence',
        'behavioral_patterns',
        'counterparties',
        'score_breakdown',
        'provenance',
        'warnings',
        'created_at',
    ];

    protected $casts = [
        'wallet_overview' => 'array',
        'balance_assets' => 'array',
        'turnover' => 'array',
        'gambling_intelligence' => 'array',
        'behavioral_patterns' => 'array',
        'counterparties' => 'array',
        'score_breakdown' => 'array',
        'provenance' => 'array',
        'warnings' => 'array',
        'created_at' => 'datetime',
    ];

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(Analysis::class);
    }
}
