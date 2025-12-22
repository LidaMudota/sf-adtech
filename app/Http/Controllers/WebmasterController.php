<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WebmasterController extends Controller
{
    public function subscriptions()
    {
        $subs = Subscription::with('offer')
            ->where('webmaster_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('webmaster.subscriptions', compact('subs'));
    }

    public function availableOffers()
    {
        $offers = Offer::with('topics')
            ->where('status', '!=', Offer::STATUS_INACTIVE)
            ->orderByDesc('created_at')
            ->get();

        return view('webmaster.offers', compact('offers'));
    }

    public function subscribe(Request $request, Offer $offer)
    {
        $data = $request->validate([
            'webmaster_cpc' => ['required', 'numeric', 'min:0'],
        ]);

        $subscription = Subscription::firstOrCreate(
            ['offer_id' => $offer->id, 'webmaster_id' => Auth::id()],
            [
                'token' => Str::uuid()->toString(),
                'webmaster_cpc' => $data['webmaster_cpc'],
                'is_active' => true,
            ]
        );

        if (!$subscription->wasRecentlyCreated) {
            $subscription->update(['webmaster_cpc' => $data['webmaster_cpc'], 'is_active' => true]);
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'token' => $subscription->token]);
        }

        return redirect()->route('webmaster.subscriptions')->with('status', 'Подписка оформлена.');
    }

    public function unsubscribe(Subscription $subscription)
    {
        $this->authorizeSubscription($subscription);
        $subscription->update(['is_active' => false]);
        return back()->with('status', 'Подписка отключена.');
    }

    public function stats(Subscription $subscription)
    {
        $this->authorizeSubscription($subscription);

        $periods = [
            'day' => DB::raw('DATE(clicks.created_at)'),
            'month' => DB::raw('DATE_FORMAT(clicks.created_at, "%Y-%m")'),
            'year' => DB::raw('YEAR(clicks.created_at)'),
        ];

        $result = [];

        foreach ($periods as $key => $expression) {
            $rows = $subscription->clicks()
                ->select(
                    $expression . ' as label',
                    DB::raw('COUNT(clicks.id) as clicks_count'),
                    DB::raw('SUM(CASE WHEN clicks.is_successful = 1 THEN 1 ELSE 0 END) as redirects')
                )
                ->groupBy('label')
                ->orderBy('label')
                ->get();

            $result[$key] = $rows->map(function ($row) use ($subscription) {
                $advertiserCost = $row->redirects * $subscription->offer->price_per_click;
                $webmasterIncome = $row->redirects * $subscription->webmaster_cpc;
                $systemIncome = $advertiserCost - $webmasterIncome;

                return [
                    'label' => $row->label,
                    'clicks' => (int) $row->clicks_count,
                    'redirects' => (int) $row->redirects,
                    'advertiser_cost' => (float) $advertiserCost,
                    'webmaster_income' => (float) $webmasterIncome,
                    'system_income' => (float) $systemIncome,
                ];
            });
        }

        return view('webmaster.stats', [
            'subscription' => $subscription->load('offer'),
            'stats' => $result,
        ]);
    }

    protected function authorizeSubscription(Subscription $subscription): void
    {
        if ($subscription->webmaster_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403);
        }
    }
}
