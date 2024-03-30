<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 50; $i++) {
            DB::table('orders')->insert([
                'client_id' => rand(1, 10),
                'product_id' => rand(1, 10),
                'warehouse_id' => rand(1, 5),
                'quantity' => rand(1, 20),
                'full_amount' => rand(100, 1000)
            ]);
        }
    }
}
