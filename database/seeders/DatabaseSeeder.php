<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $branch = Branch::create([
            'name' => 'Branch QC',
        ]);

        User::create([
            'name' => 'Owner Admin',
            'email' => 'owner@inventory.test',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'branch_id' => null,
        ]);

        User::create([
            'name' => 'Staff Juan',
            'pin' => Hash::make('1234'),
            'role' => 'staff',
            'branch_id' => $branch->id,
        ]);

        $ingredient = Ingredient::create([
            'name' => 'Flavor Powder',
            'unit' => 'g',
        ]);

        $product = Product::create([
            'name' => 'Classic Milk Tea',
            'category' => 'Milk Tea',
            'price' => 65.00,
        ]);

        Recipe::create([
            'product_id' => $product->id,
            'ingredient_id' => $ingredient->id,
            'quantity_required' => 30,
        ]);

        BranchStock::create([
            'branch_id' => $branch->id,
            'ingredient_id' => $ingredient->id,
            'current_quantity' => 500,
            'min_threshold' => 100,
        ]);
    }
}
