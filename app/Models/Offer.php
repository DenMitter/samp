<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'uuid',
    'creator_id',
    'buyer_id',
    'seller_id',
    'title',
    'description',
    'asset_type',
    'currency',
    'amount',
    'status',
    'expires_at',
    'accepted_at',
    'meta',
])]
class Offer extends Model
{
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $query = $this->newQuery();

        if ($field) {
            return $query->where($field, $value)->firstOrFail();
        }

        $normalizedValue = trim((string) $value);

        if ($normalizedValue !== '' && ctype_digit($normalizedValue)) {
            return $query->whereKey((int) $normalizedValue)->firstOrFail();
        }

        return $query->where('uuid', $normalizedValue)->firstOrFail();
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }
}
