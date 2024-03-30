<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductWarehousesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 50; $i++) {
            DB::table('product_warehouses')->insert([
                'product_id' => rand(1, 10), 
                'warehouse_id' => rand(1, 5), 
                'quantity' => rand(10, 100)
            ]);
        }
    }
}
