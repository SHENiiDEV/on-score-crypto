<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditLedger extends Model
{
    public $timestamps = false;
    protected $table = 'credit_ledger';

    protected $fillable = [
        'account_id',
        'delta',
        'type',
        'balance_after',
        'reference_id',
        'reason',
        'actor',
        'created_at',
    ];

    protected $casts = [
        'delta' => 'integer',
        'balance_after' => 'integer',
        'created_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
