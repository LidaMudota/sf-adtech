<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Subscription;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OfferController extends Controller
{
    public function index()
    {
        $offers = Offer::withCount([
            'subscriptions as subscriptions_count' => fn ($query) => $query->where('is_active', true),
        ])
            ->where('advertiser_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('offers.index', compact('offers'));
    }

    public function create()
    {
        $topics = Topic::orderBy('name')->get();
        return view('offers.create', compact('topics'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price_per_click' => ['required', 'numeric', 'min:0'],
            'target_url' => ['required', 'url'],
            'status' => ['required', 'in:' . implode(',', [Offer::STATUS_DRAFT, Offer::STATUS_ACTIVE])],
            'topics' => ['array'],
            'topics.*' => ['exists:topics,id'],
        ]);

        $offer = Offer::create([
            'advertiser_id' => Auth::id(),
            'name' => $data['name'],
            'price_per_click' => $data['price_per_click'],
            'target_url' => $data['target_url'],
            'status' => $data['status'],
        ]);

        $offer->topics()->sync($data['topics'] ?? []);

        if ($request->expectsJson()) {
            $offer->loadCount([
                'subscriptions as subscriptions_count' => fn ($query) => $query->where('is_active', true),
            ])->load('topics');

            return response()->json([
                'status' => 'ok',
                'offer' => $this->serializeOffer($offer),
            ], 201);
        }

        return redirect()->route('offers.index')->with('status', 'Оффер создан.');
    }

    public function updateStatus(Request $request, Offer $offer)
    {
        $this->authorizeOffer($offer);

        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', [Offer::STATUS_DRAFT, Offer::STATUS_ACTIVE, Offer::STATUS_INACTIVE])],
        ]);

        $offer->update(['status' => $data['status']]);

        if ($request->expectsJson()) {
            $offer->loadCount([
                'subscriptions as subscriptions_count' => fn ($query) => $query->where('is_active', true),
            ]);

            return response()->json([
                'status' => 'ok',
                'offer' => $this->serializeOffer($offer),
            ]);
        }

        return back()->with('status', 'Статус обновлен.');
    }

    public function kanban()
    {
        $offers = Offer::where('advertiser_id', Auth::id())
            ->orderBy('created_at')
            ->get()
            ->groupBy('status');

        return view('offers.kanban', compact('offers'));
    }

    public function show(Offer $offer)
    {
        $this->authorizeOffer($offer);

        $stats = $this->buildStats($offer->id);

        return view('offers.show', [
            'offer' => $offer->load('topics'),
            'stats' => $stats,
        ]);
    }

    public function deactivate(Offer $offer)
    {
        $this->authorizeOffer($offer);
        $offer->update(['status' => Offer::STATUS_INACTIVE]);

        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'id' => $offer->id]);
        }

        return back()->with('status', 'Оффер деактивирован.');
    }

    public function jsonShow(Offer $offer)
    {
        $this->authorizeOffer($offer);

        $offer->loadCount([
            'subscriptions as subscriptions_count' => fn ($query) => $query->where('is_active', true),
        ])->load('topics');

        return response()->json([
            'offer' => $this->serializeOffer($offer),
        ]);
    }

    public function jsonList(Request $request)
    {
        $offers = Offer::withCount([
            'subscriptions as subscriptions_count' => fn ($query) => $query->where('is_active', true),
        ])
            ->where('advertiser_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json([
            'offers' => $offers->map(fn ($offer) => $this->serializeOffer($offer)),
        ]);
    }

    protected function authorizeOffer(Offer $offer): void
    {
        if ($offer->advertiser_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403);
        }
    }

    protected function buildStats(int $offerId): array
    {
        $periods = [
            'day' => 'DATE(clicks.created_at)',
            'month' => 'DATE_FORMAT(clicks.created_at, "%Y-%m")',
            'year' => 'YEAR(clicks.created_at)',
        ];
    
        $result = [];
    
        foreach ($periods as $key => $expression) {
            $rows = Subscription::where('subscriptions.offer_id', $offerId)
                ->join('clicks', 'clicks.subscription_id', '=', 'subscriptions.id')
                ->join('offers', 'offers.id', '=', 'subscriptions.offer_id')
                ->selectRaw("$expression as label")
                ->addSelect([
                    DB::raw('COUNT(clicks.id) as clicks_count'),
                    DB::raw('SUM(CASE WHEN clicks.is_successful = 1 THEN 1 ELSE 0 END) as redirects'),
                    DB::raw('SUM(CASE WHEN clicks.is_successful = 1 THEN offers.price_per_click ELSE 0 END) as advertiser_cost'),
                    DB::raw('SUM(CASE WHEN clicks.is_successful = 1 THEN subscriptions.webmaster_cpc ELSE 0 END) as webmaster_income'),
                ])
                ->groupBy('label')
                ->orderBy('label')
                ->get();
    
            $result[$key] = $rows->map(function ($row) {
                $systemIncome = $row->advertiser_cost * config('adtech.system_commission_rate');
                return [
                    'label' => $row->label,
                    'clicks' => (int) $row->clicks_count,
                    'redirects' => (int) $row->redirects,
                    'advertiser_cost' => (float) $row->advertiser_cost,
                    'webmaster_income' => (float) $row->webmaster_income,
                    'system_income' => (float) $systemIncome,
                ];
            });
        }
    
        return $result;
    }

    private function serializeOffer(Offer $offer): array
    {
        return [
            'id' => $offer->id,
            'name' => $offer->name,
            'price_per_click' => (float) $offer->price_per_click,
            'target_url' => $offer->target_url,
            'status' => $offer->status,
            'subscriptions_count' => (int) ($offer->subscriptions_count ?? 0),
            'topics' => $offer->topics?->pluck('name')->all() ?? [],
        ];
    }
}
