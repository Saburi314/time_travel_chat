<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            OpponentsSeeder::class, // Opponents の初期データを登録
        ]);
    }
}
