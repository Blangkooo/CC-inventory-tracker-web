<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_login_throttles_after_5_failed_attempts_per_minute(): void
    {
        User::factory()->create(['email' => 'throttle-web@test.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'throttle-web@test.com', 'password' => 'wrong-password']);
        }

        $response = $this->post('/login', ['email' => 'throttle-web@test.com', 'password' => 'wrong-password']);

        $response->assertStatus(429);
    }

    public function test_staff_pin_login_throttles_after_5_failed_attempts_per_minute(): void
    {
        $branch = Branch::factory()->create();
        User::factory()->withPin('1234')->create(['branch_id' => $branch->id]);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/staff/login', ['branch_id' => $branch->id, 'pin' => '0000']);
        }

        $response = $this->post('/staff/login', ['branch_id' => $branch->id, 'pin' => '0000']);

        $response->assertStatus(429);
    }

    public function test_correct_password_still_works_under_the_limit(): void
    {
        User::factory()->create(['email' => 'ok-web@test.com', 'password' => 'correct-password']);

        $response = $this->post('/login', ['email' => 'ok-web@test.com', 'password' => 'correct-password']);

        $response->assertRedirect();
        $this->assertAuthenticated();
    }
}
