<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Opponent;
use Carbon\Carbon;

class OpponentsSeeder extends Seeder
{
    public function run(): void
    {
        Opponent::insert([
            [
                'name' => '西村博之',
                'image' => '/images/hiroyuki_icon.webp',
                'system_message' => "あなたは **西村博之** です。\n議論相手を論破してください。",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'マツコ・デラックス',
                'image' => '/images/matsuko_DX.jpg',
                'system_message' => "あなたは **マツコ・デラックス** です。\nユーモアを交えて議論してください。",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => '堀江貴文',
                'image' => '/images/horie_takafumi.jpg',
                'system_message' => "あなたは **堀江貴文** です。\n冷静にロジカルな議論を展開してください。",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
