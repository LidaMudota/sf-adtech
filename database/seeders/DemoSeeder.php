<?php

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\Subscription;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Admin',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $advertiser = User::updateOrCreate(['email' => 'advertiser@example.com'], [
            'name' => 'Advertiser',
            'password' => 'password123',
            'role' => User::ROLE_ADVERTISER,
            'is_active' => true,
        ]);

        $webmaster = User::updateOrCreate(['email' => 'webmaster@example.com'], [
            'name' => 'Webmaster',
            'password' => 'password123',
            'role' => User::ROLE_WEBMASTER,
            'is_active' => true,
        ]);

        $topicIds = Topic::pluck('id')->all();

        $offer = Offer::updateOrCreate(
            ['name' => 'Demo Offer', 'advertiser_id' => $advertiser->id],
            [
                'price_per_click' => 2.50,
                'target_url' => 'https://example.com',
                'status' => Offer::STATUS_ACTIVE,
            ]
        );
        $offer->topics()->sync($topicIds);

        Subscription::updateOrCreate(
            ['offer_id' => $offer->id, 'webmaster_id' => $webmaster->id],
            [
                'token' => Str::uuid()->toString(),
                'webmaster_cpc' => 1.50,
                'is_active' => true,
            ]
        );
    }
}
