<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
        ]);

        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => strtolower(trim($data['email']))],
            ['ip_address' => $request->ip()],
        );

        $alreadySubscribed = ! $subscriber->wasRecentlyCreated;
        $message = $alreadySubscribed
            ? 'You are already subscribed to our newsletter.'
            : 'Thank you for subscribing!';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'type' => $alreadySubscribed ? 'info' : 'success',
            ]);
        }

        return back()->with('status', $message);
    }
}
