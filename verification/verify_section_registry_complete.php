<?php

/**
 * Loads the ACTUAL, unmodified App\Services\PageBuilder\SectionRegistry
 * and all 12 section definition classes (copied verbatim from app/) via a
 * minimal PSR-4 autoloader - same technique as checkpoint 1's
 * verify_section_registry.php, extended to the complete registry.
 * resolve() is never called here (that needs Eloquent/MySQL - covered by
 * verify_complete_registry_backend.php instead); this script verifies
 * registry lookup, type(), propsSchema(), defaultProps(), and isDynamic()
 * for all 12 real classes.
 */
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    if (! str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = __DIR__.'/app_stub/'.str_replace('\\', '/', $relative).'.php';
    if (file_exists($path)) {
        require $path;
    }
});

spl_autoload_register(function ($class) {
    $prefix = 'Illuminate\\';
    if (! str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = __DIR__.'/framework_stubs/Illuminate/'.str_replace('\\', '/', $relative).'.php';
    if (file_exists($path)) {
        require $path;
    }
});

function app(string $class)
{
    return new $class;
}

use App\Services\PageBuilder\SectionRegistry;

function pass(string $msg): void
{
    echo "  [PASS] $msg\n";
}
function fail(string $msg): void
{
    echo "  [FAIL] $msg\n";
    global $failures;
    $failures++;
}
$failures = 0;

$expectedTypes = [
    'hero', 'text', 'image', 'gallery',
    'facility-grid', 'restaurant-grid', 'service-grid',
    'events', 'offers', 'experiences',
    'cta', 'spacer',
];

echo "=== All 12 real definitions are registered ===\n";
count(SectionRegistry::types()) === 12 ? pass('exactly 12 types registered') : fail('got '.count(SectionRegistry::types()).' types');

foreach ($expectedTypes as $type) {
    SectionRegistry::has($type) ? pass("'{$type}' registered") : fail("'{$type}' MISSING from registry");
}

echo "\n=== Every real definition's metadata is internally consistent ===\n";
foreach (SectionRegistry::types() as $type) {
    $definition = SectionRegistry::get($type);
    $definition->type() === $type
        ? pass("'{$type}': type() matches registry key")
        : fail("'{$type}': type() returns '{$definition->type()}' - mismatch");

    is_array($definition->propsSchema()) ? pass("'{$type}': propsSchema() returns array") : fail("'{$type}': propsSchema() not an array");
    is_array($definition->defaultProps()) ? pass("'{$type}': defaultProps() returns array") : fail("'{$type}': defaultProps() not an array");
    is_bool($definition->isDynamic()) ? pass("'{$type}': isDynamic() returns bool") : fail("'{$type}': isDynamic() not a bool");
}

echo "\n=== isDynamic() matches the spec's designated dynamic sections ===\n";
$expectedDynamic = ['facility-grid', 'restaurant-grid', 'service-grid', 'events', 'offers', 'experiences'];
$expectedStatic = ['hero', 'text', 'image', 'gallery', 'cta', 'spacer'];

foreach ($expectedDynamic as $type) {
    SectionRegistry::get($type)->isDynamic() === true
        ? pass("'{$type}' correctly marked dynamic")
        : fail("'{$type}' should be dynamic but isn't");
}
foreach ($expectedStatic as $type) {
    SectionRegistry::get($type)->isDynamic() === false
        ? pass("'{$type}' correctly marked static")
        : fail("'{$type}' should be static but isn't");
}

echo "\n=== Unknown type still rejected (regression check after registry growth) ===\n";
! SectionRegistry::has('made-up-type') ? pass('unknown type correctly not recognized') : fail('unknown type incorrectly accepted');
$threw = false;
try {
    SectionRegistry::get('made-up-type');
} catch (InvalidArgumentException $e) {
    $threw = true;
}
$threw ? pass('get() still throws for unknown type') : fail('get() did not throw');

echo "\n".($failures === 0 ? 'ALL REGISTRY CLASS CHECKS PASSED (12/12 real definitions)' : "{$failures} CHECK(S) FAILED")."\n";
exit($failures === 0 ? 0 : 1);
