<?php

namespace App\Http\Middleware;

use App\Models\VisitorLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log GET requests that return HTML
        if (! $request->isMethod('GET')) {
            return $response;
        }

        $path = $request->path();

        // Skip internal/admin/asset/livewire paths
        if (
            $request->is('admin*') ||
            $request->is('build*') ||
            $request->is('storage*') ||
            $request->is('api*') ||
            $request->is('_debugbar*') ||
            $request->is('favicon.ico') ||
            $request->is('robots.txt')
        ) {
            return $response;
        }

        try {
            $ua = $request->userAgent() ?? '';
            $deviceType = 'desktop';
            if (preg_match('/(bot|crawl|spider|slurp|facebookexternalhit|bingbot|googlebot)/i', $ua)) {
                $deviceType = 'bot';
            } elseif (preg_match('/(mobile|android|iphone|ipad|ipod|blackberry|opera mini)/i', $ua)) {
                $deviceType = 'mobile';
            }

            $platform = 'Other';
            if (stripos($ua, 'windows') !== false) {
                $platform = 'Windows';
            } elseif (stripos($ua, 'mac os') !== false || stripos($ua, 'macintosh') !== false) {
                $platform = 'OS X';
            } elseif (stripos($ua, 'iphone') !== false || stripos($ua, 'ipad') !== false) {
                $platform = 'iOS';
            } elseif (stripos($ua, 'android') !== false) {
                $platform = 'Android';
            } elseif (stripos($ua, 'linux') !== false) {
                $platform = 'Linux';
            }

            $browser = 'Other';
            if (stripos($ua, 'edg') !== false) {
                $browser = 'Edge';
            } elseif (stripos($ua, 'opr') !== false || stripos($ua, 'opera') !== false) {
                $browser = 'Opera';
            } elseif (stripos($ua, 'chrome') !== false) {
                $browser = 'Chrome';
            } elseif (stripos($ua, 'safari') !== false) {
                $browser = 'Safari';
            } elseif (stripos($ua, 'firefox') !== false) {
                $browser = 'Firefox';
            }

            VisitorLog::create([
                'ip_address' => $request->ip(),
                'user_agent' => substr($ua, 0, 500),
                'url' => '/' . ltrim($path, '/'),
                'referrer' => $request->headers->get('referer'),
                'device_type' => $deviceType,
                'platform' => $platform,
                'browser' => $browser,
                'country' => 'Bangladesh', // default local/development geo
                'country_code' => 'BD',
                'city' => 'Dhaka',
                'user_id' => $request->user()?->id,
                'session_id' => $request->session()->getId(),
            ]);
        } catch (\Throwable) {
            // Silently continue so analytics never break user traffic
        }

        return $response;
    }
}
