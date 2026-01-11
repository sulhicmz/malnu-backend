# Technical Architecture Documentation

*Last Updated: January 9, 2026*

## 🏗️ System Architecture Overview

### Framework Stack
- **Backend Framework**: HyperVel (Laravel-style with Swoole support)
- **PHP Version**: 8.2+
- **Database**: SQLite (development), MySQL (production)
- **Cache**: Redis
- **Queue**: Database
- **Frontend**: React with Vite

### Architecture Pattern
- **Domain-Driven Design**: Models organized by business domains
- **MVC Pattern**: Model-View-Controller architecture
- **Repository Pattern**: Data access abstraction (planned)
- **Service Layer**: Business logic separation (planned)

## 📁 Directory Structure

```
malnu-backend/
├── app/                          # Application code
│   ├── Console/                  # Artisan commands
│   ├── Contracts/                 # Service interfaces
│   ├── Events/                   # Event classes
│   ├── Exceptions/               # Exception handlers
│   ├── Http/                     # HTTP layer
│   │   ├── Controllers/          # API controllers
│   │   │   ├── admin/         # Admin controllers
│   │   │   ├── Api/           # API controllers
│   │   │   ├── Attendance/    # Attendance controllers
│   │   │   └── Calendar/      # Calendar controllers
│   │   ├── Middleware/        # HTTP middleware
│   │   ├── Requests/           # Form request validation
│   │   ├── AbstractController.php
│   │   ├── BaseController.php
│   │   └── Controller.php
│   ├── Listeners/               # Event listeners
│   ├── Models/                   # Eloquent models
│   │   ├── AIAssistant/         # AI tutoring models
│   │   ├── Attendance/          # Attendance models
│   │   ├── CareerDevelopment/   # Career development models
│   │   ├── DigitalLibrary/      # Digital library models
│   │   ├── ELearning/           # E-Learning models
│   │   ├── Grading/            # Grading models
│   │   ├── Logs/               # Logging models
│   │   ├── Monetization/        # Monetization models
│   │   ├── OnlineExam/          # Online exam models
│   │   ├── PPDB/               # PPDB models
│   │   ├── ParentPortal/        # Parent portal models
│   │   ├── SchoolManagement/    # School management models
│   │   ├── System/              # System models
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Permission.php
│   │   └── Model.php
│   ├── Providers/                # Service providers
│   ├── Services/                 # Business logic services
│   └── Traits/                   # Reusable traits
├── config/                       # Configuration files
├── database/                     # Database layer
│   ├── factories/                # Model factories
│   ├── migrations/               # Database migrations
│   └── seeders/                  # Database seeders
├── docs/                         # Documentation
├── frontend/                     # React frontend
├── public/                       # Public assets
├── routes/                       # Route definitions
├── storage/                      # File storage
└── tests/                        # Test suite
```

## 🗄️ Database Architecture

### Primary Key Strategy
- **UUID Implementation**: All tables use UUID primary keys
- **Standard Format**: `CHAR(36)` with default `UUID()` values
- **Consistent Naming**: All primary keys named `id`

### Core Tables

#### Authentication & Authorization
- `users` - User accounts and authentication
- `roles` - Role definitions
- `permissions` - Permission definitions
- `model_has_roles` - User-role assignments
- `role_has_permissions` - Role-permission assignments
- `model_has_permissions` - Direct user permissions

#### School Management
- `students` - Student records
- `teachers` - Teacher records
- `staff` - Staff records
- `class_models` - Class definitions
- `subjects` - Subject definitions
- `class_subjects` - Class-subject relationships
- `schedules` - Class schedules

#### Academic Management
- `grades` - Student grades
- `competencies` - Competency tracking
- `reports` - Student reports
- `student_portfolios` - Portfolio items

#### E-Learning
- `virtual_classes` - Online classrooms
- `learning_materials` - Course content
- `assignments` - Student assignments
- `quizzes` - Online quizzes
- `discussions` - Forum discussions
- `video_conferences` - Video sessions

#### Digital Library
- `books` - E-book catalog
- `book_loans` - Lending records
- `book_reviews` - User reviews
- `ebook_formats` - Format definitions

#### Online Exams
- `exams` - Exam definitions
- `question_banks` - Question pools
- `exam_questions` - Exam questions
- `exam_answers` - Student answers
- `exam_results` - Exam results

#### PPDB (Registration)
- `ppdb_registrations` - Student applications
- `ppdb_documents` - Required documents
- `ppdb_announcements` - Registration info
- `ppdb_tests` - Entrance tests

#### Career Development
- `career_assessments` - Career tests
- `counseling_sessions` - Guidance sessions
- `industry_partners` - Partner companies

#### Monetization
- `transactions` - Financial transactions
- `transaction_items` - Transaction details
- `marketplace_products` - Product catalog

#### System Management
- `system_settings` - Configuration
- `audit_logs` - Activity tracking

## 🔐 Security Architecture

### Authentication
- **JWT Tokens**: Stateless authentication
- **Role-Based Access**: Permission-based authorization
- **Session Management**: Secure session handling
- **Password Security**: Hashed passwords with bcrypt

### Security Headers
- **Content Security Policy**: XSS prevention
- **X-Frame-Options**: Clickjacking protection
- **X-Content-Type-Options**: MIME type sniffing prevention
- **Strict-Transport-Security**: HTTPS enforcement
- **Referrer Policy**: Referrer information control
- **Permissions Policy**: Feature access control

### Input Validation
- **Request Validation**: Form request validation classes
- **Input Sanitization**: XSS and SQL injection prevention
- **Rate Limiting**: API abuse prevention
- **CSRF Protection**: Cross-site request forgery prevention

## 🚀 Performance Architecture

### Caching Strategy
- **Redis Caching**: Query result caching
- **Application Cache**: Configuration and route caching
- **Session Storage**: Redis-based sessions
- **API Response Caching**: Frequent query optimization

### Database Optimization
- **Query Optimization**: Efficient query design
- **Index Strategy**: Composite indexes for common queries
- **Connection Pooling**: Swoole connection management
- **Eager Loading**: N+1 query prevention

### Application Performance
- **Swoole Coroutines**: Non-blocking I/O operations
- **Memory Management**: Efficient memory usage
- **Response Compression**: Gzip compression
- **Static Asset Optimization**: CDN-ready assets

## 📡 API Architecture

### RESTful Design
- **Resource-Based Routes**: RESTful endpoint design
- **HTTP Methods**: Proper method usage (GET, POST, PUT, DELETE)
- **Status Codes**: Consistent HTTP status code usage
- **Response Format**: Standardized JSON responses

### API Versioning
- **URL Versioning**: `/api/v1/` prefix strategy
- **Backward Compatibility**: Maintaining older versions
- **Deprecation Policy**: Clear version lifecycle management

### Error Handling
- **Standardized Errors**: Consistent error response format
- **Exception Handling**: Centralized exception management
- **Logging**: Comprehensive error logging
- **Monitoring**: Error rate tracking

## 🔧 Development Architecture

### Code Quality
- **PHPStan**: Static analysis level 5
- **PHP CS Fixer**: Code formatting standards
- **PHPUnit**: Unit and feature testing
- **Type Safety**: Strict types declaration

### Testing Strategy
- **Unit Tests**: Model and business logic testing
- **Feature Tests**: API endpoint testing
- **Integration Tests**: Component interaction testing
- **Database Tests**: Migration and schema testing

### Development Workflow
- **Git Flow**: Feature branch workflow
- **Code Review**: Pull request process
- **CI/CD**: Automated testing and deployment
- **Quality Gates**: Automated quality checks

## 📊 Monitoring & Logging

### Application Monitoring
- **Performance Metrics**: Response times and throughput
- **Error Tracking**: Exception monitoring
- **User Analytics**: Usage pattern analysis
- **System Health**: Resource utilization monitoring

### Logging Strategy
- **Structured Logging**: JSON format logs
- **Log Levels**: Appropriate level usage
- **Log Rotation**: Automatic log management
- **Security Logging**: Authentication and authorization events

## 🔄 Integration Architecture

### External Services
- **Email Service**: Notification delivery
- **Payment Gateway**: Financial transactions
- **File Storage**: Document and media storage
- **Video Conferencing**: Online classroom integration

### Third-Party APIs
- **Government Services**: Student data validation
- **Educational Platforms**: Content integration
- **Analytics Services**: Learning analytics
- **Communication Platforms**: Parent-teacher communication

## 🚀 Deployment Architecture

### Environment Strategy
- **Development**: Local development environment
- **Staging**: Pre-production testing
- **Production**: Live application environment
- **Disaster Recovery**: Backup and recovery procedures

### Infrastructure
- **Load Balancing**: Traffic distribution
- **Auto Scaling**: Dynamic resource allocation
- **Database Replication**: High availability
- **CDN Integration**: Content delivery optimization

---

*This documentation is continuously updated as the architecture evolves.*