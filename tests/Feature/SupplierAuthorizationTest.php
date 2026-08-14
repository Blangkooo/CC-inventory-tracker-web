<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_list_suppliers(): void
    {
        $staff = User::factory()->create();

        $this->actingAs($staff)->get('/suppliers')->assertForbidden();
    }

    public function test_staff_cannot_create_supplier(): void
    {
        $staff = User::factory()->create();

        $this->actingAs($staff)->postJson('/suppliers', ['name' => 'Sneaky Supplier'])
            ->assertForbidden();

        $this->assertDatabaseMissing('suppliers', ['name' => 'Sneaky Supplier']);
    }

    public function test_staff_cannot_update_or_delete_supplier(): void
    {
        $staff = User::factory()->create();
        $supplier = Supplier::create(['name' => 'Existing Supplier']);

        $this->actingAs($staff)->putJson("/suppliers/{$supplier->id}", ['name' => 'Renamed'])
            ->assertForbidden();
        $this->actingAs($staff)->deleteJson("/suppliers/{$supplier->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'Existing Supplier']);
    }

    public function test_staff_cannot_link_ingredient_or_add_purchase(): void
    {
        $staff = User::factory()->create();
        $supplier = Supplier::create(['name' => 'Existing Supplier']);
        $ingredient = Ingredient::create(['name' => 'Sugar', 'unit' => 'kg']);

        $this->actingAs($staff)->postJson("/suppliers/{$supplier->id}/ingredients", [
            'ingredient_id' => $ingredient->id,
        ])->assertForbidden();

        $this->actingAs($staff)->postJson("/suppliers/{$supplier->id}/purchases", [
            'ingredient_id' => $ingredient->id,
            'unit_price' => 10,
            'quantity' => 1,
            'purchased_at' => now()->toDateString(),
        ])->assertForbidden();
    }

    public function test_manager_can_create_and_list_suppliers(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)->get('/suppliers')->assertOk();

        $this->actingAs($manager)->postJson('/suppliers', ['name' => 'New Supplier'])
            ->assertCreated();

        $this->assertDatabaseHas('suppliers', ['name' => 'New Supplier']);
    }

    public function test_super_admin_can_delete_supplier(): void
    {
        $owner = User::factory()->superAdmin()->create();
        $supplier = Supplier::create(['name' => 'Doomed Supplier']);

        $this->actingAs($owner)->deleteJson("/suppliers/{$supplier->id}")->assertOk();

        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }
}
