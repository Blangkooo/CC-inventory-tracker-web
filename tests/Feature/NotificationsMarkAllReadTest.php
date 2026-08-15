<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationsMarkAllReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_all_read_marks_only_the_authenticated_users_unread_notifications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $unread1 = Notification::create(['user_id' => $user->id, 'title' => 'A', 'message' => 'a']);
        $unread2 = Notification::create(['user_id' => $user->id, 'title' => 'B', 'message' => 'b']);
        $alreadyRead = Notification::create(['user_id' => $user->id, 'title' => 'C', 'message' => 'c', 'read_at' => now()]);
        $othersUnread = Notification::create(['user_id' => $otherUser->id, 'title' => 'D', 'message' => 'd']);

        $this->actingAs($user)->putJson('/notifications/read-all')
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNotNull($unread1->fresh()->read_at);
        $this->assertNotNull($unread2->fresh()->read_at);
        $this->assertNotNull($alreadyRead->fresh()->read_at);
        $this->assertNull($othersUnread->fresh()->read_at);
    }
}
