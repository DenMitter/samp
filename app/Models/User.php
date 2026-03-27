<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $appends = ['is_admin'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function isAdmin(): Attribute
    {
        return Attribute::get(fn () => $this->hasAdminAccess());
    }

    public function createdOffers(): HasMany
    {
        return $this->hasMany(Offer::class, 'creator_id');
    }

    public function buyingOffers(): HasMany
    {
        return $this->hasMany(Offer::class, 'buyer_id');
    }

    public function sellingOffers(): HasMany
    {
        return $this->hasMany(Offer::class, 'seller_id');
    }

    public function buyerTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'buyer_id');
    }

    public function sellerTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'seller_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payer_id');
    }

    public function hasAdminAccess(): bool
    {
        $allowedEmails = collect(explode(',', (string) env('ADMIN_EMAILS', 'admin@admin.com')))
            ->map(fn (string $item) => mb_strtolower(trim($item)))
            ->filter()
            ->values();

        return $allowedEmails->contains(mb_strtolower(trim($this->email)));
    }
}
