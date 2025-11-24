<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Communication\Message;
use App\Models\Communication\MessageThread;
use App\Models\Communication\Announcement;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use PHPUnit\Framework\TestCase;
use Hyperf\Testing\Client;

/**
 * @internal
 * @coversNothing
 */
class CommunicationTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = make(Client::class);
    }

    public function test_user_can_create_and_retrieve_announcement()
    {
        // Create a user for testing
        $user = User::factory()->create();
        
        // Login to get JWT token
        $loginResponse = $this->client->post('/auth/login', [
            'email' => $user->email,
            'password' => 'password' // assuming default password
        ]);
        
        $loginData = json_decode($loginResponse->getBody()->getContents(), true);
        $token = $loginData['data']['token'] ?? null;
        
        $this->assertNotNull($token, 'Failed to login user');
        
        if ($token) {
            // Create an announcement
            $createResponse = $this->client->post('/communication/announcements', [
                'title' => 'Test Announcement',
                'content' => 'This is a test announcement',
                'type' => 'general'
            ], [
                'Authorization' => 'Bearer ' . $token
            ]);
            
            $createData = json_decode($createResponse->getBody()->getContents(), true);
            $this->assertTrue($createData['success'], 'Failed to create announcement');
            
            // Retrieve announcements
            $getResponse = $this->client->get('/communication/announcements', [], [
                'Authorization' => 'Bearer ' . $token
            ]);
            
            $getData = json_decode($getResponse->getBody()->getContents(), true);
            $this->assertTrue($getData['success'], 'Failed to retrieve announcements');
        }
    }
    
    public function test_user_can_send_and_receive_messages()
    {
        // Create users for testing
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        
        // Login sender to get JWT token
        $loginResponse = $this->client->post('/auth/login', [
            'email' => $sender->email,
            'password' => 'password'
        ]);
        
        $loginData = json_decode($loginResponse->getBody()->getContents(), true);
        $token = $loginData['data']['token'] ?? null;
        
        $this->assertNotNull($token, 'Failed to login sender user');
        
        if ($token) {
            // Create a message thread first (in a real scenario, this would be done via an endpoint)
            $thread = MessageThread::create([
                'subject' => 'Test Thread',
                'type' => 'private',
                'created_by' => $sender->id
            ]);
            
            // Add both users as participants
            \App\Models\Communication\ThreadParticipant::create([
                'thread_id' => $thread->id,
                'user_id' => $sender->id,
                'is_admin' => true
            ]);
            
            \App\Models\Communication\ThreadParticipant::create([
                'thread_id' => $thread->id,
                'user_id' => $recipient->id,
                'is_admin' => false
            ]);
            
            // Send a message
            $messageResponse = $this->client->post('/communication/messages', [
                'thread_id' => $thread->id,
                'content' => 'Hello, this is a test message!',
                'recipient_id' => $recipient->id
            ], [
                'Authorization' => 'Bearer ' . $token
            ]);
            
            $messageData = json_decode($messageResponse->getBody()->getContents(), true);
            $this->assertTrue($messageData['success'], 'Failed to send message');
            
            // Retrieve messages from the thread
            $threadResponse = $this->client->get("/communication/messages/threads/{$thread->id}", [], [
                'Authorization' => 'Bearer ' . $token
            ]);
            
            $threadData = json_decode($threadResponse->getBody()->getContents(), true);
            $this->assertTrue($threadData['success'], 'Failed to retrieve thread messages');
        }
    }
}