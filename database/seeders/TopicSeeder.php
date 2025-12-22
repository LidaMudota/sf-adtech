<?php

namespace Database\Seeders;

use App\Models\Topic;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    public function run(): void
    {
        $topics = ['Финансы', 'Здоровье', 'Образование', 'Туризм', 'Развлечения'];
        foreach ($topics as $topic) {
            Topic::firstOrCreate(['name' => $topic]);
        }
    }
}
