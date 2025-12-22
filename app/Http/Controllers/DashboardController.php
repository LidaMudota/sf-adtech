<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();
        if ($user->role === 'advertiser') {
            $offersCount = Offer::where('advertiser_id', $user->id)->count();
            return view('dashboard.advertiser', compact('offersCount'));
        }

        if ($user->role === 'webmaster') {
            $subscriptionsCount = Subscription::where('webmaster_id', $user->id)->count();
            return view('dashboard.webmaster', compact('subscriptionsCount'));
        }

        return view('dashboard.admin');
    }
}
