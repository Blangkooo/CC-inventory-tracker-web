<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsRealDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_profit_margin_is_computed_from_real_recipe_costs_not_a_fake_placeholder(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $branch = Branch::factory()->create();

        $product = Product::create(['name' => 'Milk Tea', 'category' => 'Drinks', 'price' => 100, 'is_active' => true]);
        $ingredient = Ingredient::create(['name' => 'Milk Powder', 'unit' => 'g']);
        $supplier = Supplier::create(['name' => 'Dairy Co', 'is_active' => true]);
        $supplier->ingredients()->attach($ingredient->id, ['unit_cost' => 2, 'is_primary' => true]);
        Recipe::create(['product_id' => $product->id, 'ingredient_id' => $ingredient->id, 'size' => 'regular', 'quantity_required' => 10]);

        Transaction::create([
            'branch_id' => $branch->id, 'user_id' => $admin->id, 'product_id' => $product->id,
            'client_uuid' => (string) \Illuminate\Support\Str::uuid(),
            'quantity' => 1, 'unit_price' => 100, 'total_amount' => 100, 'sync_status' => 'synced',
        ]);

        $response = $this->actingAs($admin)->get('/analytics');

        $response->assertOk();
        // Cost = 10 * 2 = 20, price = 100 → margin = (100-20)/100 * 100 = 80%.
        $response->assertViewHas('profitMargin', 80.0);
    }

    public function test_profit_margin_is_null_not_a_fake_number_when_no_cost_data_exists(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $branch = Branch::factory()->create();

        $product = Product::create(['name' => 'Mystery Item', 'category' => 'Drinks', 'price' => 100, 'is_active' => true]);

        Transaction::create([
            'branch_id' => $branch->id, 'user_id' => $admin->id, 'product_id' => $product->id,
            'client_uuid' => (string) \Illuminate\Support\Str::uuid(),
            'quantity' => 1, 'unit_price' => 100, 'total_amount' => 100, 'sync_status' => 'synced',
        ]);

        $response = $this->actingAs($admin)->get('/analytics');

        $response->assertOk();
        $response->assertViewHas('profitMargin', null);
    }

    public function test_order_trend_reflects_real_month_over_month_order_counts_not_a_fake_placeholder(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $branch = Branch::factory()->create();
        $product = Product::create(['name' => 'Milk Tea', 'category' => 'Drinks', 'price' => 100, 'is_active' => true]);

        // Last month: 2 orders.
        for ($i = 0; $i < 2; $i++) {
            $txn = Transaction::create([
                'branch_id' => $branch->id, 'user_id' => $admin->id, 'product_id' => $product->id,
                'client_uuid' => (string) \Illuminate\Support\Str::uuid(),
            'quantity' => 1, 'unit_price' => 100, 'total_amount' => 100, 'sync_status' => 'synced',
            ]);
            $txn->forceFill(['created_at' => now()->subMonth()])->save();
        }

        // This month: 3 orders.
        for ($i = 0; $i < 3; $i++) {
            Transaction::create([
                'branch_id' => $branch->id, 'user_id' => $admin->id, 'product_id' => $product->id,
                'client_uuid' => (string) \Illuminate\Support\Str::uuid(),
            'quantity' => 1, 'unit_price' => 100, 'total_amount' => 100, 'sync_status' => 'synced',
            ]);
        }

        $response = $this->actingAs($admin)->get('/analytics');

        $response->assertOk();
        // (3 - 2) / 2 * 100 = 50% increase.
        $response->assertViewHas('orderTrend', 50);
    }

    public function test_ajax_analytics_endpoint_returns_the_same_real_figures_as_the_page(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $branch = Branch::factory()->create();
        $product = Product::create(['name' => 'Milk Tea', 'category' => 'Drinks', 'price' => 100, 'is_active' => true]);

        Transaction::create([
            'branch_id' => $branch->id, 'user_id' => $admin->id, 'product_id' => $product->id,
            'client_uuid' => (string) \Illuminate\Support\Str::uuid(),
            'quantity' => 1, 'unit_price' => 100, 'total_amount' => 100, 'sync_status' => 'synced',
        ]);

        $response = $this->actingAs($admin)->getJson('/ajax/analytics');

        $response->assertOk();
        $response->assertJson(['profitMargin' => null, 'totalOrders' => 1]);
    }
}
