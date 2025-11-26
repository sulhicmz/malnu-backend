<?php
// Simple test to verify that all the models can be loaded without errors

echo "Testing model imports...\n";

// Test basic model loading
require_once 'vendor/autoload.php';

// Test that models can be instantiated (this will fail if imports are missing)
try {
    // Test models that we fixed
    $role = new \App\Models\Role();
    echo "✓ Role model loaded\n";
    
    $permission = new \App\Models\Permission();
    echo "✓ Permission model loaded\n";
    
    $leaveRequest = new \App\Models\Attendance\LeaveRequest();
    echo "✓ LeaveRequest model loaded\n";
    
    $leaveType = new \App\Models\Attendance\LeaveType();
    echo "✓ LeaveType model loaded\n";
    
    $substituteAssignment = new \App\Models\Attendance\SubstituteAssignment();
    echo "✓ SubstituteAssignment model loaded\n";
    
    $substituteTeacher = new \App\Models\Attendance\SubstituteTeacher();
    echo "✓ SubstituteTeacher model loaded\n";
    
    $leaveBalance = new \App\Models\Attendance\LeaveBalance();
    echo "✓ LeaveBalance model loaded\n";
    
    $student = new \App\Models\SchoolManagement\Student();
    echo "✓ Student model loaded\n";
    
    $teacher = new \App\Models\SchoolManagement\Teacher();
    echo "✓ Teacher model loaded\n";
    
    $classModel = new \App\Models\SchoolManagement\ClassModel();
    echo "✓ ClassModel model loaded\n";
    
    $classSubject = new \App\Models\SchoolManagement\ClassSubject();
    echo "✓ ClassSubject model loaded\n";
    
    $schedule = new \App\Models\SchoolManagement\Schedule();
    echo "✓ Schedule model loaded\n";
    
    $subject = new \App\Models\SchoolManagement\Subject();
    echo "✓ Subject model loaded\n";
    
    $discussionReply = new \App\Models\ELearning\DiscussionReply();
    echo "✓ DiscussionReply model loaded\n";
    
    $quiz = new \App\Models\ELearning\Quiz();
    echo "✓ Quiz model loaded\n";
    
    $virtualClass = new \App\Models\ELearning\VirtualClass();
    echo "✓ VirtualClass model loaded\n";
    
    $discussion = new \App\Models\ELearning\Discussion();
    echo "✓ Discussion model loaded\n";
    
    $learningMaterial = new \App\Models\ELearning\LearningMaterial();
    echo "✓ LearningMaterial model loaded\n";
    
    $assignment = new \App\Models\ELearning\Assignment();
    echo "✓ Assignment model loaded\n";
    
    $videoConference = new \App\Models\ELearning\VideoConference();
    echo "✓ VideoConference model loaded\n";
    
    echo "\nAll models loaded successfully! Import fixes are working correctly.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}