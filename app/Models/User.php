<?php

namespace App\Models;

use App\Enums\KycStatus;
use App\Enums\NotificationCategory;
use App\Enums\ReviewableType;
use App\Enums\ReviewStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'password', 'role', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function businessProfile(): HasOne
    {
        return $this->hasOne(BusinessProfile::class);
    }

    public function salerProfile(): HasOne
    {
        return $this->hasOne(SalerProfile::class);
    }

    public function verifierProfile(): HasOne
    {
        return $this->hasOne(VerifierProfile::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(UserVerification::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function warehouseDeposits(): HasMany
    {
        return $this->hasMany(WarehouseProduct::class, 'seller_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function wishlist(): HasOne
    {
        return $this->hasOne(Wishlist::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function vendorOrders(): HasMany
    {
        return $this->hasMany(VendorOrder::class, 'vendor_id');
    }

    public function paymentSettings(): HasOne
    {
        return $this->hasOne(VendorPaymentSetting::class);
    }

    public function paymentAccounts(): HasMany
    {
        return $this->hasMany(VendorPaymentAccount::class);
    }

    public function invoicesAsSeller(): HasMany
    {
        return $this->hasMany(Invoice::class, 'seller_id');
    }

    public function invoicesAsBuyer(): HasMany
    {
        return $this->hasMany(Invoice::class, 'buyer_id');
    }

    public function reviewsWritten(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsAsSeller(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewable_id')->where('reviewable_type', ReviewableType::Seller);
    }

    public function approvedReviewsAsSeller(): HasMany
    {
        return $this->reviewsAsSeller()->where('status', ReviewStatus::Approved);
    }

    public function conversationsAsBuyer(): HasMany
    {
        return $this->hasMany(ChatConversation::class, 'buyer_id');
    }

    public function conversationsAsSeller(): HasMany
    {
        return $this->hasMany(ChatConversation::class, 'seller_id');
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(SellerWallet::class);
    }

    public function sellerTransactions(): HasMany
    {
        return $this->hasMany(SellerTransaction::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(SellerPayout::class);
    }

    public function commissionRecords(): HasMany
    {
        return $this->hasMany(CommissionRecord::class, 'seller_id');
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    /**
     * Opt-out model — no stored preference means email is enabled.
     */
    public function wantsEmailFor(NotificationCategory $category): bool
    {
        return $this->relationLoaded('notificationPreferences')
            ? ($this->notificationPreferences->firstWhere('category', $category)?->email_enabled ?? true)
            : ($this->notificationPreferences()->where('category', $category)->value('email_enabled') ?? true);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->where('status', SubscriptionStatus::Active)->latestOfMany();
    }

    public function latestVerification(): HasOne
    {
        return $this->hasOne(UserVerification::class)->latestOfMany();
    }

    public function isKycApproved(): bool
    {
        return $this->latestVerification?->status === KycStatus::Approved;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isVerifier(): bool
    {
        return $this->role === UserRole::Verifier;
    }

    public function isBusiness(): bool
    {
        return $this->role === UserRole::Business;
    }

    public function isSaler(): bool
    {
        return $this->role === UserRole::Saler;
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::Customer;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }
}
