<?php

namespace Database\Seeders;

use App\Models\Branch;
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
        // ── 1 Global Owner Account ─────────────────────────────────────
        // Note: password and pin use the 'hashed' cast in User model,
        // so we pass plain text and the cast hashes automatically.

        User::create([
            'name' => 'Admin Owner',
            'email' => 'admin@inventory.ph',
            'password' => 'password',
            'role' => 'owner',
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

        // ── 10 Branch Manager Accounts (mapped to branches) ────────────

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
                'role' => 'manager',
                'branch_id' => $branches[$manager['branch']]->id,
            ]);
        }

        // ── 10 Desktop Branch Manager Accounts (email + password auth) ──
        // These managers log in via the unified web form using email + shared password.

        $desktopManagers = [
            ['name' => 'Juan Cruz',         'email' => 'juan.cruz@nita.com',       'branch_idx' => 0], // QC
            ['name' => 'Maria Santos',      'email' => 'maria.santos@nita.com',    'branch_idx' => 0], // QC
            ['name' => 'Pedro Reyes',       'email' => 'pedro.reyes@nita.com',     'branch_idx' => 1], // Makati
            ['name' => 'Ana Gonzales',      'email' => 'ana.gonzales@nita.com',    'branch_idx' => 1], // Makati
            ['name' => 'Jose Mercado',       'email' => 'jose.mercado@nita.com',   'branch_idx' => 2], // BGC
            ['name' => 'Luisa Fernandez',    'email' => 'luisa.fernandez@nita.com', 'branch_idx' => 2], // BGC
            ['name' => 'Carlos Ramos',       'email' => 'carlos.ramos@nita.com',    'branch_idx' => 3], // Cebu
            ['name' => 'Elena Torres',       'email' => 'elena.torres@nita.com',   'branch_idx' => 4], // Davao
            ['name' => 'Miguel Villanueva',  'email' => 'miguel.villanueva@nita.com', 'branch_idx' => 4], // Davao
            ['name' => 'Sofia Lim',          'email' => 'sofia.lim@nita.com',      'branch_idx' => 5], // Clark
        ];

        foreach ($desktopManagers as $dm) {
            User::create([
                'name' => $dm['name'],
                'email' => $dm['email'],
                'password' => 'password123',
                'role' => 'manager',
                'branch_id' => $branches[$dm['branch_idx']]->id,
            ]);
        }
    }
}
