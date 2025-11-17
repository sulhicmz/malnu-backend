# Web School Module v12

Modular web-based school management system built with Laravel 12.

## About

This module provides a comprehensive web interface for school management, including:
- User authentication and authorization
- Role-based permissions
- Data management with DataTables
- Modular architecture for easy extension

## Project Structure

```
web-sch-12/
├── app/                    # Application core
├── Modules/                # Laravel modules
│   ├── AiLearning/        # AI-powered learning features
│   ├── UserManagement/    # User management module
│   └── ...                # Other modules
├── config/                # Configuration files
├── database/              # Database migrations and seeders
├── public/                # Public assets
├── resources/             # Views and frontend assets
├── routes/                # Route definitions
└── tests/                 # Test files
```

## Database Schema

The system uses a modular database structure with:
- Users and roles management
- Permission system
- Academic data structures
- AI learning analytics

## Installation

1. Navigate to the module directory:
   ```bash
   cd web-sch-12
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node.js dependencies:
   ```bash
   npm install
   ```

4. Environment setup:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Database setup:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. Build frontend assets:
   ```bash
   npm run build
   ```

7. Start development server:
   ```bash
   php artisan serve
   ```

## Development Workflow

1. **Feature Development**: Create new features in appropriate modules
2. **Testing**: Write and run tests with `php artisan test`
3. **Code Quality**: Use Laravel Pint for code formatting
4. **Frontend**: Build assets with `npm run dev` or `npm run build`

## Available Modules

- **AiLearning**: AI-powered educational features
- **UserManagement**: User roles and permissions
- **Analytics**: Data analysis and reporting
- **Core**: Core system functionality

## Dependencies

- Laravel 12
- Laravel Modules (nwidart/laravel-modules)
- Laravel Permissions (spatie/laravel-permission)
- DataTables (yajra/laravel-datatables)
- Vue.js/React for frontend components

## Testing

Run the test suite:
```bash
php artisan test
```

## Contributing

Follow the main project's contributing guidelines in the root CONTRIBUTING.md file.
