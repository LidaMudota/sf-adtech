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
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->get();

        return view('webmaster.subscriptions', compact('subs'));
    }

    public function availableOffers()
    {
        $offers = Offer::with('topics')
            ->where('status', '!=', Offer::STATUS_INACTIVE)
            ->whereDoesntHave('subscriptions', function ($query) {
                $query->where('webmaster_id', Auth::id())
                    ->where('is_active', true);
            })
            ->orderByDesc('created_at')
            ->get();

        return view('webmaster.offers', compact('offers'));
    }

    public function subscribe(Request $request, Offer $offer)
    {
        $validator = validator($request->all(), [
            'webmaster_cpc' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            if ($this->isAjax($request)) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

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

        if ($this->isAjax($request)) {
            return response()->json([
                'status' => 'ok',
                'offer_id' => $offer->id,
                'subscription_id' => $subscription->id,
                'message' => 'subscribed',
            ]);
        }

        return redirect()->route('webmaster.offers')->with('status', 'Подписка оформлена.');
    }

    protected function isAjax(Request $request): bool
    {
        return $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';
    }

    public function unsubscribe(Subscription $subscription)
    {
        $this->authorizeSubscription($subscription);

        if (!$subscription->is_active) {
            return back();
        }

        DB::transaction(function () use ($subscription) {
            $subscription->offer()->lockForUpdate()->first();

            $subscription->update(['is_active' => false]);
        });

        if (request()->expectsJson()) {
            return response()->json([
                'ok' => true,
                'subscription_id' => $subscription->id,
            ]);
        }

        return back()->with('status', 'Подписка отключена.');
    }

    public function stats(Subscription $subscription)
    {
        $this->authorizeSubscription($subscription);

        $periods = [
            'day' => 'DATE(clicks.created_at)',
            'month' => 'DATE_FORMAT(clicks.created_at, "%Y-%m")',
            'year' => 'YEAR(clicks.created_at)',
        ];        

        $result = [];

        foreach ($periods as $key => $expression) {
            $rows = $subscription->clicks()
                ->selectRaw("$expression as label")
                ->selectRaw('COUNT(clicks.id) as clicks_count')
                ->selectRaw('SUM(CASE WHEN clicks.is_successful = 1 THEN 1 ELSE 0 END) as redirects')
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
