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

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ── Global Super Admin Accounts ────────────────────────────────
        // Note: password and pin use the 'hashed' cast in User model,
        // so we pass plain text and the cast hashes automatically.

        User::create([
            'name' => 'Admin Owner',
            'email' => 'admin@inventory.ph',
            'password' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
            'branch_id' => null,
        ]);

        // Canonical demo account referenced by README + Postman collection.
        User::create([
            'name' => 'Owner Admin',
            'email' => 'owner@inventory.test',
            'password' => 'password123',
            'role' => User::ROLE_SUPER_ADMIN,
            'branch_id' => null,
        ]);

        // ── 6 Operating Branches ───────────────────────────────────────

        $branches = collect([
            ['name' => 'Branch QC', 'location' => 'Quezon City, Metro Manila', 'status' => 'active'],
            ['name' => 'Branch Makati', 'location' => 'Makati City, Metro Manila', 'status' => 'active'],
            ['name' => 'Branch BGC', 'location' => 'Bonifacio Global City, Taguig', 'status' => 'active'],
            ['name' => 'Branch Cebu', 'location' => 'Cebu City, Cebu', 'status' => 'active'],
            ['name' => 'Branch Davao', 'location' => 'Davao City, Davao del Sur', 'status' => 'active'],
            ['name' => 'Branch Clark', 'location' => 'Clark Freeport Zone, Pampanga', 'status' => 'active'],
        ])->map(fn ($data) => Branch::create($data));

        // Canonical demo manager referenced by README + Postman collection.
        User::create([
            'name' => 'Manager Branch QC',
            'email' => 'manager@inventory.test',
            'password' => 'password123',
            'role' => User::ROLE_MANAGER,
            'branch_id' => $branches[0]->id,
        ]);

        // Demo staff account (pin login) for role-gate testing.
        User::create([
            'name' => 'Staff Juan',
            'pin' => '1111',
            'role' => User::ROLE_STAFF,
            'branch_id' => $branches[0]->id,
        ]);

        // ── 10 Branch Manager Accounts (PIN login, mapped to branches) ──

        $managerData = [
            ['name' => 'Juan Cruz',          'pin' => '1234', 'branch' => 0], // QC
            ['name' => 'Maria Santos',       'pin' => '2345', 'branch' => 0], // QC
            ['name' => 'Pedro Reyes',        'pin' => '3456', 'branch' => 1], // Makati
            ['name' => 'Ana Gonzales',       'pin' => '4567', 'branch' => 1], // Makati
            ['name' => 'Jose Mercado',       'pin' => '5678', 'branch' => 2], // BGC
            ['name' => 'Luisa Fernandez',    'pin' => '6789', 'branch' => 2], // BGC
            ['name' => 'Carlos Ramos',       'pin' => '7890', 'branch' => 3], // Cebu
            ['name' => 'Elena Torres',       'pin' => '8901', 'branch' => 4], // Davao
            ['name' => 'Miguel Villanueva',  'pin' => '9012', 'branch' => 4], // Davao
            ['name' => 'Sofia Lim',          'pin' => '0123', 'branch' => 5], // Clark
        ];

        foreach ($managerData as $manager) {
            User::create([
                'name' => $manager['name'],
                'pin' => $manager['pin'],
                'role' => User::ROLE_MANAGER,
                'branch_id' => $branches[$manager['branch']]->id,
            ]);
        }

        // ── 10 Desktop Branch Manager Accounts (email + password auth) ──
        // These managers log in via the unified web form using email + shared password.

        $desktopManagers = [
            ['name' => 'Juan Cruz',          'email' => 'juan.cruz@nita.com',        'branch_idx' => 0], // QC
            ['name' => 'Maria Santos',       'email' => 'maria.santos@nita.com',     'branch_idx' => 0], // QC
            ['name' => 'Pedro Reyes',        'email' => 'pedro.reyes@nita.com',      'branch_idx' => 1], // Makati
            ['name' => 'Ana Gonzales',       'email' => 'ana.gonzales@nita.com',     'branch_idx' => 1], // Makati
            ['name' => 'Jose Mercado',       'email' => 'jose.mercado@nita.com',     'branch_idx' => 2], // BGC
            ['name' => 'Luisa Fernandez',    'email' => 'luisa.fernandez@nita.com',  'branch_idx' => 2], // BGC
            ['name' => 'Carlos Ramos',       'email' => 'carlos.ramos@nita.com',     'branch_idx' => 3], // Cebu
            ['name' => 'Elena Torres',       'email' => 'elena.torres@nita.com',     'branch_idx' => 4], // Davao
            ['name' => 'Miguel Villanueva',  'email' => 'miguel.villanueva@nita.com', 'branch_idx' => 4], // Davao
            ['name' => 'Sofia Lim',          'email' => 'sofia.lim@nita.com',        'branch_idx' => 5], // Clark
        ];

        foreach ($desktopManagers as $dm) {
            User::create([
                'name' => $dm['name'],
                'email' => $dm['email'],
                'password' => 'password123',
                'role' => User::ROLE_MANAGER,
                'branch_id' => $branches[$dm['branch_idx']]->id,
            ]);
        }

        // ── Ingredients — raw materials master list ────────────────────

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

        // ── Menu + recipes (formula per unit sold) ─────────────────────

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

        // ── Starting stock — every ingredient stocked at every branch ──

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

        foreach ($branches as $branch) {
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
