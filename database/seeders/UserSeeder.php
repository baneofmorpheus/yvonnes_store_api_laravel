<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()
            ->hasStores(1, ['name' => 'SoulTrain', 'is_default' => true])
            ->create(['first_name' => 'Yvonne', 'last_name' => 'Chux']);
        User::factory()
            ->count(50)->hasStores(1)
            ->create();
    }
}
