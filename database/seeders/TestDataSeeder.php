<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branch = \App\Models\Branch::create([
            'name'     => 'Main Branch',
            'location' => 'Quezon City',
        ]);

        \App\Models\User::create([
            'name'      => 'Juan',
            'pin_hash'  => \Illuminate\Support\Facades\Hash::make('1234'),
            'role'      => 'cashier',
            'branch_id' => $branch->id,
        ]);

        $product = \App\Models\Product::create([
            'name'  => 'Milk Tea',
            'price' => 45.00,
        ]);

        \App\Models\Recipe::create([
            'product_id'      => $product->id,
            'ingredient_name' => 'Tea Powder',
            'quantity'        => 0.05,
            'unit'            => 'kg',
        ]);

        \App\Models\StockLevel::create([
            'branch_id'       => $branch->id,
            'ingredient_name' => 'Tea Powder',
            'quantity'        => 10,
            'unit'            => 'kg',
            'updated_at'      => now(),
        ]);

        $this->command->info('Done! Branch ID: ' . $branch->id . ', Product ID: ' . $product->id);
    }
}
