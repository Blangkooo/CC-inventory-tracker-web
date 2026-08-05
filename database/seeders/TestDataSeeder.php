<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Skip if data already exists
        if (User::count() > 0) {
            $this->command->info('Users already exist. Skipping TestDataSeeder.');
            return;
        }

        $branch = Branch::create([
            'name'     => 'Main Branch',
            'location' => 'Quezon City',
            'status'   => 'active',
        ]);

        User::create([
            'name'      => 'Juan',
            'email'     => 'juan@test.com',
            'pin'       => '1234',
            'password'  => 'password',
            'role'      => User::ROLE_STAFF,
            'branch_id' => $branch->id,
        ]);

        $ingredient = Ingredient::create(['name' => 'Tea Powder', 'unit' => 'g']);

        $product = Product::create([
            'name'     => 'Milk Tea',
            'category' => 'Drinks',
            'price'    => 45.00,
        ]);

        Recipe::create([
            'product_id'        => $product->id,
            'ingredient_id'     => $ingredient->id,
            'size'              => Recipe::SIZE_REGULAR,
            'quantity_required' => 50,
        ]);

        BranchStock::create([
            'branch_id'         => $branch->id,
            'ingredient_id'     => $ingredient->id,
            'current_quantity'  => 1000,
            'min_threshold'     => 100,
        ]);

        $this->command->info('Done! Branch ID: ' . $branch->id . ', Product ID: ' . $product->id);
    }
}
