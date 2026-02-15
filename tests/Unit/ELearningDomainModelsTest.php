<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\ELearning\Assignment;
use App\Models\ELearning\Quiz;
use App\Models\ELearning\Discussion;
use App\Models\ELearning\DiscussionReply;
use App\Models\ELearning\LearningMaterial;
use App\Models\ELearning\VideoConference;
use App\Models\ELearning\VirtualClass;
use App\Models\User;
use Hypervel\Foundation\Testing\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ELearningDomainModelsTest extends TestCase
{
    /**
     * Test assignment model configuration.
     */
    public function testAssignmentModelConfiguration(): void
    {
        $assignment = new Assignment();
        
        $this->assertEquals('id', $assignment->getKeyName());
        $this->assertIsArray($assignment->getFillable());
        $this->assertIsArray($assignment->getCasts());
    }

    /**
     * Test assignment relationships.
     */
    public function testAssignmentRelationships(): void
    {
        $assignment = new Assignment();
        
        $creatorRelation = $assignment->creator();
        $this->assertEquals('created_by', $creatorRelation->getForeignKeyName());
    }

    /**
     * Test quiz model configuration.
     */
    public function testQuizModelConfiguration(): void
    {
        $quiz = new Quiz();
        
        $this->assertEquals('id', $quiz->getKeyName());
        $this->assertIsArray($quiz->getFillable());
        $this->assertIsArray($quiz->getCasts());
    }

    /**
     * Test quiz relationships.
     */
    public function testQuizRelationships(): void
    {
        $quiz = new Quiz();
        
        $creatorRelation = $quiz->creator();
        $this->assertEquals('created_by', $creatorRelation->getForeignKeyName());
    }

    /**
     * Test discussion model configuration.
     */
    public function testDiscussionModelConfiguration(): void
    {
        $discussion = new Discussion();
        
        $this->assertEquals('id', $discussion->getKeyName());
        $this->assertIsArray($discussion->getFillable());
        $this->assertIsArray($discussion->getCasts());
    }

    /**
     * Test discussion relationships.
     */
    public function testDiscussionRelationships(): void
    {
        $discussion = new Discussion();
        
        $creatorRelation = $discussion->creator();
        $this->assertEquals('created_by', $creatorRelation->getForeignKeyName());
        
        $repliesRelation = $discussion->replies();
        $this->assertEquals('discussion_id', $repliesRelation->getForeignKeyName());
    }

    /**
     * Test discussion reply model configuration.
     */
    public function testDiscussionReplyModelConfiguration(): void
    {
        $reply = new DiscussionReply();
        
        $this->assertEquals('id', $reply->getKeyName());
        $this->assertIsArray($reply->getFillable());
    }

    /**
     * Test discussion reply relationships.
     */
    public function testDiscussionReplyRelationships(): void
    {
        $reply = new DiscussionReply();
        
        $creatorRelation = $reply->creator();
        $this->assertEquals('created_by', $creatorRelation->getForeignKeyName());
        
        $discussionRelation = $reply->discussion();
        $this->assertEquals('discussion_id', $discussionRelation->getForeignKeyName());
    }

    /**
     * Test learning material model configuration.
     */
    public function testLearningMaterialModelConfiguration(): void
    {
        $material = new LearningMaterial();
        
        $this->assertEquals('id', $material->getKeyName());
        $this->assertIsArray($material->getFillable());
        $this->assertIsArray($material->getCasts());
    }

    /**
     * Test learning material relationships.
     */
    public function testLearningMaterialRelationships(): void
    {
        $material = new LearningMaterial();
        
        $creatorRelation = $material->creator();
        $this->assertEquals('created_by', $creatorRelation->getForeignKeyName());
    }

    /**
     * Test video conference model configuration.
     */
    public function testVideoConferenceModelConfiguration(): void
    {
        $conference = new VideoConference();
        
        $this->assertEquals('id', $conference->getKeyName());
        $this->assertIsArray($conference->getFillable());
        $this->assertIsArray($conference->getCasts());
    }

    /**
     * Test video conference relationships.
     */
    public function testVideoConferenceRelationships(): void
    {
        $conference = new VideoConference();
        
        $creatorRelation = $conference->creator();
        $this->assertEquals('created_by', $creatorRelation->getForeignKeyName());
    }

    /**
     * Test virtual class model configuration.
     */
    public function testVirtualClassModelConfiguration(): void
    {
        $virtualClass = new VirtualClass();
        
        $this->assertEquals('id', $virtualClass->getKeyName());
        $this->assertIsArray($virtualClass->getFillable());
    }

    /**
     * Test virtual class relationships.
     */
    public function testVirtualClassRelationships(): void
    {
        $virtualClass = new VirtualClass();
        
        $teacherRelation = $virtualClass->teacher();
        $this->assertEquals('teacher_id', $teacherRelation->getForeignKeyName());
    }
}
