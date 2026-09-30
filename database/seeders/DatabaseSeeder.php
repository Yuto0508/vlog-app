<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // TagSeeder と AdminSeeder を実行する
        $this->call([
            TagSeeder::class,
            AdminSeeder::class,
        ]);
    }
}
