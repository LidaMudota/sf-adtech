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
        $offers = Offer::withCount('subscriptions')
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

        return redirect()->route('offers.index')->with('status', 'Оффер создан.');
    }

    public function updateStatus(Request $request, Offer $offer)
    {
        $this->authorizeOffer($offer);

        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', [Offer::STATUS_DRAFT, Offer::STATUS_ACTIVE, Offer::STATUS_INACTIVE])],
        ]);

        $offer->update(['status' => $data['status']]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
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
        return back()->with('status', 'Оффер деактивирован.');
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
            'day' => DB::raw('DATE(clicks.created_at)'),
            'month' => DB::raw('DATE_FORMAT(clicks.created_at, "%Y-%m")'),
            'year' => DB::raw('YEAR(clicks.created_at)'),
        ];

        $result = [];

        foreach ($periods as $key => $expression) {
            $rows = Subscription::where('offer_id', $offerId)
                ->join('clicks', 'clicks.subscription_id', '=', 'subscriptions.id')
                ->select(
                    $expression . ' as label',
                    DB::raw('COUNT(clicks.id) as clicks_count'),
                    DB::raw('SUM(CASE WHEN clicks.is_successful = 1 THEN 1 ELSE 0 END) as redirects'),
                    DB::raw('SUM(CASE WHEN clicks.is_successful = 1 THEN 1 ELSE 0 END) * offers.price_per_click as advertiser_cost'),
                    DB::raw('SUM(CASE WHEN clicks.is_successful = 1 THEN 1 ELSE 0 END) * subscriptions.webmaster_cpc as webmaster_income')
                )
                ->join('offers', 'offers.id', '=', 'subscriptions.offer_id')
                ->groupBy('label', 'offers.price_per_click')
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
}
