# Library Management System

## Overview

The Library Management System is a comprehensive library and digital resource management platform that enhances the existing Digital Library module with advanced library operations including circulation management, patron services, inventory control, acquisition workflows, MARC cataloging, reading programs, and space management.

## Architecture

### Database Schema

The system consists of the following database tables:

#### Core Library Tables
- **library_patrons** - Patron profiles with library cards and membership management
- **library_fines** - Fine tracking, calculation, and payment management
- **library_holds** - Book holds and recalls with priority queuing
- **library_inventory** - Stock taking, weeding, and collection management

#### Acquisition Tables
- **library_acquisitions** - Book purchasing, vendor management, and budget tracking

#### Reading Program Tables
- **library_reading_programs** - Reading challenges, book clubs, and literacy initiatives
- **library_reading_program_participants** - Enrollment tracking and progress monitoring

#### Analytics Tables
- **library_analytics** - Usage statistics, circulation data, and collection analysis

#### Space Management Tables
- **library_spaces** - Study rooms, computer labs, and collaborative spaces
- **library_space_bookings** - Space reservation and scheduling

#### MARC Cataloging Tables
- **marc_records** - MARC 21 cataloging standards support
- **marc_fields** - MARC field structure with tags, indicators, and subfields

## API Endpoints

### Patron Management

#### Create Patron
```
POST /api/library/patrons
Content-Type: application/json
Authorization: Bearer {token}

{
  "user_id": "uuid",
  "library_card_number": "LC12345",
  "status": "active",
  "membership_start_date": "2026-01-08",
  "max_loan_limit": 5,
  "notes": "Optional notes"
}
```

#### Get All Patrons
```
GET /api/library/patrons?status=active&search=john&per_page=20
Authorization: Bearer {token}
```

#### Get Patron
```
GET /api/library/patrons/{id}
Authorization: Bearer {token}
```

#### Update Patron
```
PUT /api/library/patrons/{id}
Content-Type: application/json
Authorization: Bearer {token}

{
  "status": "suspended",
  "max_loan_limit": 10
}
```

#### Delete Patron
```
DELETE /api/library/patrons/{id}
Authorization: Bearer {token}
```

### Circulation Management

#### Checkout Book
```
POST /api/library/circulation/checkout
Content-Type: application/json
Authorization: Bearer {token}

{
  "patron_id": "uuid",
  "book_id": "uuid",
  "loan_days": 14
}
```

**Validation Rules:**
- Patron must be active
- Patron must be under loan limit
- Book must have available copies

#### Return Book
```
POST /api/library/circulation/return/{loanId}
Authorization: Bearer {token}
```

**Automatic Actions:**
- Calculates overdue fines if applicable
- Updates book availability
- Updates patron loan count

#### Renew Book
```
POST /api/library/circulation/renew/{loanId}
Content-Type: application/json
Authorization: Bearer {token}

{
  "additional_days": 14
}
```

**Validation Rules:**
- Book must not be returned
- Patron must have no outstanding fines

### Hold Management

#### Place Hold
```
POST /api/library/holds
Content-Type: application/json
Authorization: Bearer {token}

{
  "patron_id": "uuid",
  "book_id": "uuid",
  "hold_type": "hold|recall"
}
```

**Hold Types:**
- `hold` - Standard hold for unavailable books
- `recall` - Recall a book that is currently checked out

#### Cancel Hold
```
POST /api/library/holds/{holdId}/cancel
Authorization: Bearer {token}
```

#### Fulfill Hold
```
POST /api/library/holds/{holdId}/fulfill
Authorization: Bearer {token}
```

### Fine Management

#### Create Fine
```
POST /api/library/fines
Content-Type: application/json
Authorization: Bearer {token}

{
  "patron_id": "uuid",
  "loan_id": "uuid",
  "fine_type": "overdue|lost|damaged|other",
  "amount": 5.00,
  "description": "Optional description",
  "due_date": "2026-01-22"
}
```

**Fine Types:**
- `overdue` - Automatic fine for late returns
- `lost` - Fine for lost books
- `damaged` - Fine for damaged materials
- `other` - Custom fine type

#### Pay Fine
```
POST /api/library/fines/{fineId}/pay
Content-Type: application/json
Authorization: Bearer {token}

{
  "amount": 5.00
}
```

#### Waive Fine
```
POST /api/library/fines/{fineId}/waive
Authorization: Bearer {token}
```

### Inventory Management

#### Create Inventory Record
```
POST /api/library/inventory
Content-Type: application/json
Authorization: Bearer {token}

{
  "book_id": "uuid",
  "action_type": "stock_take|weeding|addition|correction",
  "expected_quantity": 10,
  "actual_quantity": 9,
  "notes": "Optional notes",
  "performed_by": "Librarian name"
}
```

**Action Types:**
- `stock_take` - Regular inventory count
- `weeding` - Removing outdated materials
- `addition` - Adding new acquisitions
- `correction` - Fixing inventory discrepancies

### Acquisition Management

#### Create Acquisition
```
POST /api/library/acquisitions
Content-Type: application/json
Authorization: Bearer {token}

{
  "acquisition_number": "ACQ001",
  "title": "Book Title",
  "author": "Author Name",
  "isbn": "978-1234567890",
  "publisher": "Publisher Name",
  "quantity": 5,
  "unit_cost": 25.00,
  "vendor": "Vendor Name",
  "status": "ordered",
  "notes": "Optional notes"
}
```

**Auto-calculated:**
- `total_cost` = `unit_cost` × `quantity`

#### Mark Acquisition as Received
```
POST /api/library/acquisitions/{id}/receive
Content-Type: application/json
Authorization: Bearer {token}

{
  "received_date": "2026-01-08",
  "notes": "Optional notes"
}
```

**Automatic Actions:**
- Creates book records in the catalog
- Updates book availability

### Reading Program Management

#### Create Reading Program
```
POST /api/library/reading-programs
Content-Type: application/json
Authorization: Bearer {token}

{
  "program_name": "Summer Reading Challenge",
  "program_type": "reading_challenge|book_club|literacy_initiative",
  "description": "Program description",
  "start_date": "2026-06-01",
  "end_date": "2026-08-31",
  "target_books": 10,
  "prizes": "Certificate, Bookmark",
  "status": "active"
}
```

**Program Types:**
- `reading_challenge` - Set reading goals and track progress
- `book_club` - Organize discussion groups
- `literacy_initiative` - Educational programs

#### Enroll in Program
```
POST /api/library/reading-programs/enroll
Content-Type: application/json
Authorization: Bearer {token}

{
  "program_id": "uuid",
  "patron_id": "uuid"
}
```

#### Record Books Read
```
POST /api/library/reading-programs/participants/{participantId}/books-read
Content-Type: application/json
Authorization: Bearer {token}

{
  "count": 1
}
```

**Automatic Actions:**
- Updates participant's book count
- Auto-completes program if target reached

### Space Management

#### Create Space
```
POST /api/library/spaces
Content-Type: application/json
Authorization: Bearer {token}

{
  "space_name": "Study Room A",
  "space_type": "study_room|computer_lab|meeting_room|collaborative_area",
  "capacity": 4,
  "availability": "available",
  "equipment": "Whiteboard, Projector",
  "amenities": "WiFi, Power outlets",
  "rules": "No food allowed"
}
```

**Space Types:**
- `study_room` - Individual/small group study
- `computer_lab` - Computer facilities
- `meeting_room` - Meeting and conference rooms
- `collaborative_area` - Open collaborative spaces

#### Book Space
```
POST /api/library/spaces/book
Content-Type: application/json
Authorization: Bearer {token}

{
  "space_id": "uuid",
  "user_id": "uuid",
  "start_time": "2026-01-08T09:00:00",
  "end_time": "2026-01-08T11:00:00",
  "attendees": 4,
  "purpose": "Group study",
  "notes": "Optional notes"
}
```

**Validation Rules:**
- No overlapping bookings for the same space
- Time slots must be within operating hours

#### Cancel Space Booking
```
POST /api/library/spaces/bookings/{bookingId}/cancel
Authorization: Bearer {token}
```

### MARC Cataloging

#### Create MARC Record
```
POST /api/library/marc-records
Content-Type: application/json
Authorization: Bearer {token}

{
  "book_id": "uuid",
  "leader": "00000nam  2200000i 4500",
  "control_number": "12345678",
  "record_type": "language_material|manuscript|cartographic|projected|sound_recording|music|visual",
  "bibliographic_level": "monograph",
  "cataloging_notes": "Optional notes"
}
```

#### Add MARC Field
```
POST /api/library/marc-records/{recordId}/fields
Content-Type: application/json
Authorization: Bearer {token}

{
  "tag": "245",
  "indicator1": "1",
  "indicator2": "0",
  "data": "Title of the book"
}
```

**Common Tags:**
- `020` - ISBN
- `100` - Main Author
- `245` - Title Statement
- `260` - Publication Information
- `300` - Physical Description
- `650` - Subject Headings

### Analytics

#### Record Analytics
```
POST /api/library/analytics
Content-Type: application/json
Authorization: Bearer {token}

{
  "date": "2026-01-08"
}
```

**Automatically Collected:**
- Daily checkouts
- Daily returns
- Holds placed
- Renewals
- Unique patrons

#### Get Popular Books
```
GET /api/library/analytics/popular-books?limit=10&days=30
Authorization: Bearer {token}
```

#### Get Patron Reading History
```
GET /api/library/patrons/{patronId}/reading-history?status=returned&from_date=2026-01-01&to_date=2026-01-31&per_page=20
Authorization: Bearer {token}
```

#### Generate Overdue Fines
```
POST /api/library/fines/generate-overdue
Authorization: Bearer {token}
```

**Automatically:**
- Finds all overdue loans
- Calculates fine amount ($0.50 per day)
- Creates fine records
- Updates patron totals

## Model Scopes

### LibraryPatron Scopes
- `active()` - Filter active patrons
- `suspended()` - Filter suspended patrons
- `expired()` - Filter expired memberships
- `canBorrow()` - Filter patrons who can borrow

### LibraryFine Scopes
- `pending()` - Filter unpaid fines
- `paid()` - Filter paid fines
- `waived()` - Filter waived fines

### LibraryHold Scopes
- `pending()` - Filter pending holds
- `ready()` - Filter ready holds
- `byPriority()` - Sort by priority queue

### LibraryAnalytics Scopes
- `popularBooks()` - Get most popular books
- `recent()` - Filter by recent time period
- `overall()` - Get overall statistics

## Model Methods

### LibraryPatron
```php
$patron->canBorrowMore(); // Check if patron can borrow
$patron->hasOutstandingFines(); // Check for unpaid fines
```

### LibraryFine
```php
$fine->getRemainingBalanceAttribute(); // Calculate remaining balance
$fine->isPaid(); // Check if fully paid
$fine->isOverdue(); // Check if payment is overdue
```

### LibraryHold
```php
$hold->isReady(); // Check if hold is ready for pickup
$hold->isPending(); // Check if hold is pending
$hold->isExpired(); // Check if hold has expired
```

### LibraryReadingProgram
```php
$program->isActive(); // Check if program is currently active
$program->hasEnded(); // Check if program end date passed
$program->getParticipantCountAttribute(); // Get participant count
```

### LibraryReadingProgramParticipant
```php
$participant->incrementBooksRead(); // Increment books read
$participant->complete(); // Mark program complete
$participant->withdraw(); // Withdraw from program
```

### LibrarySpace
```php
$space->isAvailable(); // Check if space is available
$space->isUnderMaintenance(); // Check maintenance status
```

### LibrarySpaceBooking
```php
$booking->isActive(); // Check if booking is currently active
$booking->isUpcoming(); // Check if booking is in the future
$booking->isPast(); // Check if booking has passed
```

## Security and Privacy

### Access Control
- All library endpoints require JWT authentication
- Role-based access control for librarians, patrons, and administrators
- Audit logging for all circulation and patron data operations

### Privacy Controls
- Patron reading history is protected
- Fine information is sensitive
- Personal contact information is secured
- GDPR compliance with data retention policies

### Audit Logging
- All checkout/return operations logged
- Fine modifications tracked
- Hold management actions recorded
- Inventory changes documented

## Integration Points

### Student Information System (Issue #229)
- Patron profiles linked to User records
- Student ID integration
- Account status synchronization

### Notification System (Issue #257)
- Due date reminders
- Hold ready notifications
- Fine payment reminders
- Program announcements

### Fee Management System (Issue #200)
- Fine payment processing
- Library fee tracking
- Budget integration for acquisitions

## Best Practices

### Circulation Workflow
1. Verify patron is active and under loan limit
2. Check book availability
3. Process checkout and update counts
4. Set due date and issue receipt
5. Send notification to patron

### Fine Management
1. Automatically calculate overdue fines
2. Send fine payment reminders
3. Track partial payments
4. Allow waiver with approval
5. Update patron account status

### Inventory Control
1. Schedule regular stock takes
2. Investigate discrepancies
3. Weed outdated materials
4. Update catalog records
5. Generate collection reports

### Space Management
1. Set clear booking policies
2. Monitor space usage
3. Prevent overbooking
4. Track equipment usage
5. Gather usage analytics

## Testing

Run library management tests:
```bash
vendor/bin/phpunit tests/Feature/LibraryManagementTest.php
```

## Troubleshooting

### Common Issues

**Patron cannot borrow books:**
- Check patron status is `active`
- Verify loan limit not reached
- Check for outstanding fines

**Book not available:**
- Verify `available_quantity > 0`
- Check for active holds
- Review inventory records

**Hold queue not processing:**
- Check hold priority ordering
- Verify book availability
- Review hold expiry dates

**Space booking conflicts:**
- Check for overlapping bookings
- Verify space availability
- Review booking time rules

**MARC record errors:**
- Validate leader string format
- Check field tag validity
- Verify indicator values
- Test field subfield structure

## Performance Optimization

### Database Indexing
- Indexes on patron_id, book_id for foreign keys
- Composite indexes for status/date fields
- Full-text search on book titles and authors

### Caching Strategy
- Cache popular books list (5 min)
- Cache patron information (10 min)
- Cache space availability (1 min)

### Query Optimization
- Use eager loading for relationships
- Implement pagination for large result sets
- Use database-specific query optimizations

## Future Enhancements

- Advanced RFID integration
- Inter-library loan system
- Digital content provider integration
- Citation generator tools
- Research guide management
- Mobile library access
- Barcode/QR code generation
- Advanced analytics dashboards
- Collection development tools
- Budget management system
