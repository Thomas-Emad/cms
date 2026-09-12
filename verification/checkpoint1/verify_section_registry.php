<?php
/**
 * Loads the ACTUAL, unmodified App\Services\PageBuilder\SectionRegistry
 * and its section definition classes (copied verbatim, not reimplemented)
 * and exercises them directly with a minimal PSR-4 autoloader - no
 * Laravel framework needed for this piece, since registry lookup and
 * propsSchema()/defaultProps()/isDynamic() are plain PHP with no Eloquent
 * dependency. Only SectionDefinition::resolve() needs Eloquent, and this
 * script doesn't call resolve() - that's covered separately in
 * verify_tenant_isolation.php against real MySQL.
 *
 * A minimal `app()` helper is stubbed since SectionRegistry::get() calls
 * it as a service-container resolver; here it just does `new $class()`,
 * which is behaviorally identical for these stateless definition classes.
 */

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/app_stub/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($path)) require $path;
});

spl_autoload_register(function ($class) {
    $prefix = 'Illuminate\\';
    if (!str_starts_with($class, $prefix)) return;
    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/framework_stubs/Illuminate/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($path)) require $path;
});

function app(string $class) { return new $class(); }

use App\Services\PageBuilder\SectionRegistry;

function pass(string $msg): void { echo "  [PASS] $msg\n"; }
function fail(string $msg): void { echo "  [FAIL] $msg\n"; global $failures; $failures++; }
$failures = 0;

echo "=== SectionRegistry: known types ===\n";
SectionRegistry::has('hero') ? pass("'hero' recognized") : fail("'hero' not recognized");
SectionRegistry::has('text') ? pass("'text' recognized") : fail("'text' not recognized");
SectionRegistry::has('facility-grid') ? pass("'facility-grid' recognized") : fail("'facility-grid' not recognized");

echo "\n=== SectionRegistry: unknown types are rejected, not silently accepted ===\n";
!SectionRegistry::has('evil-custom-html') ? pass("unknown type 'evil-custom-html' correctly NOT recognized") : fail('unknown type was accepted!');

$threw = false;
try {
    SectionRegistry::get('evil-custom-html');
} catch (InvalidArgumentException $e) {
    $threw = true;
}
$threw ? pass('SectionRegistry::get() throws for an unknown type rather than returning something usable') : fail('get() did not throw for unknown type');

echo "\n=== Per-type schema correctness ===\n";
$hero = SectionRegistry::get('hero');
$hero->isDynamic() === false ? pass("hero correctly marked as NOT dynamic (no DB query, no preview-resolve network call needed)") : fail('hero should not be dynamic');
array_key_exists('title', $hero->propsSchema()) ? pass('hero propsSchema requires a title') : fail('hero schema missing title rule');

$facilityGrid = SectionRegistry::get('facility-grid');
$facilityGrid->isDynamic() === true ? pass('facility-grid correctly marked as dynamic') : fail('facility-grid should be dynamic');
$schema = $facilityGrid->propsSchema();
foreach (['category', 'featured_only', 'limit', 'columns'] as $key) {
    array_key_exists($key, $schema) ? pass("facility-grid schema validates '{$key}'") : fail("facility-grid schema missing '{$key}'");
}
$defaults = $facilityGrid->defaultProps();
$defaults['limit'] === 6 ? pass('facility-grid default limit is 6, matching the design spec example') : fail('unexpected default limit: ' . $defaults['limit']);

echo "\n" . ($failures === 0 ? "ALL SECTIONREGISTRY CHECKS PASSED" : "{$failures} CHECK(S) FAILED") . "\n";
exit($failures === 0 ? 0 : 1);
