<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Meeting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MeetingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Only seeds if meetings table is empty.
     */
    public function run(): void
    {
        // Only seed if meetings table is empty
        if (Meeting::count() > 0) {
            $this->command->info('Meetings table already has data. Skipping seed.');
            return;
        }

        $this->command->info('Seeding meetings table with sample data...');

        $branches = Branch::all();
        $admin = User::where('email', 'admin@inventory.ph')->first() ?? User::first();

        if ($branches->isEmpty() || !$admin) {
            $this->command->warn('No branches or admin user found. Skipping meeting seed.');
            return;
        }

        $today = Carbon::now();
        $startOfWeek = $today->copy()->startOfWeek();

        $meetings = [
            // Current week meetings
            [
                'title' => 'Weekly Team Sync',
                'description' => 'Discuss weekly goals and review progress on current projects.',
                'date' => $startOfWeek->copy()->addDays(1), // Tuesday
                'start_time' => '10:00 AM',
                'end_time' => '11:00 AM',
                'meeting_type' => 'meeting',
                'location' => 'Conference Room',
                'status' => 'scheduled',
                'branch_id' => $branches[0]->id,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Inventory Audit',
                'description' => 'Monthly inventory check and reconciliation.',
                'date' => $startOfWeek->copy()->addDays(2), // Wednesday
                'start_time' => '2:00 PM',
                'end_time' => '4:00 PM',
                'meeting_type' => 'task',
                'location' => 'Warehouse',
                'status' => 'scheduled',
                'branch_id' => $branches[0]->id,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Store Update with Manager Lin',
                'description' => 'Review store performance and discuss upcoming promotions.',
                'date' => $startOfWeek->copy()->addDays(3), // Thursday
                'start_time' => '10:00 AM',
                'end_time' => '11:00 AM',
                'meeting_type' => 'meeting',
                'location' => 'Online',
                'status' => 'scheduled',
                'branch_id' => $branches[1]->id,
                'created_by' => $admin->id,
            ],
            // Next week meetings
            [
                'title' => 'Team Meeting',
                'description' => 'Monthly team building and performance review.',
                'date' => $today->copy()->addDays(7)->startOfWeek()->addDays(3), // Next Thursday
                'start_time' => '9:00 AM',
                'end_time' => '10:30 AM',
                'meeting_type' => 'meeting',
                'location' => 'Main Office',
                'status' => 'scheduled',
                'branch_id' => $branches[2]->id,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Perishable Delivery',
                'description' => 'Receive and check delivery of perishable goods.',
                'date' => $today->copy()->addDays(7)->startOfWeek()->addDays(3), // Next Thursday
                'start_time' => '9:00 AM',
                'end_time' => '10:00 AM',
                'meeting_type' => 'task',
                'location' => 'Loading Dock',
                'status' => 'scheduled',
                'branch_id' => $branches[3]->id,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Staff Training Session',
                'description' => 'New POS system training for all staff members.',
                'date' => $today->copy()->addDays(10), // In 10 days
                'start_time' => '1:00 PM',
                'end_time' => '3:00 PM',
                'meeting_type' => 'event',
                'location' => 'Training Room',
                'status' => 'scheduled',
                'branch_id' => $branches[0]->id,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Budget Review Meeting',
                'description' => 'Quarterly budget review and financial planning.',
                'date' => $today->copy()->addDays(14), // In 2 weeks
                'start_time' => '11:00 AM',
                'end_time' => '12:30 PM',
                'meeting_type' => 'meeting',
                'location' => 'Board Room',
                'status' => 'scheduled',
                'branch_id' => $branches[1]->id,
                'created_by' => $admin->id,
            ],
        ];

        foreach ($meetings as $meetingData) {
            Meeting::create($meetingData);
        }

        $this->command->info('Successfully seeded ' . count($meetings) . ' meetings.');
    }
}
