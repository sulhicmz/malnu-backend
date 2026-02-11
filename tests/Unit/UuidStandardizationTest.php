<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Allergy;
use App\Models\Calendar;
use App\Models\Role;
use App\Models\User;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

/**
 * @internal
 * @coversNothing
 */
class UuidStandardizationTest extends TestCase
{
    public function testModelsHaveCorrectUuidProperties()
    {
        $models = [
            User::class,
            Role::class,
            Allergy::class,
            Calendar::class,
        ];

        foreach ($models as $model) {
            $instance = new $model();

            $this->assertEquals('id', $instance->getKeyName());
            $this->assertEquals('string', $instance->getKeyType());
            $this->assertFalse($instance->getIncrementing());
        }
    }

    public function testModelsDeclareStrictTypes()
    {
        $reflection = new ReflectionClass(User::class);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        $primaryKey = null;
        $keyType = null;
        $incrementing = null;

        foreach ($properties as $property) {
            if ($property->getName() === 'primaryKey') {
                $this->assertEquals('string', $property->getType()->getName());
            }
            if ($property->getName() === 'keyType') {
                $this->assertEquals('string', $property->getType()->getName());
            }
            if ($property->getName() === 'incrementing') {
                $this->assertEquals('bool', $property->getType()->getName());
            }
        }

        $this->assertNotNull($primaryKey);
        $this->assertNotNull($keyType);
        $this->assertNotNull($incrementing);
    }

    public function testAllModelsHaveUuidConfiguration()
    {
        $modelFiles = glob(app_path('Models/**/*.php'));
        $hasConfig = 0;
        $missingConfig = [];

        foreach ($modelFiles as $file) {
            $content = file_get_contents($file);
            $className = basename($file, '.php');

            $hasPrimaryKey = str_contains($content, 'protected string $primaryKey');
            $hasKeyType = str_contains($content, 'protected string $keyType');
            $hasIncrementing = str_contains($content, 'public bool $incrementing');

            if ($hasPrimaryKey && $hasKeyType && $hasIncrementing) {
                ++$hasConfig;
            } else {
                $missingConfig[] = $className;
            }
        }

        $this->assertGreaterThan(50, $hasConfig, 'Expected most models to have UUID configuration');
        $this->assertCount(0, $missingConfig, 'No models should be missing UUID configuration: ' . implode(', ', $missingConfig));
    }
}
