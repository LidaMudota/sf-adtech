<?php

namespace App\Http\Controllers;

use App\Models\Click;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function users()
    {
        $users = User::orderBy('created_at')->get();
        return view('admin.users', compact('users'));
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $user->update(['is_active' => $data['is_active']]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back()->with('status', 'Статус пользователя обновлен.');
    }

    public function stats()
    {
        $periods = [
            'day' => DB::raw('DATE(created_at)'),
            'month' => DB::raw('DATE_FORMAT(created_at, "%Y-%m")'),
            'year' => DB::raw('YEAR(created_at)'),
        ];

        $clickStats = [];

        foreach ($periods as $key => $expression) {
            $rows = Click::select(
                $expression . ' as label',
                DB::raw('SUM(CASE WHEN is_successful = 1 THEN 1 ELSE 0 END) as successful'),
                DB::raw('SUM(CASE WHEN is_successful = 0 THEN 1 ELSE 0 END) as failed')
            )
                ->groupBy('label')
                ->orderBy('label')
                ->get();

            $clickStats[$key] = $rows;
        }

        $income = $this->calculateIncome();

        $subscriptions = Subscription::with('offer', 'webmaster')->latest()->take(20)->get();

        return view('admin.stats', compact('clickStats', 'income', 'subscriptions'));
    }

    protected function calculateIncome(): array
    {
        $query = Subscription::join('offers', 'offers.id', '=', 'subscriptions.offer_id')
            ->join('clicks', 'clicks.subscription_id', '=', 'subscriptions.id')
            ->where('clicks.is_successful', true)
            ->select(
                DB::raw('SUM(offers.price_per_click) as advertiser_cost'),
                DB::raw('SUM(subscriptions.webmaster_cpc) as webmaster_payout')
            )
            ->first();

        $advertiser = (float) ($query->advertiser_cost ?? 0);
        $webmaster = (float) ($query->webmaster_payout ?? 0);
        $system = $advertiser - $webmaster;

        return compact('advertiser', 'webmaster', 'system');
    }
}
