<?php

namespace Tests\Feature;

use App\Models\Communication\Announcement;
use App\Models\Communication\Message;
use App\Models\Communication\MessageParticipant;
use App\Models\Communication\MessageThread;
use App\Models\User;
use Tests\TestCase;

class CommunicationTest extends TestCase
{
    public function test_message_thread_can_be_created(): void
    {
        $user = User::factory()->create();

        $thread = MessageThread::create([
            'subject' => 'Test Subject',
            'type' => 'direct',
            'created_by' => $user->id,
        ]);

        $this->assertNotNull($thread->id);
        $this->assertEquals('Test Subject', $thread->subject);
        $this->assertEquals('direct', $thread->type);
        $this->assertEquals($user->id, $thread->created_by);
    }

    public function test_message_can_be_created(): void
    {
        $user = User::factory()->create();

        $thread = MessageThread::create([
            'subject' => 'Test Subject',
            'type' => 'direct',
            'created_by' => $user->id,
        ]);

        $message = Message::create([
            'thread_id' => $thread->id,
            'sender_id' => $user->id,
            'content' => 'Test message content',
        ]);

        $this->assertNotNull($message->id);
        $this->assertEquals($thread->id, $message->thread_id);
        $this->assertEquals($user->id, $message->sender_id);
        $this->assertEquals('Test message content', $message->content);
        $this->assertFalse($message->is_read);
    }

    public function test_message_participants_can_be_added(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $thread = MessageThread::create([
            'subject' => 'Test Subject',
            'type' => 'direct',
            'created_by' => $user1->id,
        ]);

        $participant1 = MessageParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $user1->id,
            'is_admin' => true,
        ]);

        $participant2 = MessageParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $user2->id,
            'is_admin' => false,
        ]);

        $this->assertNotNull($participant1->id);
        $this->assertNotNull($participant2->id);
        $this->assertEquals($thread->id, $participant1->thread_id);
        $this->assertEquals($thread->id, $participant2->thread_id);
        $this->assertTrue($participant1->is_admin);
        $this->assertFalse($participant2->is_admin);
    }

    public function test_announcement_can_be_created(): void
    {
        $user = User::factory()->create();

        $announcement = Announcement::create([
            'created_by' => $user->id,
            'title' => 'Test Announcement',
            'content' => 'This is a test announcement',
            'type' => 'general',
            'target_type' => 'all',
            'is_active' => true,
        ]);

        $this->assertNotNull($announcement->id);
        $this->assertEquals('Test Announcement', $announcement->title);
        $this->assertEquals($user->id, $announcement->created_by);
        $this->assertEquals('general', $announcement->type);
        $this->assertEquals('all', $announcement->target_type);
        $this->assertTrue($announcement->is_active);
    }

    public function test_announcement_with_target_users(): void
    {
        $user = User::factory()->create();
        $targetUser1 = User::factory()->create();
        $targetUser2 = User::factory()->create();

        $announcement = Announcement::create([
            'created_by' => $user->id,
            'title' => 'Targeted Announcement',
            'content' => 'This is a targeted announcement',
            'type' => 'general',
            'target_type' => 'users',
            'target_users' => [$targetUser1->id, $targetUser2->id],
            'is_active' => true,
        ]);

        $this->assertNotNull($announcement->id);
        $this->assertIsArray($announcement->target_users);
        $this->assertCount(2, $announcement->target_users);
    }

    public function test_message_can_be_marked_as_read(): void
    {
        $user = User::factory()->create();

        $thread = MessageThread::create([
            'subject' => 'Test Subject',
            'type' => 'direct',
            'created_by' => $user->id,
        ]);

        $message = Message::create([
            'thread_id' => $thread->id,
            'sender_id' => $user->id,
            'content' => 'Test message content',
            'is_read' => false,
        ]);

        $this->assertFalse($message->is_read);
        $this->assertNull($message->read_at);

        $message->update([
            'is_read' => true,
            'read_at' => date('Y-m-d H:i:s'),
        ]);

        $updatedMessage = Message::find($message->id);
        $this->assertTrue($updatedMessage->is_read);
        $this->assertNotNull($updatedMessage->read_at);
    }

    public function test_message_with_attachment(): void
    {
        $user = User::factory()->create();

        $thread = MessageThread::create([
            'subject' => 'Test Subject',
            'type' => 'direct',
            'created_by' => $user->id,
        ]);

        $message = Message::create([
            'thread_id' => $thread->id,
            'sender_id' => $user->id,
            'content' => 'Test message with attachment',
            'attachment_url' => 'https://example.com/file.pdf',
            'attachment_type' => 'document',
        ]);

        $this->assertNotNull($message->id);
        $this->assertEquals('https://example.com/file.pdf', $message->attachment_url);
        $this->assertEquals('document', $message->attachment_type);
    }

    public function test_announcement_expiration(): void
    {
        $user = User::factory()->create();

        $announcement = Announcement::create([
            'created_by' => $user->id,
            'title' => 'Expiring Announcement',
            'content' => 'This announcement will expire',
            'type' => 'general',
            'target_type' => 'all',
            'is_active' => true,
            'published_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
        ]);

        $this->assertNotNull($announcement->id);
        $this->assertNotNull($announcement->expires_at);
        $this->assertTrue($announcement->is_active);
    }

    public function test_message_thread_relationships(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $thread = MessageThread::create([
            'subject' => 'Test Subject',
            'type' => 'direct',
            'created_by' => $user1->id,
        ]);

        $message = Message::create([
            'thread_id' => $thread->id,
            'sender_id' => $user1->id,
            'content' => 'Test message content',
        ]);

        $participant = MessageParticipant::create([
            'thread_id' => $thread->id,
            'user_id' => $user2->id,
            'is_admin' => false,
        ]);

        $this->assertEquals($thread->id, $message->thread->id);
        $this->assertEquals($thread->id, $participant->thread_id);

        $reloadedThread = MessageThread::with(['messages', 'participants'])->find($thread->id);
        $this->assertNotNull($reloadedThread);
        $this->assertCount(1, $reloadedThread->messages);
        $this->assertCount(2, $reloadedThread->participants);
    }
}
