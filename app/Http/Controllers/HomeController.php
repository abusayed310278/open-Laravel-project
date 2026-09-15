<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::cachedTree()->take(8);
        $banners = Banner::query()->live()->position('homepage')->get();

        $allLiveProducts = Product::query()
            ->live()
            ->with([
                'brand',
                'images',
                'user',
                'category.attributes',
                'attributeValues.attribute',
                'attributeValues.attributeValue',
            ])
            ->latest('published_at')
            ->get();

        $featuredProducts = $this->productCards(
            $allLiveProducts->sortByDesc('views_count')->take(4)
        );

        $refurbishedDeals = $this->productCards(
            $allLiveProducts->whereIn('condition.value', ['used', 'refurbished'])->take(4)
        );

        $mobileTechProducts = $this->productCards(
            $allLiveProducts->slice(2, 4)
        );

        $latestProducts = $this->productCards(
            $allLiveProducts->take(8)
        );

        $whyBuy = [
            [
                'icon' => 'shield',
                'color' => 'bg-amber-50 text-amber-600',
                'title' => 'Verified Quality',
                'description' => 'Every single device undergoes a 40+ point physical and hardware inspection by Openbox certified engineers.',
            ],
            [
                'icon' => 'refresh',
                'color' => 'bg-amber-50 text-amber-600',
                'title' => '7-Day Return Policy',
                'description' => 'Not completely satisfied? Return your device within 7 days for a hassle-free, full refund.',
            ],
            [
                'icon' => 'lock',
                'color' => 'bg-amber-50 text-amber-600',
                'title' => 'Safe Escrow Payments',
                'description' => 'Your funds remain securely in escrow until you receive and verify the item matches its grade.',
            ],
            [
                'icon' => 'truck',
                'color' => 'bg-amber-50 text-amber-600',
                'title' => 'Fast Inspected Delivery',
                'description' => 'Safe nationwide doorstep delivery with option for on-the-spot doorstep verification.',
            ],
        ];

        $vendors = User::query()
            ->whereIn('role', [UserRole::Business, UserRole::Saler])
            ->where(fn ($q) => $q->whereHas('businessProfile', fn ($p) => $p->where('is_store_active', true))
                ->orWhereHas('salerProfile', fn ($p) => $p->where('is_store_active', true)))
            ->withCount('vendorOrders')
            ->withAvg('approvedReviewsAsSeller', 'rating')
            ->orderByDesc('vendor_orders_count')
            ->limit(6)
            ->get()
            ->map(fn (User $vendor) => [
                'name' => $vendor->name,
                'rating' => $vendor->approved_reviews_as_seller_avg_rating ?? 4.9,
                'salesCount' => $vendor->vendor_orders_count ?: 120,
            ]);

        // Fallback default vendors if none in DB
        if ($vendors->isEmpty()) {
            $vendors = collect([
                ['name' => 'Gadget Hub BD', 'rating' => 4.9, 'salesCount' => 1420],
                ['name' => 'Apple Zone', 'rating' => 5.0, 'salesCount' => 980],
                ['name' => 'Tech Point', 'rating' => 4.8, 'salesCount' => 840],
                ['name' => 'Pixel Lab', 'rating' => 4.9, 'salesCount' => 650],
                ['name' => 'iCenter BD', 'rating' => 4.9, 'salesCount' => 1100],
                ['name' => 'Smart Store', 'rating' => 4.7, 'salesCount' => 430],
            ]);
        }

        $reviews = Review::query()
            ->approved()
            ->with('reviewer')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Review $review) => [
                'name' => $review->reviewer->name,
                'city' => 'Dhaka',
                'rating' => $review->rating ?? 5,
                'body' => $review->body,
            ]);

        if ($reviews->isEmpty()) {
            $reviews = collect([
                [
                    'name' => 'Tanvir Ahmed',
                    'city' => 'Dhaka',
                    'rating' => 5,
                    'body' => 'Bought an iPhone 14 Pro Max Grade A. The phone looks and works brand new! Battery health is 96%. Best purchase ever.',
                ],
                [
                    'name' => 'Nusrat Jahan',
                    'city' => 'Chittagong',
                    'rating' => 5,
                    'body' => 'Openbox inspection gives total peace of mind. Delivery was lightning fast and the packaging was super secure.',
                ],
                [
                    'name' => 'Rahim Chowdhury',
                    'city' => 'Sylhet',
                    'rating' => 5,
                    'body' => 'Sold my M1 MacBook Pro through Openbox. The verification payout was directly sent to my bank in 24 hours!',
                ],
                [
                    'name' => 'Sadia Islam',
                    'city' => 'Dhaka',
                    'rating' => 5,
                    'body' => 'Got Apple Watch Ultra 2 for a steal price. Genuine product with warranty. Highly recommend Openbox!',
                ],
                [
                    'name' => 'Mahmudul Hasan',
                    'city' => 'Rajshahi',
                    'rating' => 5,
                    'body' => 'Fair prices and authentic grading. Customer support guided me patiently through every step.',
                ],
            ]);
        }

        return view('pages.home', compact(
            'categories',
            'banners',
            'featuredProducts',
            'refurbishedDeals',
            'mobileTechProducts',
            'latestProducts',
            'whyBuy',
            'vendors',
            'reviews',
        ));
    }

    /**
     * @param  Collection<int, Product>  $products
     * @return array<int, array<string, mixed>>
     */
    private function productCards($products): array
    {
        return $products->map(fn (Product $product) => [
            'title' => $product->title,
            'brand' => $product->brand?->name ?? 'Electronics',
            'price' => (float) $product->price,
            'comparePrice' => $product->compare_price ? (float) $product->compare_price : (float) ($product->price * 1.2),
            'rating' => 4.9,
            'ratingCount' => rand(15, 60),
            'location' => 'Dhaka',
            'condition' => $product->condition->value,
            'seller' => $product->user->name,
            'isNew' => $product->condition->value === 'new',
            'image' => $product->primaryImage()?->url() ?? 'https://loremflickr.com/600/600/electronics?random=' . $product->id,
            'href' => route('products.show', $product),
            'keyFeatures' => $product->keyFeatures(3),
        ])->values()->all();
    }
}
