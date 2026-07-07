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
        $branchQc = Branch::create([
            'name' => 'Branch QC',
            'location' => 'Quezon City',
        ]);

        $branchManila = Branch::create([
            'name' => 'Branch Manila',
            'location' => 'Manila',
        ]);

        User::create([
            'name' => 'Owner Admin',
            'email' => 'owner@inventory.test',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_SUPER_ADMIN,
            'branch_id' => null,
        ]);

        User::create([
            'name' => 'Manager Branch QC',
            'email' => 'manager@inventory.test',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_MANAGER,
            'branch_id' => $branchQc->id,
        ]);

        User::create([
            'name' => 'Staff Juan',
            'pin' => Hash::make('1234'),
            'role' => User::ROLE_STAFF,
            'branch_id' => $branchQc->id,
        ]);

        // Ingredients — raw materials master list, spanning the three
        // target-industry categories from the project brief.
        $ingredients = collect([
            'flavor_powder' => Ingredient::create(['name' => 'Flavor Powder', 'unit' => 'g']),
            'milk_tea_cup' => Ingredient::create(['name' => 'Milk Tea Cup', 'unit' => 'pcs']),
            'cup_wrapper' => Ingredient::create(['name' => 'Cup Wrapper', 'unit' => 'pcs']),
            'tapioca_pearls' => Ingredient::create(['name' => 'Tapioca Pearls', 'unit' => 'g']),
            'siomai_wrapper' => Ingredient::create(['name' => 'Siomai Wrapper', 'unit' => 'pcs']),
            'pork_filling' => Ingredient::create(['name' => 'Pork Filling', 'unit' => 'g']),
            'steamer_cup' => Ingredient::create(['name' => 'Steamer Cup', 'unit' => 'pcs']),
            'flour' => Ingredient::create(['name' => 'Flour', 'unit' => 'g']),
            'sugar' => Ingredient::create(['name' => 'Sugar', 'unit' => 'g']),
            'butter' => Ingredient::create(['name' => 'Butter', 'unit' => 'g']),
        ]);

        // Menu — one product per line, formula (recipe) defined right after each.
        $classicMilkTea = Product::create(['name' => 'Classic Milk Tea', 'category' => 'Milk Tea', 'price' => 65.00]);
        Recipe::create(['product_id' => $classicMilkTea->id, 'ingredient_id' => $ingredients['flavor_powder']->id, 'quantity_required' => 30]);
        Recipe::create(['product_id' => $classicMilkTea->id, 'ingredient_id' => $ingredients['milk_tea_cup']->id, 'quantity_required' => 1]);
        Recipe::create(['product_id' => $classicMilkTea->id, 'ingredient_id' => $ingredients['cup_wrapper']->id, 'quantity_required' => 1]);

        $taroMilkTea = Product::create(['name' => 'Taro Milk Tea', 'category' => 'Milk Tea', 'price' => 70.00]);
        Recipe::create(['product_id' => $taroMilkTea->id, 'ingredient_id' => $ingredients['flavor_powder']->id, 'quantity_required' => 25]);
        Recipe::create(['product_id' => $taroMilkTea->id, 'ingredient_id' => $ingredients['tapioca_pearls']->id, 'quantity_required' => 20]);
        Recipe::create(['product_id' => $taroMilkTea->id, 'ingredient_id' => $ingredients['milk_tea_cup']->id, 'quantity_required' => 1]);
        Recipe::create(['product_id' => $taroMilkTea->id, 'ingredient_id' => $ingredients['cup_wrapper']->id, 'quantity_required' => 1]);

        $porkSiomai = Product::create(['name' => 'Pork Siomai (4pcs)', 'category' => 'Siomai', 'price' => 55.00]);
        Recipe::create(['product_id' => $porkSiomai->id, 'ingredient_id' => $ingredients['siomai_wrapper']->id, 'quantity_required' => 4]);
        Recipe::create(['product_id' => $porkSiomai->id, 'ingredient_id' => $ingredients['pork_filling']->id, 'quantity_required' => 120]);
        Recipe::create(['product_id' => $porkSiomai->id, 'ingredient_id' => $ingredients['steamer_cup']->id, 'quantity_required' => 1]);

        $pandesal = Product::create(['name' => 'Pandesal (5pcs)', 'category' => 'Bakery', 'price' => 40.00]);
        Recipe::create(['product_id' => $pandesal->id, 'ingredient_id' => $ingredients['flour']->id, 'quantity_required' => 200]);
        Recipe::create(['product_id' => $pandesal->id, 'ingredient_id' => $ingredients['sugar']->id, 'quantity_required' => 30]);
        Recipe::create(['product_id' => $pandesal->id, 'ingredient_id' => $ingredients['butter']->id, 'quantity_required' => 20]);

        // Starting stock per branch — every ingredient stocked at both branches.
        $startingStock = [
            'flavor_powder' => ['qty' => 2000, 'min' => 300],
            'milk_tea_cup' => ['qty' => 300, 'min' => 50],
            'cup_wrapper' => ['qty' => 300, 'min' => 50],
            'tapioca_pearls' => ['qty' => 1500, 'min' => 200],
            'siomai_wrapper' => ['qty' => 400, 'min' => 60],
            'pork_filling' => ['qty' => 5000, 'min' => 500],
            'steamer_cup' => ['qty' => 150, 'min' => 30],
            'flour' => ['qty' => 4000, 'min' => 500],
            'sugar' => ['qty' => 1000, 'min' => 150],
            'butter' => ['qty' => 800, 'min' => 100],
        ];

        foreach ([$branchQc, $branchManila] as $branch) {
            foreach ($startingStock as $key => $levels) {
                BranchStock::create([
                    'branch_id' => $branch->id,
                    'ingredient_id' => $ingredients[$key]->id,
                    'current_quantity' => $levels['qty'],
                    'min_threshold' => $levels['min'],
                ]);
            }
        }
    }
}
