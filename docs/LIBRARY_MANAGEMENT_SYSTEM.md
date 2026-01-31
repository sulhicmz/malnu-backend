# Library Management System Documentation

## Overview

The Library Management System provides comprehensive library operations for the school, including:
- Book catalog with advanced search and categorization
- Circulation management (checkout, check-in, renewal, holds)
- Patron management with library cards and reading history
- Fine tracking and loan policies
- Library statistics and analytics

## Architecture

### Database Schema

#### Core Tables

**books**
- UUID primary key
- Enhanced catalog fields (subtitle, language, pages, edition, genre, price, location, call_number)
- Quantity and availability tracking
- Reference-only book flag

**book_authors**
- Multiple authors per book support
- Author ordering for display

**book_categories**
- Hierarchical category structure
- Category codes and descriptions

**book_subjects**
- Subject classification
- Code-based lookups

**book_category_mappings** & **book_subject_mappings**
- Many-to-many pivot tables
- Unique constraints

**book_loans**
- Loan tracking with fine management
- Renewal count tracking
- Original due date for fine calculation
- Fine payment tracking

**book_holds**
- Reservation system
- Status tracking (pending, ready, cancelled)
- Expiry date management

**library_cards**
- Patron library card management
- Card number tracking
- Issue and expiry dates

**reading_history**
- Complete patron reading history
- Loan and return date tracking

**loan_policies**
- Borrowing rules by user type
- Fine calculation parameters
- Renewal and grace period limits

### Models

#### Book Model
```php
// Relationships
- bookAuthors(): Has many authors
- categories(): Belongs to many categories
- subjects(): Belongs to many subjects
- bookLoans(): Has many loans
- bookHolds(): Has many holds
- bookReviews(): Has many reviews
- ebookFormats(): Has many e-book formats

// Scopes
- scopeAvailable($query): Filter available books
- scopeByCategory($query, $categoryCode): Filter by category
- scopeBySubject($query, $subjectCode): Filter by subject
- scopeSearch($query, $searchTerm): Search by title, author, ISBN, publisher
```

#### BookLoan Model
```php
// Relationships
- book(): Belongs to book
- borrower(): Belongs to user
- libraryCard(): Belongs to library card

// Scopes
- scopeActive($query): Active loans (not returned)
- scopeOverdue($query): Overdue loans
- scopeReturned($query): Returned loans

// Methods
- isOverdue(): bool - Check if loan is overdue
- canBeRenewed(int $renewalLimit): bool - Check if renewal allowed
```

#### BookHold Model
```php
// Relationships
- book(): Belongs to book
- patron(): Belongs to user

// Scopes
- scopeActive($query): Active holds (not cancelled)
- scopeReady($query): Ready for pickup holds
- scopePending($query): Pending holds (waiting for availability)
```

#### LibraryCard Model
```php
// Relationships
- user(): Belongs to user
- bookLoans(): Has many loans

// Scopes
- scopeActive($query): Active library cards
- scopeExpired($query): Expired library cards

// Methods
- isExpired(): bool - Check if card is expired
- isActive(): bool - Check if card is active
```

#### LoanPolicy Model
```php
// Scopes
- scopeActive($query): Active policies
- scopeByUserType($query, $userType): Filter by user type

// Methods
- isRenewalAllowed(int $renewalCount): bool - Check if renewal allowed
- calculateFine(int $overdueDays): float - Calculate fine for overdue books
```

### Service Layer

#### LibraryService

**searchBooks(array $filters, int $page, int $perPage)**: array
- Search books with multiple filters
- Supports: search term, category, subject, availability
- Paginated results

**getBookDetails(string $bookId)**: ?array
- Get detailed book information
- Includes authors, categories, subjects, reviews

**checkoutBook(string $bookId, string $userId, ?string $libraryCardId)**: array
- Checkout book to patron
- Validates availability and loan limits
- Applies loan policy rules
- Decrements available quantity

**returnBook(string $loanId)**: array
- Return borrowed book
- Calculates fines for overdue books
- Creates reading history entry
- Increments available quantity

**renewBook(string $loanId)**: array
- Extend loan due date
- Validates renewal limits and due date
- Updates renewal count

**placeBookHold(string $bookId, string $userId)**: array
- Place book on hold
- Validates existing holds
- Creates pending hold request

**cancelBookHold(string $holdId, string $userId)**: array
- Cancel book hold
- Validates ownership
- Marks hold as cancelled

**processBookHolds(string $bookId)**: array
- Process holds when book becomes available
- Marks holds as ready
- Maintains queue order

**createLibraryCard(string $userId, string $cardNumber)**: array
- Create library card for patron
- Validates unique card number
- Generates issue date

**getReadingHistory(string $userId, int $page, int $perPage)**: array
- Get patron reading history
- Ordered by return date (most recent first)
- Paginated results

**getLibraryStatistics()**: array
- Get comprehensive library statistics
- Books: total, available, checked out
- Loans: total, active, overdue
- Holds: total, active
- Library cards: active count

### API Endpoints

#### Book Catalog

**GET /api/library/books/search**
- Query parameters:
  - `search` (string): Search term
  - `category` (string): Category code
  - `subject` (string): Subject code
  - `available_only` (boolean): Only available books
  - `page` (int, default 1): Page number
  - `per_page` (int, default 20): Items per page
- Response: Paginated book list

**GET /api/library/books/{id}**
- Get detailed book information
- Includes relationships (authors, categories, subjects, reviews)

#### Circulation

**POST /api/library/books/checkout**
- Request body:
  ```json
  {
    "book_id": "uuid",
    "library_card_id": "uuid" // optional
  }
  ```
- Response: Loan details or error

**POST /api/library/loans/{id}/return**
- Return borrowed book
- Calculates fines if overdue

**POST /api/library/loans/{id}/renew**
- Extend loan due date
- Validates renewal limits

#### Hold Management

**POST /api/library/holds/place**
- Request body:
  ```json
  {
    "book_id": "uuid"
  }
  ```
- Response: Hold details

**POST /api/library/holds/{id}/cancel**
- Cancel pending hold
- Requires patron ownership

**POST /api/library/books/{bookId}/holds/process**
- Process pending holds for available book
- Admin operation

#### Library Cards

**POST /api/library/cards/create**
- Request body:
  ```json
  {
    "card_number": "string"
  }
  ```
- Response: Library card details

#### Reading History

**GET /api/library/history**
- Query parameters:
  - `page` (int, default 1)
  - `per_page` (int, default 20)
- Response: Paginated reading history

#### Statistics

**GET /api/library/statistics**
- Response: Library statistics
  ```json
  {
    "books": {
      "total": 1000,
      "available": 850,
      "checked_out": 150
    },
    "loans": {
      "total": 500,
      "active": 200,
      "overdue": 15
    },
    "holds": {
      "total": 50,
      "active": 30
    },
    "library_cards": {
      "active": 300
    }
  }
  ```

## Loan Policies

Loan policies determine borrowing rules based on user type:

**Example Policy: Student**
- Max books: 5
- Loan duration: 14 days
- Renewal limit: 2
- Fine per day: $0.50
- Grace period: 3 days

**Example Policy: Teacher/Staff**
- Max books: 10
- Loan duration: 30 days
- Renewal limit: 5
- Fine per day: $0.25
- Grace period: 5 days

**Example Policy: General**
- Max books: 3
- Loan duration: 7 days
- Renewal limit: 1
- Fine per day: $1.00
- Grace period: 0 days

## Fine Calculation

Fines are calculated based on overdue days minus grace period:

```
Chargeable Days = Overdue Days - Grace Period
Fine Amount = Chargeable Days × Fine Per Day
```

**Example:**
- Book due: January 15
- Returned: January 20 (5 days overdue)
- Grace period: 3 days
- Fine per day: $0.50
- Calculation: (5 - 3) × $0.50 = $1.00

## Hold Queue Processing

When a book becomes available:
1. System finds all pending holds for the book
2. Sorts by hold date (first-come, first-served)
3. Marks first hold as "ready"
4. Sends notification to patron

Reference-only books cannot be placed on hold.

## Security & Access Control

**Authentication**
- All endpoints require JWT authentication
- User ID extracted from JWT token for patron operations

**Role-Based Access**
- Students, Teachers, Staff: Can checkout, return, renew, place holds
- Admin, Staff: Can process holds, view all statistics
- Admin only: Can create library cards

**Authorization**
- Patrons can only cancel their own holds
- Users can only view their own reading history
- Checkout validated against loan limits and availability

## Integration Points

### Student Information System
- User model provides patron information
- User roles determine applicable loan policies

### Fee Management System
- Fine amounts can be linked to fee payment system
- Library cards can require fee clearance

### Notification System
- Hold ready notifications
- Due date reminders
- Overdue notifications
- Fine payment reminders

## Testing

Run library system tests:
```bash
vendor/bin/co-phpunit tests/Feature/LibrarySystemTest.php
```

Test coverage includes:
- Book search with filters
- Book details retrieval
- Book categorization and subjects
- Loan policy calculations
- Hold management
- Library card management
- Model scopes and relationships

## Best Practices

1. **Checkout Validation**
   - Always check book availability before checkout
   - Validate loan limits based on user type
   - Create reading history entry on return

2. **Hold Management**
   - Process holds in queue order (first-come, first-served)
   - Set hold expiry dates (typically 7 days)
   - Notify patrons when holds become ready

3. **Fine Collection**
   - Calculate fines on book return
   - Update fine status when paid
   - Record fine payment date

4. **Reading History**
   - Create history entry on every book return
   - Maintain complete patron reading record
   - Archive old history periodically (if needed)

## Future Enhancements

Out of scope for initial implementation but planned for future:

- **Advanced Cataloging**
  - MARC record import/export
  - Dewey Decimal Classification
  - Call number management

- **Inventory Management**
  - Stock taking interface
  - Weeding recommendations
  - Collection development tools

- **Research Tools**
  - Citation generators (APA, MLA, Chicago)
  - Research guides
  - Bibliography management

- **Inter-library Loan**
  - Resource sharing with other libraries
  - Loan requests between institutions
  - Tracking and return coordination

- **Space Management**
  - Study room booking
  - Seat reservation system
  - Capacity management

## Troubleshooting

### Book Not Available for Checkout

**Issue**: Book shows as unavailable
**Solution**:
1. Check book availability quantity
2. Check if book is reference-only
3. Check total copies vs. available copies

### Holds Not Processing

**Issue**: Holds not becoming ready when book available
**Solution**:
1. Verify book has been returned and available_quantity updated
2. Check for pending holds in database
3. Run processHolds endpoint manually if needed

### Fine Calculation Incorrect

**Issue**: Fines not calculating correctly
**Solution**:
1. Verify loan policy is correct for user type
2. Check grace_period_days value
3. Verify fine_per_day is set correctly
4. Confirm return_date and due_date are accurate

### Reading History Missing

**Issue**: Reading history not recording
**Solution**:
1. Verify returnBook method is creating ReadingHistory entry
2. Check database connection
3. Review logs for errors

## Configuration

### Environment Variables

No specific environment variables required for library system.
Standard application configuration applies:
- Database connection
- JWT authentication
- Rate limiting

### Loan Policy Setup

Default loan policies should be created via database seeders:
```bash
php artisan db:seed --class=LoanPolicySeeder
```

Example seeder data:
- Student Policy (5 books, 14 days, 2 renewals)
- Teacher Policy (10 books, 30 days, 5 renewals)
- Staff Policy (10 books, 30 days, 5 renewals)
- General Policy (3 books, 7 days, 1 renewal)
