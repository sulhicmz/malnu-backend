# Business Domains Guide

## Overview

The Malnu Backend School Management System is organized into 11 distinct business domains, each representing a core functional area of school operations. This guide provides an overview of each domain, its purpose, key features, models, and services.

## Domain Architecture

The system follows Domain-Driven Design (DDD) principles, with each domain containing:
- **Models**: Data structures and relationships
- **Controllers**: API endpoints for domain operations
- **Services**: Business logic specific to the domain
- **Migrations**: Database schema changes
- **Tests**: Domain-specific test coverage

## Domain List

1. **School Management** - Core school operations and administration
2. **Attendance Management** - Student and staff attendance tracking
3. **Calendar System** - School calendar, events, and scheduling
4. **AI Assistant** - Intelligent tutoring and learning support
5. **E-Learning** - Online classroom and learning materials
6. **Online Exam** - Examination and assessment system
7. **Grading System** - Grade management and academic records
8. **Digital Library** - E-books and digital resources
9. **PPDB (Admissions)** - Student registration and enrollment
10. **Parent Portal** - Parent communication and engagement
11. **Career Development** - Career guidance and counseling

---

## 1. School Management

### Purpose

Manages core school operations including students, teachers, staff, classes, subjects, and scheduling.

### Key Features

- **Student Management**: Student records, profiles, and academic history
- **Teacher Management**: Teacher profiles, qualifications, and assignments
- **Staff Management**: Administrative staff records and roles
- **Class Management**: Class definitions, sections, and enrollment
- **Subject Management**: Subject definitions and assignments
- **Schedule Management**: Class schedules and timetable management

### Core Models

```
app/Models/SchoolManagement/
├── Student.php          # Student records
├── Teacher.php          # Teacher profiles
├── Staff.php            # Staff records
├── ClassModel.php       # Class definitions
└── Subject.php         # Subject definitions
```

### Database Tables

- `students` - Student information
- `teachers` - Teacher information
- `staff` - Staff information
- `class_models` - Class definitions
- `subjects` - Subject definitions
- `class_subjects` - Class-subject relationships
- `schedules` - Class schedules

### API Endpoints

```
/api/school/*
├── GET    /api/school/students          - List students
├── POST   /api/school/students          - Create student
├── GET    /api/school/teachers          - List teachers
├── POST   /api/school/teachers          - Create teacher
├── GET    /api/school/staff             - List staff
├── POST   /api/school/staff             - Create staff
├── GET    /api/school/classes           - List classes
├── POST   /api/school/classes           - Create class
├── GET    /api/school/subjects         - List subjects
└── POST   /api/school/subjects         - Create subject
```

### Services

- `SchoolManagementService` - Core school operations
- `StudentService` - Student CRUD operations
- `TeacherService` - Teacher management
- `ClassService` - Class and enrollment management

### Integration Points

- **Attendance**: Student/Teacher records used for attendance
- **E-Learning**: Class/Subject assignments for virtual classes
- **Grading**: Student records for grade storage
- **PPDB**: Admissions feed into student records

### Access Control

- **Admin**: Full access to all school management operations
- **Kepala Sekolah**: View and manage all operations
- **Staf TU**: Limited access to student/teacher data
- **Guru**: View own class and student data

---

## 2. Attendance Management

### Purpose

Tracks and manages attendance for students and staff with real-time monitoring and reporting.

### Key Features

- **Student Attendance**: Daily attendance tracking and reporting
- **Staff Attendance**: Staff check-in/check-out and work hours
- **Real-time Updates**: Live attendance status monitoring
- **Reports**: Attendance summaries and analytics
- **Notifications**: Absence alerts and notifications

### Core Models

```
app/Models/Attendance/
├── Attendance.php       # Student attendance records
└── StaffAttendance.php # Staff attendance records
```

### Database Tables

- `attendances` - Student attendance records
- `staff_attendances` - Staff attendance records

### API Endpoints

```
/api/attendance/*
├── GET    /api/attendance/students         - List student attendance
├── POST   /api/attendance/students         - Record student attendance
├── GET    /api/attendance/staff            - List staff attendance
├── POST   /api/attendance/staff            - Record staff attendance
└── GET    /api/attendance/reports         - Attendance reports
```

### Services

- `AttendanceService` - Attendance tracking and management
- `StaffAttendanceService` - Staff attendance operations

### Integration Points

- **School Management**: Student/Staff data for attendance records
- **Notifications**: Send absence alerts to parents/students
- **Calendar**: Attendance on scheduled days

### Access Control

- **Admin**: Full access to all attendance data
- **Kepala Sekolah**: View and manage attendance
- **Staf TU**: Manage attendance records
- **Guru**: View student attendance in their classes
- **Siswa**: View own attendance history

---

## 3. Calendar System

### Purpose

Manages school calendar, events, holidays, and scheduling with reminder notifications.

### Key Features

- **Calendar Management**: School calendar creation and management
- **Event Management**: School events and activities
- **Holiday Management**: School holidays and breaks
- **Reminders**: Event and deadline reminders
- **Recurring Events**: Support for recurring schedules

### Core Models

```
app/Models/Calendar/
├── Calendar.php        # School calendars
└── Event.php          # Calendar events
```

### Database Tables

- `calendars` - School calendars
- `events` - Calendar events

### API Endpoints

```
/api/calendar/*
├── GET    /api/calendar/calendars        - List calendars
├── POST   /api/calendar/calendars        - Create calendar
├── GET    /api/calendar/events          - List events
├── POST   /api/calendar/events          - Create event
├── PUT    /api/calendar/events/{id}     - Update event
└── DELETE /api/calendar/events/{id}     - Delete event
```

### Services

- `CalendarService` - Calendar operations
- `EventService` - Event management

### Integration Points

- **Attendance**: Attendance tracking on school days
- **Notifications**: Event reminders and notifications
- **All Domains**: Events can be related to any domain

### Access Control

- **Admin**: Full access to calendar management
- **Kepala Sekolah**: Manage calendar and events
- **Staf TU**: Manage events
- **Guru**: View and create class-specific events
- **Siswa**: View calendar and events
- **Orang Tua**: View school calendar

---

## 4. AI Assistant

### Purpose

Provides intelligent tutoring, personalized learning support, and AI-powered educational assistance.

### Key Features

- **Intelligent Tutoring**: AI-powered learning assistance
- **Personalized Learning**: Customized learning paths
- **Question Answering**: AI-driven Q&A for students
- **Study Recommendations**: Smart study suggestions
- **Progress Tracking**: AI-powered progress analysis

### Core Models

```
app/Models/AIAssistant/
├── AIConversation.php   # AI conversation history
├── AIQuestion.php       # AI questions and answers
└── AIStudyPlan.php     # AI-generated study plans
```

### Database Tables

- `ai_conversations` - Conversation history
- `ai_questions` - Questions and answers
- `ai_study_plans` - Personalized study plans

### API Endpoints

```
/api/ai/*
├── POST   /api/ai/ask                 - Ask AI a question
├── GET    /api/ai/conversations        - View conversations
├── POST   /api/ai/study-plan          - Generate study plan
└── GET    /api/ai/recommendations     - Get study recommendations
```

### Services

- `AIAssistantService` - AI interaction and tutoring

### Integration Points

- **E-Learning**: AI assistance for learning materials
- **Online Exam**: AI-powered exam preparation
- **Grading**: AI-assisted grade analysis

### Access Control

- **Siswa**: Access AI tutoring and assistance
- **Guru**: View student AI interactions for insights

---

## 5. E-Learning

### Purpose

Delivers online classroom functionality with virtual classes, learning materials, assignments, and video conferencing.

### Key Features

- **Virtual Classes**: Online classroom creation and management
- **Learning Materials**: Course content and resources
- **Assignments**: Online assignment creation and submission
- **Discussions**: Forum and discussion boards
- **Video Conferences**: Live online classes

### Core Models

```
app/Models/ELearning/
├── VirtualClass.php    # Online classrooms
├── LearningMaterial.php # Course content
├── Assignment.php      # Assignments
├── Quiz.php           # Online quizzes
├── Discussion.php      # Forum discussions
└── VideoConference.php # Video sessions
```

### Database Tables

- `virtual_classes` - Virtual classroom definitions
- `learning_materials` - Learning materials and resources
- `assignments` - Assignment definitions
- `quizzes` - Quiz definitions
- `discussions` - Forum discussions
- `video_conferences` - Video conference sessions

### API Endpoints

```
/api/elearning/*
├── GET    /api/elearning/virtual-classes         - List virtual classes
├── POST   /api/elearning/virtual-classes         - Create virtual class
├── GET    /api/elearning/materials               - List materials
├── POST   /api/elearning/materials               - Upload material
├── GET    /api/elearning/assignments             - List assignments
├── POST   /api/elearning/assignments             - Create assignment
├── GET    /api/elearning/quizzes                - List quizzes
├── POST   /api/elearning/quizzes                - Create quiz
├── GET    /api/elearning/discussions             - List discussions
├── POST   /api/elearning/discussions             - Create discussion
└── POST   /api/elearning/video-conference        - Start video conference
```

### Services

- `VirtualClassService` - Virtual class management
- `LearningMaterialService` - Material management
- `AssignmentService` - Assignment operations
- `QuizService` - Quiz management
- `DiscussionService` - Forum operations

### Integration Points

- **School Management**: Class/Subject data for virtual classes
- **Attendance**: Virtual class attendance tracking
- **Online Exam**: Quizzes and assessments
- **Grading**: Assignment and quiz grading
- **AI Assistant**: AI-powered learning support

### Access Control

- **Admin**: Full access to e-learning features
- **Guru**: Manage own virtual classes and materials
- **Siswa**: Access enrolled virtual classes and materials

---

## 6. Online Exam

### Purpose

Manages online examinations with secure test delivery, automated grading, and comprehensive result analysis.

### Key Features

- **Exam Management**: Online exam creation and scheduling
- **Question Bank**: Question repository and management
- **Secure Testing**: Proctoring and security features
- **Automated Grading**: Auto-grading for objective questions
- **Results & Analytics**: Exam performance analysis

### Core Models

```
app/Models/OnlineExam/
├── Exam.php            # Exam definitions
├── ExamQuestion.php    # Exam questions
├── ExamResult.php      # Student exam results
└── QuestionBank.php    # Question repository
```

### Database Tables

- `exams` - Exam definitions
- `exam_questions` - Exam question associations
- `exam_results` - Student exam results
- `question_banks` - Question repository

### API Endpoints

```
/api/exam/*
├── GET    /api/exam/exams                - List exams
├── POST   /api/exam/exams                - Create exam
├── GET    /api/exam/exams/{id}           - View exam
├── POST   /api/exam/exams/{id}/submit     - Submit exam
├── GET    /api/exam/results              - View results
├── GET    /api/exam/questions            - Question bank
└── POST   /api/exam/questions            - Add question
```

### Services

- `ExamService` - Exam creation and management
- `ExamResultService` - Result processing and analysis
- `QuestionBankService` - Question bank management

### Integration Points

- **E-Learning**: Quizzes and assessments
- **Grading**: Grade storage and reporting
- **AI Assistant**: AI-powered exam preparation
- **School Management**: Student enrollment in exams

### Access Control

- **Admin**: Full access to exam management
- **Guru**: Create and manage own exams
- **Siswa**: Take assigned exams and view results

---

## 7. Grading System

### Purpose

Manages student grades, academic records, competency tracking, and report card generation.

### Key Features

- **Grade Management**: Student grade entry and management
- **Competency Tracking**: Skill and competency assessment
- **Report Cards**: Comprehensive report card generation
- **Transcripts**: Academic transcript records
- **Analytics**: Grade analysis and performance tracking

### Core Models

```
app/Models/Grading/
├── Grade.php           # Student grades
├── Competency.php     # Competency records
├── Report.php         # Student reports
└── Transcript.php     # Academic transcripts
```

### Database Tables

- `grades` - Student grade records
- `competencies` - Competency assessments
- `reports` - Student reports
- `student_portfolios` - Student portfolio items

### API Endpoints

```
/api/grading/*
├── GET    /api/grading/grades            - List grades
├── POST   /api/grading/grades            - Enter grade
├── GET    /api/grading/competencies      - List competencies
├── POST   /api/grading/competencies      - Record competency
├── GET    /api/grading/reports           - Generate reports
├── GET    /api/grading/transcripts       - View transcripts
└── POST   /api/grading/transcripts       - Create transcript
```

### Services

- `GradeService` - Grade management
- `CompetencyService` - Competency tracking
- `ReportService` - Report generation
- `TranscriptService` - Transcript management

### Integration Points

- **School Management**: Student data for grades
- **Online Exam**: Exam result integration
- **E-Learning**: Assignment grades
- **Attendance**: Attendance impact on grades

### Access Control

- **Admin**: Full access to grading system
- **Guru**: Enter and manage grades for own students
- **Kepala Sekolah**: View all grades and reports
- **Siswa**: View own grades and reports
- **Orang Tua**: View child's grades and reports

---

## 8. Digital Library

### Purpose

Provides comprehensive library management with e-books, digital resources, cataloging, and circulation tracking.

### Key Features

- **Book Catalog**: Comprehensive book catalog with MARC standards
- **E-Books**: Digital library with e-book access
- **Circulation**: Book checkout, check-in, and holds
- **Library Cards**: Patron management and library cards
- **Analytics**: Usage statistics and reading analytics

### Core Models

```
app/Models/DigitalLibrary/
├── Book.php            # Book catalog
├── BookLoan.php        # Loan records
├── BookReview.php      # Book reviews
├── EbookFormat.php     # E-book formats
├── LibraryPatron.php   # Patron management
├── LibraryHold.php     # Book holds
└── LibraryFine.php     # Fine tracking
```

### Database Tables

- `books` - Book catalog
- `book_loans` - Loan records
- `book_reviews` - Book reviews
- `ebook_formats` - E-book formats
- `library_patrons` - Patron records
- `library_holds` - Book holds
- `library_fines` - Fine records

### API Endpoints

```
/api/library/*
├── GET    /api/library/books              - List books
├── POST   /api/library/books              - Add book
├── GET    /api/library/books/{id}         - View book
├── POST   /api/library/books/{id}/checkout - Checkout book
├── POST   /api/library/books/{id}/checkin  - Checkin book
├── GET    /api/library/patrons            - List patrons
├── POST   /api/library/patrons            - Create patron
├── GET    /api/library/fines              - View fines
└── POST   /api/library/fines              - Pay fine
```

### Services

- `LibraryManagementService` - Core library operations
- `CirculationService` - Book circulation
- `CatalogService` - Book cataloging

### Integration Points

- **School Management**: Student data for patron records
- **Notifications**: Due date reminders and hold alerts
- **Fee Management**: Fine payment integration

### Access Control

- **Admin**: Full library management access
- **Kepala Sekolah**: View library operations
- **Guru**: Library access and student borrowing limits
- **Siswa**: Library access and borrowing
- **Orang Tua**: View child's library activity

---

## 9. PPDB (Admissions)

### Purpose

Manages student admissions, new student registration (PPDB), enrollment process, and admission tracking.

### Key Features

- **Student Registration**: Online PPDB registration
- **Admission Tracking**: Application status and tracking
- **Enrollment Management**: Student enrollment process
- **Document Management**: Admission document collection
- **Reporting**: Admission analytics and reports

### Core Models

```
app/Models/PPDB/
├── PPDBRegistration.php # Admission registration
└── Enrollment.php       # Student enrollment
```

### Database Tables

- `ppdb_registrations` - Admission registrations
- `enrollments` - Student enrollments

### API Endpoints

```
/api/ppdb/*
├── GET    /api/ppdb/registrations        - List registrations
├── POST   /api/ppdb/registrations        - Submit registration
├── GET    /api/ppdb/registrations/{id}   - View registration status
├── POST   /api/ppdb/registrations/{id}/approve - Approve registration
├── GET    /api/ppdb/enrollments         - List enrollments
└── POST   /api/ppdb/enrollments         - Create enrollment
```

### Services

- `PPDBService` - Admission management
- `EnrollmentService` - Enrollment processing

### Integration Points

- **School Management**: Approved registrations become students
- **Payment**: Admission fee processing
- **Notifications**: Admission status notifications

### Access Control

- **Admin**: Full PPDB management
- **Staf TU**: Manage registrations and enrollments
- **Public**: Submit PPDB registration

---

## 10. Parent Portal

### Purpose

Enables parent engagement with child's academic progress, attendance, communication, and school updates.

### Key Features

- **Academic Progress**: View grades, reports, and transcripts
- **Attendance Monitoring**: Track child's attendance
- **Communication**: Parent-teacher messaging and announcements
- **School Updates**: Receive school news and events
- **Fee Management**: View and pay school fees

### Core Models

```
app/Models/ParentPortal/
├── Parent.php          # Parent profiles
├── ParentStudent.php   # Parent-student relationships
├── Message.php        # Parent-teacher messages
└── Announcement.php   # School announcements
```

### Database Tables

- `parents` - Parent information
- `parent_student` - Parent-student relationships
- `messages` - Communication messages
- `announcements` - School announcements

### API Endpoints

```
/api/parent/*
├── GET    /api/parent/children            - View children
├── GET    /api/parent/children/{id}/grades - View child's grades
├── GET    /api/parent/children/{id}/attendance - View child's attendance
├── GET    /api/parent/messages            - View messages
├── POST   /api/parent/messages            - Send message
├── GET    /api/parent/announcements      - View announcements
└── GET    /api/parent/fees               - View fees
```

### Services

- `ParentPortalService` - Parent portal operations
- `CommunicationService` - Parent-teacher communication

### Integration Points

- **School Management**: Student data for parent access
- **Grading**: Student grade access
- **Attendance**: Student attendance access
- **Notifications**: School announcements and alerts
- **Payment**: Fee management and payment

### Access Control

- **Orang Tua**: Access to own children's data
- **Guru**: Send messages to parents
- **Admin**: Manage parent accounts and announcements

---

## 11. Career Development

### Purpose

Provides career guidance, counseling services, scholarship information, and professional development support.

### Key Features

- **Career Guidance**: Career path exploration and guidance
- **Counseling Services**: Student counseling and support
- **Scholarships**: Scholarship information and applications
- **Professional Development**: Skills development programs
- **Job Placement**: Career placement assistance

### Core Models

```
app/Models/CareerDevelopment/
├── CareerPath.php      # Career path definitions
├── Counseling.php      # Counseling sessions
├── Scholarship.php     # Scholarship opportunities
└── JobPosting.php     # Job postings
```

### Database Tables

- `career_paths` - Career path definitions
- `counseling_sessions` - Counseling records
- `scholarships` - Scholarship opportunities
- `job_postings` - Job postings

### API Endpoints

```
/api/career/*
├── GET    /api/career/paths              - List career paths
├── POST   /api/career/counseling         - Request counseling
├── GET    /api/career/counseling         - View counseling sessions
├── GET    /api/career/scholarships       - View scholarships
├── POST   /api/career/scholarships       - Apply for scholarship
├── GET    /api/career/jobs               - View job postings
└── POST   /api/career/jobs               - Apply for job
```

### Services

- `CareerDevelopmentService` - Career guidance operations
- `CounselingService` - Counseling management
- `ScholarshipService` - Scholarship management

### Integration Points

- **School Management**: Student data for career planning
- **E-Learning**: Skill development courses
- **Grading**: Academic performance for career guidance
- **AI Assistant**: AI-powered career recommendations

### Access Control

- **Admin**: Full career management access
- **Konselor**: Manage counseling and career guidance
- **Guru**: View student career plans
- **Siswa**: Access career resources and guidance

---

## Cross-Domain Relationships

### Student Data Flow

```
PPDB (Registration)
    ↓
School Management (Student Record)
    ↓
Attendance (Tracking)
    ↓
E-Learning (Enrollment)
    ↓
Online Exam (Assessment)
    ↓
Grading (Grade Records)
    ↓
Parent Portal (Parent Access)
```

### Teacher Data Flow

```
School Management (Teacher Profile)
    ↓
Attendance (Staff Tracking)
    ↓
E-Learning (Class Management)
    ↓
Grading (Grade Entry)
    ↓
Parent Portal (Communication)
```

### Communication Flow

```
Calendar (Events)
    ↓
Notifications (Alerts)
    ↓
All Domains (Notifications)
    ↓
Parent Portal (Updates)
```

## Domain Access Control Matrix

| Domain | Admin | Kepala Sekolah | Staf TU | Guru | Siswa | Orang Tua |
|--------|-------|-----------------|---------|------|------|------------|
| School Management | Full | Full | Limited | View | None | None |
| Attendance | Full | Full | Full | View Class | Own | Child's |
| Calendar | Full | Full | Manage | View | View | View |
| AI Assistant | Full | View | None | View Students | Access | None |
| E-Learning | Full | View | None | Manage Own | Access | None |
| Online Exam | Full | View | None | Manage Own | Take | Child's |
| Grading | Full | Full | None | Enter Grades | Own | Child's |
| Digital Library | Full | View | Manage | Access | Access | Child's |
| PPDB | Full | Full | Full | None | Register | Register |
| Parent Portal | Full | Full | None | Communicate | None | Access |
| Career Development | Full | View | None | View | Access | Child's |

## Development Guidelines

### When Working on a Domain

1. **Understand the Domain Purpose**: Review the domain's purpose and features
2. **Check Existing Models**: Review existing models and relationships
3. **Follow Domain Structure**: Keep domain-specific code within domain folders
4. **Use Domain Services**: Use domain-specific services for business logic
5. **Test in Context**: Test domain features with integration points

### Creating New Domain Features

1. **Identify Affected Domains**: Determine which domains are impacted
2. **Create Domain-Specific Models**: Add models to appropriate domain folder
3. **Implement Services**: Create business logic in domain services
4. **Update Controllers**: Add API endpoints to domain controllers
5. **Create Migrations**: Add database schema changes
6. **Write Tests**: Test domain functionality and integration points
7. **Update Documentation**: Document new features in domain guide

### Cross-Domain Development

When features span multiple domains:

1. **Identify Primary Domain**: Choose the domain that "owns" the feature
2. **Define Integration Points**: Specify how domains interact
3. **Use Event System**: Dispatch events for cross-domain communication
4. **Document Relationships**: Update this guide with new relationships

## Additional Resources

- [Architecture Documentation](ARCHITECTURE.md)
- [Database Schema](DATABASE_SCHEMA.md)
- [API Documentation](API.md)
- [Developer Guide](DEVELOPER_GUIDE.md)
- [HyperVel Framework Guide](HYPERVEL_FRAMEWORK_GUIDE.md)

---

**Last Updated:** January 9, 2026
**Total Domains:** 11
