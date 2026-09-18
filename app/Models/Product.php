<?php

namespace App\Models;

use App\Enums\PaymentRoute;
use App\Enums\ProductApprovalStatus;
use App\Enums\ProductCondition;
use App\Enums\ProductGrade;
use App\Enums\ProductPublicationStatus;
use App\Enums\ProductStatus;
use App\Enums\ReviewableType;
use App\Enums\ReviewStatus;
use App\Enums\ShippingType;
use App\Enums\VerificationStatus;
use App\Enums\WarehouseStatus;
use App\Support\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasSlug, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'brand_id',
        'title',
        'slug',
        'description',
        'short_description',
        'condition',
        'grade',
        'grade_notes',
        'status',
        'approval_status',
        'publication_status',
        'verification_status',
        'warehouse_status',
        'payment_route',
        'rejection_reason',
        'price',
        'compare_price',
        'sku',
        'quantity',
        'is_negotiable',
        'weight',
        'shipping_type',
        'shipping_flat_rate',
        'meta_title',
        'meta_description',
        'published_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'condition' => ProductCondition::class,
            'grade' => ProductGrade::class,
            'status' => ProductStatus::class,
            'approval_status' => ProductApprovalStatus::class,
            'publication_status' => ProductPublicationStatus::class,
            'verification_status' => VerificationStatus::class,
            'warehouse_status' => WarehouseStatus::class,
            'payment_route' => PaymentRoute::class,
            'shipping_type' => ShippingType::class,
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'weight' => 'decimal:2',
            'shipping_flat_rate' => 'decimal:2',
            'is_negotiable' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function slugSourceColumn(): string
    {
        return 'title';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(ProductVerification::class);
    }

    public function latestVerificationRequest(): HasOne
    {
        return $this->hasOne(ProductVerification::class)->latestOfMany();
    }

    public function gradeAssignment(): HasOne
    {
        return $this->hasOne(ProductGradeAssignment::class)->latestOfMany();
    }

    public function warehouseEntries(): HasMany
    {
        return $this->hasMany(WarehouseProduct::class);
    }

    public function latestWarehouseEntry(): HasOne
    {
        return $this->hasOne(WarehouseProduct::class)->latestOfMany();
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewable_id')->where('reviewable_type', ReviewableType::Product);
    }

    public function approvedReviews(): HasMany
    {
        return $this->reviews()->where('status', ReviewStatus::Approved);
    }

    public function isWarehoused(): bool
    {
        return $this->warehouse_status === WarehouseStatus::Stored;
    }

    public function primaryImage(): ?ProductImage
    {
        return $this->images->firstWhere('is_primary', true) ?? $this->images->first();
    }

    public function primaryImageUrl(): string
    {
        return $this->primaryImage()?->url()
            ?? $this->images->first()?->url()
            ?? $this->defaultPlaceholderImage();
    }

    public function defaultPlaceholderImage(): string
    {
        $categorySlug = strtolower($this->category?->slug ?? '');
        $title = strtolower($this->title ?? '');
        $seed = $this->id ? (int) $this->id : abs(crc32($title ?: 'openbox-product'));

        $keyword = match (true) {
            str_contains($categorySlug, 'phone') || str_contains($title, 'phone') || str_contains($title, 'iphone') || str_contains($title, 'pixel') || str_contains($title, 'samsung') => 'smartphone,phone',
            str_contains($categorySlug, 'laptop') || str_contains($title, 'macbook') || str_contains($title, 'laptop') || str_contains($title, 'thinkpad') || str_contains($title, 'xps') => 'laptop,ultrabook',
            str_contains($categorySlug, 'tablet') || str_contains($title, 'ipad') || str_contains($title, 'tablet') => 'tablet,ipad',
            str_contains($categorySlug, 'watch') || str_contains($title, 'watch') => 'smartwatch,applewatch',
            str_contains($categorySlug, 'console') || str_contains($title, 'ps5') || str_contains($title, 'xbox') || str_contains($title, 'switch') || str_contains($title, 'gaming') => 'gamingconsole,playstation',
            str_contains($categorySlug, 'headphone') || str_contains($categorySlug, 'audio') || str_contains($title, 'headphone') || str_contains($title, 'earbuds') || str_contains($title, 'airpods') || str_contains($title, 'speaker') => 'headphones,audiodevice',
            str_contains($categorySlug, 'camera') || str_contains($title, 'camera') || str_contains($title, 'sony alpha') || str_contains($title, 'canon') || str_contains($title, 'nikon') => 'camera,dslr',
            str_contains($categorySlug, 'monitor') || str_contains($title, 'monitor') || str_contains($title, 'display') => 'monitor,screens',
            str_contains($categorySlug, 'msi') || str_contains($categorySlug, 'pc') || str_contains($title, 'desktop') || str_contains($title, 'pc') => 'gamingcomputer,desktop',
            str_contains($categorySlug, 'accessories') || str_contains($title, 'mouse') || str_contains($title, 'keyboard') || str_contains($title, 'charger') => 'gadget,techaccessory',
            default => 'electronics,technology',
        };

        return "https://loremflickr.com/800/600/{$keyword}?lock={$seed}";
    }

    public function isVerified(): bool
    {
        return $this->verification_status === VerificationStatus::Verified;
    }

    /**
     * Fields the seller can no longer edit once the product has been
     * physically verified — title, condition, description, price, images,
     * and serial/IMEI attribute values are locked at that point.
     */
    public function isLocked(): bool
    {
        return $this->isVerified();
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query->where('publication_status', ProductPublicationStatus::Published);
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Get a list of key features (up to $limit items) for product cards and summary display.
     *
     * @return array<int, string>
     */
    public function keyFeatures(int $limit = 3): array
    {
        $features = [];

        // 1. Extract from short_description if present
        if (filled($this->short_description)) {
            $raw = $this->short_description;
            if (preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $raw, $matches)) {
                foreach ($matches[1] as $item) {
                    $clean = trim(html_entity_decode(strip_tags($item)));
                    if ($clean !== '' && !in_array($clean, $features, true)) {
                        $features[] = $clean;
                    }
                }
            } else {
                $lines = preg_split('/[\r\n•·|]+/', strip_tags($raw));
                foreach ($lines as $line) {
                    $clean = trim(html_entity_decode($line));
                    if ($clean !== '' && strlen($clean) > 2 && !in_array($clean, $features, true)) {
                        $features[] = $clean;
                    }
                }
            }
        }

        // 2. If fewer than limit, pull from saved attribute values or category specifications
        if (count($features) < $limit) {
            $savedValues = $this->relationLoaded('attributeValues')
                ? $this->attributeValues->keyBy('attribute_id')
                : $this->attributeValues()->with(['attribute', 'attributeValue'])->get()->keyBy('attribute_id');

            $categoryAttributes = $this->relationLoaded('category') && $this->category && $this->category->relationLoaded('attributes')
                ? $this->category->attributes
                : ($this->category?->attributes ?? collect());

            foreach ($categoryAttributes as $attr) {
                if (! ($attr instanceof Attribute)) {
                    continue;
                }
                if (count($features) >= $limit) {
                    break;
                }
                $saved = $savedValues->get($attr->id);
                $val = $saved?->displayValue() ?: $attr->unit;
                if (filled($val)) {
                    $featureText = "{$attr->name}: {$val}";
                    if (!in_array($featureText, $features, true)) {
                        $features[] = $featureText;
                    }
                }
            }

            if (count($features) < $limit && $this->relationLoaded('attributeValues')) {
                foreach ($this->attributeValues as $valItem) {
                    if (count($features) >= $limit) {
                        break;
                    }
                    $name = $valItem->attribute?->name;
                    $val = $valItem->displayValue();
                    if ($name && filled($val)) {
                        $featureText = "{$name}: {$val}";
                        if (!in_array($featureText, $features, true)) {
                            $features[] = $featureText;
                        }
                    }
                }
            }
        }

        return array_slice($features, 0, $limit);
    }
}
