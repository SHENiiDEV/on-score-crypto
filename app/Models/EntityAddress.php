<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntityAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_id',
        'network',
        'address',
        'normalized_address',
        'label',
        'cluster',
        'source',
        'confidence',
        'evidence',
        'status',
        'last_validated_at',
    ];

    protected $casts = [
        'confidence' => 'float',
        'evidence' => 'array',
        'last_validated_at' => 'datetime',
    ];

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }
}
