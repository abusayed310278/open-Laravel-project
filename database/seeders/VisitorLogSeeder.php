<?php

namespace Database\Seeders;

use App\Models\VisitorLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VisitorLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (VisitorLog::count() >= 500) {
            return;
        }

        $pages = [
            '/' => 3666,
            '/shop' => 1420,
            '/shop?category=smartphones' => 831,
            '/categories' => 564,
            '/products/iphone-15-pro-max' => 514,
            '/blog' => 380,
            '/stores' => 290,
            '/shop?type=overstock' => 197,
            '/grading-system' => 148,
            '/about' => 120,
        ];

        $referrers = [
            'Direct / Search Engine' => 3270,
            'https://google.com' => 2240,
            'https://facebook.com' => 1104,
            'https://youtube.com' => 835,
            'https://instagram.com' => 789,
            'https://t.co' => 522,
            'https://linkedin.com' => 340,
            'https://bing.com' => 151,
            'https://pinterest.com' => 130,
            'https://reddit.com' => 97,
        ];

        $locations = [
            ['country' => 'Bangladesh', 'code' => 'BD', 'city' => 'Dhaka', 'weight' => 2442],
            ['country' => 'Singapore', 'code' => 'SG', 'city' => 'Singapore', 'weight' => 1514],
            ['country' => 'Egypt', 'code' => 'EG', 'city' => 'Cairo', 'weight' => 1170],
            ['country' => 'United States', 'code' => 'US', 'city' => 'New York', 'weight' => 1044],
            ['country' => 'Germany', 'code' => 'DE', 'city' => 'Berlin', 'weight' => 771],
            ['country' => 'Netherlands', 'code' => 'NL', 'city' => 'Amsterdam', 'weight' => 580],
            ['country' => 'France', 'code' => 'FR', 'city' => 'Paris', 'weight' => 543],
            ['country' => 'Japan', 'code' => 'JP', 'city' => 'Tokyo', 'weight' => 482],
            ['country' => 'Canada', 'code' => 'CA', 'city' => 'Toronto', 'weight' => 411],
            ['country' => 'United Kingdom', 'code' => 'GB', 'city' => 'London', 'weight' => 391],
        ];

        $browsers = [
            'Chrome' => 7060,
            'Safari' => 1250,
            'Firefox' => 480,
            'Edge' => 377,
            'Opera' => 190,
            'Other' => 203,
        ];

        $platforms = [
            'Windows' => 5864,
            'OS X' => 1402,
            'Android' => 924,
            'iOS' => 709,
            'Linux' => 523,
            'Other' => 138,
        ];

        // Seed 600 records with distribution to allow fast aggregates and instant loading
        $batch = [];
        $sessions = [];
        for ($s = 0; $s < 777; $s++) {
            $sessions[] = 'sess_' . Str::random(12);
        }

        $now = now();

        for ($i = 0; $i < 650; $i++) {
            $loc = $locations[$i % count($locations)];
            $pageKeys = array_keys($pages);
            $refKeys = array_keys($referrers);
            $browserKeys = array_keys($browsers);
            $platformKeys = array_keys($platforms);

            $deviceType = ($i % 10 === 0) ? 'bot' : (($i % 4 === 0) ? 'mobile' : 'desktop');

            $batch[] = [
                'ip_address' => '192.168.1.' . (($i % 250) + 1),
                'user_agent' => 'Mozilla/5.0 (Sample UA)',
                'url' => $pageKeys[$i % count($pageKeys)],
                'referrer' => $refKeys[$i % count($refKeys)],
                'device_type' => $deviceType,
                'platform' => $platformKeys[$i % count($platformKeys)],
                'browser' => $browserKeys[$i % count($browserKeys)],
                'country' => $loc['country'],
                'country_code' => $loc['code'],
                'city' => $loc['city'],
                'user_id' => null,
                'session_id' => $sessions[$i % count($sessions)],
                'created_at' => $now->copy()->subMinutes(rand(5, 43200)),
                'updated_at' => $now,
            ];

            if (count($batch) >= 100) {
                DB::table('visitor_logs')->insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('visitor_logs')->insert($batch);
        }
    }
}
