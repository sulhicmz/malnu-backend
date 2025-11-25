# Migration Guidelines

## Required Imports

All migration files must include the following imports at the top:

```php
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Support\Facades\Schema;
use Hyperf\DbConnection\Db;
```

The `use Hyperf\DbConnection\Db;` import is required when using `DB::raw('(UUID())')` for UUID generation.

## Best Practices

1. Always include the `use Hyperf\DbConnection\Db;` import in new migration files
2. Use `DB::raw('(UUID())')` for UUID generation in table schemas
3. Follow the naming convention: `YYYY_MM_DD_HHMMSS_description.php`
4. Include both `up()` and `down()` methods
5. Make sure `down()` completely reverses the `up()` operation

## Example Migration

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