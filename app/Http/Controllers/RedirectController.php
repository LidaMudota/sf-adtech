<?php

namespace App\Http\Controllers;

use App\Models\Click;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RedirectController extends Controller
{
    public function __invoke(string $token)
    {
        $subscription = Subscription::where('token', $token)
            ->where('is_active', true)
            ->with(['offer', 'webmaster', 'offer.advertiser'])
            ->first();

        $advertiserActive = $subscription?->offer?->advertiser?->is_active ?? false;

        if (!$subscription || !$subscription->offer || $subscription->offer->status === 'inactive' || !$subscription->webmaster->is_active || !$advertiserActive) {
            Click::create([
                'subscription_id' => $subscription?->id,
                'token' => $token,
                'is_successful' => false,
            ]);

            abort(404);
        }

        $click = Click::create([
            'subscription_id' => $subscription->id,
            'token' => $token,
            'is_successful' => true,
            'redirected_at' => Carbon::now(),
        ]);

        return redirect()->away($subscription->offer->target_url, 302, [], true);
    }
}
