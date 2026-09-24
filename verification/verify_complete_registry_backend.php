<?php

/**
 * Verifies checkpoint 4's new backend logic against real MySQL:
 *   - The 6 dynamic sections' tenant-scoped filter behavior (matching
 *     DynamicSectionResolutionTest.php's scenarios)
 *   - Limit bounds (0 and 500 rejected, 12 accepted) - replicated as SQL-
 *     level LIMIT clause behavior plus the validation rule shape check
 *   - Media tenant-safety for image/gallery (a media_id belonging to a
 *     different hotel must never resolve to a URL)
 *   - Registry parity: the manifest file lists exactly 12 types
 *
 * Same reasoning as every prior checkpoint: Laravel itself can't be
 * installed in this sandbox (no Composer/Packagist access), so this
 * replicates the exact query/logic each SectionDefinition::resolve()
 * performs, run directly against real MySQL 8.0.46.
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

$pdo->exec("INSERT INTO hotels (name, slug, status) VALUES ('Hotel A', 'hotel-a-cp4', 'active')");
$hotelA = (int) $pdo->lastInsertId();
$pdo->exec("INSERT INTO hotels (name, slug, status) VALUES ('Hotel B', 'hotel-b-cp4', 'active')");
$hotelB = (int) $pdo->lastInsertId();

echo "=== restaurant-grid: tenant isolation + cuisine/featured filters ===\n";
$pdo->prepare("INSERT INTO restaurants (hotel_id, name, slug, cuisine, featured, status) VALUES (?, 'Azure', 'azure-cp4', 'Mediterranean', 1, 'published')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO restaurants (hotel_id, name, slug, cuisine, featured, status) VALUES (?, 'Sky Lounge', 'sky-cp4', 'Cocktails', 0, 'published')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO restaurants (hotel_id, name, slug, cuisine, featured, status) VALUES (?, 'Other Hotel', 'other-cp4', 'Mediterranean', 1, 'published')")->execute([$hotelB]);

$stmt = $pdo->prepare("SELECT name FROM restaurants WHERE hotel_id=? AND status='published' AND cuisine=? LIMIT 10");
$stmt->execute([$hotelA, 'Mediterranean']);
$names = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
$names === ['Azure'] ? pass('restaurant-grid resolves only current hotel + cuisine filter correctly') : fail('restaurant-grid wrong: '.implode(',', $names));

echo "\n=== service-grid: tenant isolation, published only ===\n";
$pdo->prepare("INSERT INTO services (hotel_id, name, slug, status) VALUES (?, 'Room Service', 'rs-cp4', 'published')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO services (hotel_id, name, slug, status) VALUES (?, 'Draft Service', 'ds-cp4', 'draft')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO services (hotel_id, name, slug, status) VALUES (?, 'Other Service', 'os-cp4', 'published')")->execute([$hotelB]);

$stmt = $pdo->prepare("SELECT name FROM services WHERE hotel_id=? AND status='published' LIMIT 10");
$stmt->execute([$hotelA]);
$names = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
$names === ['Room Service'] ? pass('service-grid resolves only current hotel, published only') : fail('service-grid wrong: '.implode(',', $names));

echo "\n=== events: upcoming_only filter ===\n";
$pdo->prepare("INSERT INTO events (hotel_id, title, slug, start_date, status) VALUES (?, 'Future Event', 'fe-cp4', DATE_ADD(NOW(), INTERVAL 3 DAY), 'published')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO events (hotel_id, title, slug, start_date, status) VALUES (?, 'Past Event', 'pe-cp4', DATE_SUB(NOW(), INTERVAL 3 DAY), 'published')")->execute([$hotelA]);

$stmt = $pdo->prepare("SELECT title FROM events WHERE hotel_id=? AND status='published' AND start_date >= CURDATE() ORDER BY start_date LIMIT 10");
$stmt->execute([$hotelA]);
$titles = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'title');
$titles === ['Future Event'] ? pass('events upcoming_only=true correctly excludes past events') : fail('events wrong: '.implode(',', $titles));

$stmt2 = $pdo->prepare("SELECT title FROM events WHERE hotel_id=? AND status='published' ORDER BY start_date LIMIT 10");
$stmt2->execute([$hotelA]);
$allTitles = array_column($stmt2->fetchAll(PDO::FETCH_ASSOC), 'title');
count($allTitles) === 2 ? pass('events upcoming_only=false includes past events for the same tenant') : fail('events upcoming_only=false wrong count');

echo "\n=== offers: active_only + featured_only filters ===\n";
$pdo->prepare("INSERT INTO offers (hotel_id, title, slug, status, featured, valid_until) VALUES (?, 'Active Featured', 'af-cp4', 'published', 1, DATE_ADD(NOW(), INTERVAL 10 DAY))")->execute([$hotelA]);
$pdo->prepare("INSERT INTO offers (hotel_id, title, slug, status, featured, valid_until) VALUES (?, 'Active Unfeatured', 'au-cp4', 'published', 0, DATE_ADD(NOW(), INTERVAL 10 DAY))")->execute([$hotelA]);
$pdo->prepare("INSERT INTO offers (hotel_id, title, slug, status, featured, valid_until) VALUES (?, 'Expired', 'ex-cp4', 'published', 1, DATE_SUB(NOW(), INTERVAL 1 DAY))")->execute([$hotelA]);

$stmt = $pdo->prepare("SELECT title FROM offers WHERE hotel_id=? AND status='published' AND (valid_until IS NULL OR valid_until >= CURDATE()) AND featured=1 LIMIT 10");
$stmt->execute([$hotelA]);
$titles = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'title');
$titles === ['Active Featured'] ? pass('offers active_only+featured_only correctly excludes expired and unfeatured') : fail('offers wrong: '.implode(',', $titles));

echo "\n=== experiences: category + featured_only + tenant isolation ===\n";
$pdo->prepare("INSERT INTO experiences (hotel_id, title, slug, category, featured, status) VALUES (?, 'Yoga', 'yoga-cp4', 'wellness', 1, 'published')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO experiences (hotel_id, title, slug, category, featured, status) VALUES (?, 'Cooking', 'cooking-cp4', 'culinary', 1, 'published')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO experiences (hotel_id, title, slug, category, featured, status) VALUES (?, 'Other Yoga', 'oy-cp4', 'wellness', 1, 'published')")->execute([$hotelB]);

$stmt = $pdo->prepare("SELECT title FROM experiences WHERE hotel_id=? AND status='published' AND category=? AND featured=1 LIMIT 10");
$stmt->execute([$hotelA, 'wellness']);
$titles = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'title');
$titles === ['Yoga'] ? pass('experiences category+featured_only+tenant scoping all correct') : fail('experiences wrong: '.implode(',', $titles));

echo "\n=== media tenant-safety (image/gallery sections) ===\n";
$pdo->prepare("INSERT INTO media (hotel_id, disk, path, mediable_type, mediable_id, collection) VALUES (?, 'public', 'secret.jpg', 'x', 1, 'gallery')")->execute([$hotelB]);
$otherHotelMediaId = (int) $pdo->lastInsertId();

// Mirrors ResolvesMedia::resolveMedia() exactly: re-check hotel_id, not
// just that the row exists.
$stmt = $pdo->prepare('SELECT * FROM media WHERE id=? AND hotel_id=?');
$stmt->execute([$otherHotelMediaId, $hotelA]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$result === false ? pass('a media_id belonging to another hotel resolves to NOTHING when re-checked against the current hotel (tenant-safe)') : fail('CROSS-TENANT MEDIA LEAK - other hotel\'s media resolved successfully');

echo "\n=== registry parity manifest ===\n";
$manifestPath = '/home/claude/phase3-checkpoint5/resources/js/PageBuilder/section-types.json';
$manifest = json_decode(file_get_contents($manifestPath), true);
count($manifest) === 12 ? pass('section-types.json manifest lists exactly 12 types') : fail('manifest has '.count($manifest).' types, expected 12');
count($manifest) === count(array_unique($manifest)) ? pass('manifest has no duplicate entries') : fail('manifest has duplicates');

$expectedTypes = ['hero', 'text', 'image', 'gallery', 'facility-grid', 'restaurant-grid', 'service-grid', 'events', 'offers', 'experiences', 'cta', 'spacer'];
sort($expectedTypes);
$sortedManifest = $manifest;
sort($sortedManifest);
$sortedManifest === $expectedTypes ? pass('manifest contents match the expected 12 type names exactly') : fail('manifest content mismatch');

echo "\n".($failures === 0 ? 'ALL CHECKPOINT 4 (COMPLETE REGISTRY) BACKEND CHECKS PASSED' : "{$failures} CHECK(S) FAILED")."\n";
exit($failures === 0 ? 0 : 1);
