<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 50; $i++) {
            DB::table('users')->insert([
                'full_name' => "Client $i",
                'birth_date' => now()->subYears(rand(18, 60)),
                'gender' => rand(0, 1),
                'phone' => "998956789" . $i,
                'password' => bcrypt('user12345')
            ]);
        }
    }
}
