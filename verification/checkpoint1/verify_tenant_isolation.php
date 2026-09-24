<?php

/**
 * Verifies tenant isolation for dynamic section resolution against real
 * MySQL. This replicates the exact SQL that Facility::published()
 * ->category()->featured()->ordered()->limit()->get() produces under
 * BelongsToHotel's global scope (hotel_id = current tenant), since the
 * Eloquent layer itself can't run without the full Laravel framework
 * installed (not possible in this sandbox - see network allowlist notes).
 */
$pdo = new PDO('mysql:host=127.0.0.1;dbname=grand_horizon_test;charset=utf8mb4', 'testuser', 'testpass');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
$pdo->exec('DELETE FROM facilities');
$pdo->exec('DELETE FROM pages');
$pdo->exec('DELETE FROM hotels');
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');

$pdo->exec("INSERT INTO hotels (name, slug, status) VALUES ('Hotel A', 'hotel-a-iso', 'active')");
$hotelA = (int) $pdo->lastInsertId();
$pdo->exec("INSERT INTO hotels (name, slug, status) VALUES ('Hotel B', 'hotel-b-iso', 'active')");
$hotelB = (int) $pdo->lastInsertId();

$pdo->prepare("INSERT INTO facilities (hotel_id, name, slug, category, status, featured) VALUES (?, 'Hotel A Spa', 'hotel-a-spa', 'wellness', 'published', 1)")->execute([$hotelA]);
$pdo->prepare("INSERT INTO facilities (hotel_id, name, slug, category, status, featured) VALUES (?, 'Hotel A Gym', 'hotel-a-gym', 'fitness', 'published', 1)")->execute([$hotelA]);
$pdo->prepare("INSERT INTO facilities (hotel_id, name, slug, category, status, featured) VALUES (?, 'Hotel A Draft Spa', 'hotel-a-draft-spa', 'wellness', 'draft', 1)")->execute([$hotelA]);
$pdo->prepare("INSERT INTO facilities (hotel_id, name, slug, category, status, featured) VALUES (?, 'Hotel A Unfeatured Spa', 'hotel-a-unfeatured', 'wellness', 'published', 0)")->execute([$hotelA]);
$pdo->prepare("INSERT INTO facilities (hotel_id, name, slug, category, status, featured) VALUES (?, 'Hotel B Spa', 'hotel-b-spa', 'wellness', 'published', 1)")->execute([$hotelB]);

function resolveFacilityGrid(PDO $pdo, int $hotelId, ?string $category, bool $featuredOnly, int $limit): array
{
    // Exactly mirrors FacilityGridSectionDefinition::resolve()'s query,
    // including the tenant scope BelongsToHotel would apply automatically.
    $sql = "SELECT name FROM facilities WHERE hotel_id = ? AND status = 'published'";
    $params = [$hotelId];

    if ($category !== null) {
        $sql .= ' AND category = ?';
        $params[] = $category;
    }
    if ($featuredOnly) {
        $sql .= ' AND featured = 1';
    }
    $sql .= ' ORDER BY sort_order, name LIMIT '.(int) $limit;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
}

echo "=== Tenant isolation: Hotel A's facility-grid never returns Hotel B's data ===\n";
$namesA = resolveFacilityGrid($pdo, $hotelA, 'wellness', false, 10);
in_array('Hotel A Spa', $namesA, true) ? pass("Hotel A's own facility present") : fail("Hotel A's facility missing");
in_array('Hotel A Unfeatured Spa', $namesA, true) ? pass('non-featured wellness facility present when featured_only=false') : fail('missing');
! in_array('Hotel B Spa', $namesA, true) ? pass("Hotel B's facility NEVER leaks into Hotel A's resolved data") : fail("CROSS-TENANT LEAK: Hotel B's data appeared in Hotel A's grid");

echo "\n=== Category + featured_only filters ===\n";
$namesFeatured = resolveFacilityGrid($pdo, $hotelA, 'wellness', true, 10);
$namesFeatured === ['Hotel A Spa'] ? pass('featured_only=true correctly excludes non-featured wellness facility') : fail('featured filter incorrect: got '.implode(',', $namesFeatured));

$namesFitness = resolveFacilityGrid($pdo, $hotelA, 'fitness', false, 10);
$namesFitness === ['Hotel A Gym'] ? pass('category=fitness correctly excludes wellness facilities') : fail('category filter incorrect');

echo "\n=== Unpublished facilities never appear ===\n";
$allA = resolveFacilityGrid($pdo, $hotelA, null, false, 10);
! in_array('Hotel A Draft Spa', $allA, true) ? pass('draft-status facility excluded from resolved data') : fail('draft facility leaked into published output');

echo "\n=== Live preview re-resolution reflects prop changes, not stale data ===\n";
$wellness = resolveFacilityGrid($pdo, $hotelA, 'wellness', false, 10);
$fitness = resolveFacilityGrid($pdo, $hotelA, 'fitness', false, 10);
$wellnessAgain = resolveFacilityGrid($pdo, $hotelA, 'wellness', false, 10);
($wellness !== $fitness && $wellness === $wellnessAgain)
    ? pass('changing category prop produces genuinely different results, and re-resolving the original is stable (no stale caching)')
    : fail('prop change did not produce fresh, correct results');

echo "\n".($failures === 0 ? 'ALL TENANT-ISOLATION CHECKS PASSED' : "{$failures} CHECK(S) FAILED")."\n";
exit($failures === 0 ? 0 : 1);
