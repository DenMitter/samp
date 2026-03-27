<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'uuid',
    'offer_id',
    'buyer_id',
    'seller_id',
    'reference',
    'currency',
    'amount',
    'inspection_period_days',
    'status',
    'payment_status',
    'approved_at',
    'released_at',
    'meta',
])]
class Transaction extends Model
{
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        return $this->newQuery()
            ->when($field, fn ($query) => $query->where($field, $value))
            ->when(! $field, fn ($query) => $query
                ->where('id', $value)
                ->orWhere('uuid', $value))
            ->firstOrFail();
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'released_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
