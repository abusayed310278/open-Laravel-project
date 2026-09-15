<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Banner;
use App\Models\BlogCategory;
use App\Models\Brand;
use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\CommissionRule;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\SalerProfile;
use App\Models\Tag;
use App\Models\User;
use App\Models\VendorOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $admin = $this->seedAdmin();
            $customers = $this->seedCustomers();
            $vendors = $this->seedVendors();
            $categories = $this->seedCategories();
            $brands = $this->seedBrands();
            $products = $this->seedProducts($vendors, $categories, $brands);
            $this->seedBanners();
            $this->seedBlog($admin);
            $this->seedOrdersAndReviews($customers, $vendors, $products);
            $this->seedCommissionRule();
        });
    }

    private function seedAdmin(): User
    {
        return User::firstOrCreate(
            ['email' => 'admin@openbox.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }

    /**
     * @return array<int, User>
     */
    private function seedCustomers(): array
    {
        $names = ['Sarah Ahmed', 'Omar Khalid', 'Layla Hassan', 'Yusuf Ibrahim', 'Mona Saeed'];

        return collect($names)->map(function (string $name, int $i) {
            $email = strtolower(Str::slug($name, '.')).'@example.com';

            $customer = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => bcrypt('password'),
                    'role' => 'customer',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            Address::firstOrCreate(
                ['user_id' => $customer->id, 'is_default' => true],
                [
                    'label' => 'Home',
                    'name' => $name,
                    'phone' => '+9745500'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                    'line1' => fake()->streetAddress(),
                    'city' => 'Doha',
                    'country' => 'Qatar',
                ]
            );

            return $customer;
        })->all();
    }

    /**
     * @return array<int, array{user: User, type: string}>
     */
    private function seedVendors(): array
    {
        $businesses = [
            ['name' => 'TechHub Electronics', 'city' => 'Doha'],
            ['name' => 'Prime Gadgets Trading', 'city' => 'Dubai'],
            ['name' => 'NextGen Devices Co.', 'city' => 'Riyadh'],
            ['name' => 'Circuit City Retail', 'city' => 'Manama'],
        ];

        $salers = [
            ['name' => 'Ahmed\'s Electronics Corner', 'city' => 'Doha'],
            ['name' => 'Fatima\'s Tech Deals', 'city' => 'Doha'],
            ['name' => 'Karim Gadget Bazaar', 'city' => 'Abu Dhabi'],
            ['name' => 'Nadia\'s Refurb Store', 'city' => 'Kuwait City'],
        ];

        $vendors = [];

        foreach ($businesses as $i => $biz) {
            $email = 'vendor.business'.($i + 1).'@openbox.com';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $biz['name'].' Admin',
                    'password' => bcrypt('password'),
                    'role' => 'business',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            BusinessProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'business_name' => $biz['name'],
                    'slug' => Str::slug($biz['name']),
                    'description' => "{$biz['name']} — a trusted electronics retailer offering new and certified refurbished devices.",
                    'city' => $biz['city'],
                    'country' => 'Qatar',
                    'phone' => '+97444'.str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT),
                    'is_store_active' => true,
                    'profile_completed' => true,
                ]
            );

            $vendors[] = ['user' => $user, 'type' => 'business'];
        }

        foreach ($salers as $i => $saler) {
            $email = 'vendor.saler'.($i + 1).'@openbox.com';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $saler['name'],
                    'password' => bcrypt('password'),
                    'role' => 'saler',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            SalerProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'display_name' => $saler['name'],
                    'slug' => Str::slug($saler['name']),
                    'bio' => "Independent seller specializing in quality used and refurbished electronics from {$saler['city']}.",
                    'location' => $saler['city'],
                    'city' => $saler['city'],
                    'country' => 'Qatar',
                    'is_store_active' => true,
                    'profile_completed' => true,
                ]
            );

            $vendors[] = ['user' => $user, 'type' => 'saler'];
        }

        return $vendors;
    }

    /**
     * @return array<string, Category>
     */
    private function seedCategories(): array
    {
        $tree = [
            'Smartphones' => [],
            'Laptops' => [],
            'Tablets' => [],
            'Smartwatches' => [],
            'Gaming Consoles' => [],
            'Audio & Headphones' => [],
            'Cameras' => [],
            'Accessories' => [],
        ];

        $categories = [];
        $order = 1;

        foreach ($tree as $name => $children) {
            $categories[$name] = Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => "{$name} for every budget — new, used, and refurbished.",
                    'status' => 'active',
                    'sort_order' => $order++,
                ]
            );
        }

        return $categories;
    }

    /**
     * @return array<string, Brand>
     */
    private function seedBrands(): array
    {
        $names = ['Apple', 'Samsung', 'Sony', 'Dell', 'HP', 'Lenovo', 'Google', 'Microsoft', 'Bose', 'Nintendo'];

        $brands = [];

        foreach ($names as $name) {
            $brands[$name] = Brand::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => "Official {$name} products and accessories.",
                    'status' => 'active',
                ]
            );
        }

        return $brands;
    }

    /**
     * @param  array<int, array{user: User, type: string}>  $vendors
     * @param  array<string, Category>  $categories
     * @param  array<string, Brand>  $brands
     * @return array<int, Product>
     */
    private function seedProducts(array $vendors, array $categories, array $brands): array
    {
        $catalog = [
            ['title' => 'iPhone 14 Pro Max', 'category' => 'Smartphones', 'brand' => 'Apple', 'condition' => 'used', 'grade' => 'A', 'price' => 899.99, 'compare' => 1099.99],
            ['title' => 'Samsung Galaxy S23 Ultra', 'category' => 'Smartphones', 'brand' => 'Samsung', 'condition' => 'new', 'grade' => 'A', 'price' => 999.99, 'compare' => 1199.99],
            ['title' => 'Google Pixel 8 Pro', 'category' => 'Smartphones', 'brand' => 'Google', 'condition' => 'refurbished', 'grade' => 'B', 'price' => 649.00, 'compare' => 899.00],
            ['title' => 'MacBook Pro 16" M2 Max', 'category' => 'Laptops', 'brand' => 'Apple', 'condition' => 'refurbished', 'grade' => 'B', 'price' => 2499.00, 'compare' => 3499.00],
            ['title' => 'Dell XPS 15', 'category' => 'Laptops', 'brand' => 'Dell', 'condition' => 'used', 'grade' => 'A', 'price' => 1299.00, 'compare' => 1599.00],
            ['title' => 'Lenovo ThinkPad X1 Carbon', 'category' => 'Laptops', 'brand' => 'Lenovo', 'condition' => 'refurbished', 'grade' => 'C', 'price' => 799.00, 'compare' => 1199.00],
            ['title' => 'iPad Pro 12.9" M2', 'category' => 'Tablets', 'brand' => 'Apple', 'condition' => 'new', 'grade' => 'A', 'price' => 1099.00, 'compare' => 1299.00],
            ['title' => 'Samsung Galaxy Tab S9', 'category' => 'Tablets', 'brand' => 'Samsung', 'condition' => 'used', 'grade' => 'A', 'price' => 649.00, 'compare' => 799.00],
            ['title' => 'Apple Watch Series 9', 'category' => 'Smartwatches', 'brand' => 'Apple', 'condition' => 'new', 'grade' => 'A', 'price' => 429.00, 'compare' => 499.00],
            ['title' => 'Samsung Galaxy Watch 6', 'category' => 'Smartwatches', 'brand' => 'Samsung', 'condition' => 'refurbished', 'grade' => 'B', 'price' => 249.00, 'compare' => 349.00],
            ['title' => 'PlayStation 5 Console', 'category' => 'Gaming Consoles', 'brand' => 'Sony', 'condition' => 'used', 'grade' => 'A', 'price' => 449.00, 'compare' => 549.00],
            ['title' => 'Nintendo Switch OLED', 'category' => 'Gaming Consoles', 'brand' => 'Nintendo', 'condition' => 'new', 'grade' => 'A', 'price' => 349.00, 'compare' => 379.00],
            ['title' => 'Xbox Series X', 'category' => 'Gaming Consoles', 'brand' => 'Microsoft', 'condition' => 'refurbished', 'grade' => 'B', 'price' => 379.00, 'compare' => 499.00],
            ['title' => 'Sony WH-1000XM5 Headphones', 'category' => 'Audio & Headphones', 'brand' => 'Sony', 'condition' => 'new', 'grade' => 'A', 'price' => 329.00, 'compare' => 399.00],
            ['title' => 'Bose QuietComfort Ultra', 'category' => 'Audio & Headphones', 'brand' => 'Bose', 'condition' => 'used', 'grade' => 'A', 'price' => 279.00, 'compare' => 349.00],
            ['title' => 'Sony Alpha A7 IV Camera', 'category' => 'Cameras', 'brand' => 'Sony', 'condition' => 'refurbished', 'grade' => 'B', 'price' => 1899.00, 'compare' => 2499.00],
            ['title' => 'Apple MagSafe Charger', 'category' => 'Accessories', 'brand' => 'Apple', 'condition' => 'new', 'grade' => 'A', 'price' => 39.00, 'compare' => null],
            ['title' => 'Samsung 45W Fast Charger', 'category' => 'Accessories', 'brand' => 'Samsung', 'condition' => 'new', 'grade' => 'A', 'price' => 29.00, 'compare' => null],
        ];

        $products = [];

        foreach ($catalog as $i => $row) {
            $vendor = $vendors[$i % count($vendors)]['user'];
            $isOpenbox = $row['condition'] === 'refurbished';

            $title = $row['title'];
            $product = Product::firstOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'user_id' => $vendor->id,
                    'category_id' => $categories[$row['category']]->id,
                    'brand_id' => $brands[$row['brand']]->id,
                    'title' => $title,
                    'description' => fake()->paragraphs(3, true),
                    'short_description' => "Quality {$row['condition']} {$title}, grade {$row['grade']}.",
                    'condition' => $row['condition'],
                    'grade' => $row['grade'],
                    'status' => 'published',
                    'approval_status' => 'approved',
                    'publication_status' => 'published',
                    'verification_status' => $isOpenbox ? 'verified' : 'not_requested',
                    'warehouse_status' => $isOpenbox ? 'stored' : 'not_deposited',
                    'payment_route' => $isOpenbox ? 'openbox' : 'seller',
                    'price' => $row['price'],
                    'compare_price' => $row['compare'],
                    'sku' => strtoupper(Str::slug($title, '-')).'-'.strtoupper(Str::random(4)),
                    'quantity' => fake()->numberBetween(2, 25),
                    'is_negotiable' => fake()->boolean(20),
                    'published_at' => now()->subDays(fake()->numberBetween(1, 60)),
                ]
            );

            $this->attachImages($product, 3);
            $products[] = $product;
        }

        // A larger pool of randomized live products so category/search pages don't feel empty.
        $categoryValues = array_values($categories);
        $brandValues = array_values($brands);

        for ($i = 0; $i < 60; $i++) {
            $vendor = $vendors[array_rand($vendors)]['user'];
            $condition = fake()->randomElement(['new', 'used', 'refurbished']);
            $isOpenbox = $condition === 'refurbished' && fake()->boolean(60);

            $product = Product::factory()->live()->create([
                'user_id' => $vendor->id,
                'category_id' => fake()->randomElement($categoryValues)->id,
                'brand_id' => fake()->randomElement($brandValues)->id,
                'condition' => $condition,
                'grade' => fake()->randomElement(['A', 'B', 'C']),
                'verification_status' => $isOpenbox ? 'verified' : 'not_requested',
                'warehouse_status' => $isOpenbox ? 'stored' : 'not_deposited',
                'payment_route' => $isOpenbox ? 'openbox' : 'seller',
                'compare_price' => fake()->boolean(50) ? null : fake()->randomFloat(2, 50, 2500),
            ]);

            $this->attachImages($product, 1);
            $products[] = $product;
        }

        return $products;
    }

    private function attachImages(Product $product, int $count): void
    {
        if ($product->images()->exists()) {
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $seed = (int) ($product->id . $i);
            ProductImage::create([
                'product_id' => $product->id,
                'path' => 'https://loremflickr.com/800/600/electronics,gadget?lock='.$seed,
                'type' => 'gallery',
                'sort_order' => $i,
                'is_primary' => $i === 0,
            ]);
        }
    }

    private function seedBanners(): void
    {
        $banners = [
            ['title' => 'Certified Refurbished — Up to 40% Off', 'link' => '/shop?condition=refurbished'],
            ['title' => 'New Arrivals Every Week', 'link' => '/shop'],
            ['title' => 'Sell With Openbox — Start Today', 'link' => '/register'],
        ];

        foreach ($banners as $i => $banner) {
            Banner::firstOrCreate(
                ['title' => $banner['title']],
                [
                    'image' => 'https://loremflickr.com/1600/500/electronics,laptop?lock='.$i,
                    'link' => $banner['link'],
                    'position' => 'homepage',
                    'sort_order' => $i + 1,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedBlog(User $admin): void
    {
        $category = BlogCategory::firstOrCreate(
            ['slug' => 'buying-guides'],
            ['name' => 'Buying Guides', 'description' => 'Tips for buying electronics smarter.', 'status' => 'active']
        );

        $tags = collect(['tips', 'refurbished', 'grading', 'reviews'])->map(
            fn (string $name) => Tag::firstOrCreate(['slug' => $name], ['name' => ucfirst($name)])
        );

        $posts = [
            ['title' => 'How Our Grading System Works', 'excerpt' => 'A closer look at what Grade A, B, and C really mean.'],
            ['title' => 'Refurbished vs Used: What\'s the Difference?', 'excerpt' => 'Understanding condition categories before you buy.'],
            ['title' => '5 Tips for Buying Electronics Online Safely', 'excerpt' => 'Protect yourself when shopping on any marketplace.'],
            ['title' => 'Why Verified Sellers Matter', 'excerpt' => 'How Openbox vets every vendor on the platform.'],
        ];

        foreach ($posts as $i => $post) {
            $created = Post::firstOrCreate(
                ['slug' => Str::slug($post['title'])],
                [
                    'category_id' => $category->id,
                    'author_id' => $admin->id,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'content' => fake()->paragraphs(6, true),
                    'featured_image' => 'https://loremflickr.com/900/500/technology?lock='.$i,
                    'status' => 'published',
                    'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
                ]
            );

            if ($created->tags()->count() === 0) {
                $created->tags()->attach($tags->random(2)->pluck('id'));
            }
        }
    }

    /**
     * @param  array<int, User>  $customers
     * @param  array<int, array{user: User, type: string}>  $vendors
     * @param  array<int, Product>  $products
     */
    private function seedOrdersAndReviews(array $customers, array $vendors, array $products): void
    {
        $reviewTitles = ['Great purchase!', 'Exactly as described', 'Fast shipping, happy customer', 'Good value for money', 'Would buy again'];
        $reviewBodies = [
            'The product arrived in great condition and matched the listing perfectly. Very satisfied with this purchase.',
            'Grading was spot on — no surprises. The seller was responsive and shipping was quick.',
            'Solid quality for the price. A few minor cosmetic marks but nothing that affects function.',
            'Excellent communication from the vendor and the item works flawlessly.',
        ];

        for ($i = 0; $i < 10; $i++) {
            $customer = $customers[$i % count($customers)];
            $product = $products[array_rand($products)];
            $vendor = $product->user;

            $order = Order::create([
                'order_number' => 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                'customer_id' => $customer->id,
                'status' => 'delivered',
                'subtotal' => $product->price,
                'shipping_total' => 0,
                'tax_total' => 0,
                'total' => $product->price,
            ]);

            $vendorOrder = VendorOrder::create([
                'order_id' => $order->id,
                'vendor_id' => $vendor->id,
                'vendor_order_number' => 'VO-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                'status' => 'delivered',
                'subtotal' => $product->price,
                'shipping' => 0,
                'tax' => 0,
                'total' => $product->price,
                'payment_route' => $product->payment_route,
                'payment_method' => 'cod',
                'tracking_number' => strtoupper(Str::random(10)),
                'shipped_at' => now()->subDays(5),
                'delivered_at' => now()->subDays(2),
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'vendor_order_id' => $vendorOrder->id,
                'product_id' => $product->id,
                'product_title' => $product->title,
                'product_grade' => $product->grade,
                'product_condition' => $product->condition,
                'sku' => $product->sku,
                'quantity' => 1,
                'unit_price' => $product->price,
                'total_price' => $product->price,
                'payment_route' => $product->payment_route,
            ]);

            Review::firstOrCreate(
                [
                    'reviewer_id' => $customer->id,
                    'reviewable_type' => 'product',
                    'reviewable_id' => $product->id,
                    'order_id' => $order->id,
                ],
                [
                    'rating' => fake()->numberBetween(4, 5),
                    'title' => fake()->randomElement($reviewTitles),
                    'body' => fake()->randomElement($reviewBodies),
                    'status' => 'approved',
                ]
            );

            Review::firstOrCreate(
                [
                    'reviewer_id' => $customer->id,
                    'reviewable_type' => 'seller',
                    'reviewable_id' => $vendor->id,
                    'order_id' => $order->id,
                ],
                [
                    'rating' => fake()->numberBetween(4, 5),
                    'title' => fake()->randomElement($reviewTitles),
                    'body' => fake()->randomElement($reviewBodies),
                    'status' => 'approved',
                ]
            );
        }
    }

    private function seedCommissionRule(): void
    {
        CommissionRule::firstOrCreate(
            ['type' => 'global', 'reference_id' => null],
            [
                'commission_type' => 'percentage',
                'value' => 10,
                'priority' => 0,
                'is_active' => true,
            ]
        );
    }
}
