# Migration Guidelines

## Required Imports

All migration files must include the following imports at the top:

```php
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
```

If you plan to use direct database queries, you can import the Db facade:

```php
use Hyperf\DbConnection\Db;
```

In this case, use `Db::raw('(UUID())')` (lowercase 'd' and 'b').

Alternatively, you can use the global facade alias `DB::raw('(UUID())')` (uppercase 'D' and 'B') without importing, as it's configured in `config/app.php`.

## Best Practices

1. Always include the required imports: `use Hyperf\Database\Migrations\Migration;`, `use Hyperf\Database\Schema\Blueprint;`, `use Hyperf\Support\Facades\Schema;`
2. If using direct database queries, include `use Hyperf\DbConnection\Db;` and use `Db::raw('(UUID())')`
3. Alternatively, you can use the global facade alias `DB::raw('(UUID())')` without importing
4. Follow the naming convention: `YYYY_MM_DD_HHMMSS_description.php`
5. Include both `up()` and `down()` methods
6. Make sure `down()` completely reverses the `up()` operation

## Example Migration

### Using the imported facade:
```php
<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('example_table', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Db::raw('(UUID())'));
            $table->string('name');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('example_table');
    }
};
```

### Using the global facade alias (no import needed):
```php
<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('example_table', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->string('name');
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('example_table');
    }
};
```

## Testing Migrations

To test migration functionality:

```bash
php bin/hyperf.php migrate
php bin/hyperf.php migrate:rollback
php bin/hyperf.php migrate:status
```