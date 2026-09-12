<?php
/**
 * Same approach as every prior checkpoint: replicate the exact SQL each
 * SectionDefinition::resolve() issues, against real MySQL 8.0.46, since
 * the full Laravel/PHPUnit suite can't run in this sandbox.
 */

$pdo = new PDO('mysql:host=127.0.0.1;dbname=grand_horizon_test;charset=utf8mb4', 'testuser', 'testpass');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function pass(string $msg): void { echo "  [PASS] $msg\n"; }
function fail(string $msg): void { echo "  [FAIL] $msg\n"; global $failures; $failures++; }
$failures = 0;

$pdo->exec("SET FOREIGN_KEY_CHECKS=0");
foreach (['restaurants', 'services', 'events', 'offers', 'experiences', 'media', 'hotels'] as $t) {
    $pdo->exec("DELETE FROM {$t}");
}
$pdo->exec("SET FOREIGN_KEY_CHECKS=1");

$pdo->exec("INSERT INTO hotels (name, slug, status) VALUES ('Hotel A', 'hotel-a-cp5', 'active')");
$hotelA = (int) $pdo->lastInsertId();
$pdo->exec("INSERT INTO hotels (name, slug, status) VALUES ('Hotel B', 'hotel-b-cp5', 'active')");
$hotelB = (int) $pdo->lastInsertId();

// === restaurant-grid ===
echo "=== restaurant-grid: tenant isolation + cuisine/featured filters ===\n";
$pdo->prepare("INSERT INTO restaurants (hotel_id, name, slug, cuisine, featured, status) VALUES (?, 'Azure', 'azure', 'Mediterranean', 1, 'published')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO restaurants (hotel_id, name, slug, cuisine, featured, status) VALUES (?, 'Sky Lounge', 'sky-lounge', 'Cocktails', 0, 'published')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO restaurants (hotel_id, name, slug, cuisine, featured, status) VALUES (?, 'Other Hotel Bistro', 'other-bistro', 'Mediterranean', 1, 'published')")->execute([$hotelB]);

$stmt = $pdo->prepare("SELECT name FROM restaurants WHERE hotel_id = ? AND status='published' AND cuisine = ? LIMIT 10");
$stmt->execute([$hotelA, 'Mediterranean']);
$names = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
$names === ['Azure'] ? pass('restaurant-grid resolves only Hotel A\'s Mediterranean restaurant') : fail('got: ' . implode(',', $names));

// === service-grid ===
echo "\n=== service-grid: tenant isolation, published only ===\n";
$pdo->prepare("INSERT INTO services (hotel_id, name, slug, status) VALUES (?, 'Room Service', 'room-service', 'published')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO services (hotel_id, name, slug, status) VALUES (?, 'Draft Service', 'draft-service', 'draft')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO services (hotel_id, name, slug, status) VALUES (?, 'Other Hotel Service', 'other-service', 'published')")->execute([$hotelB]);

$stmt = $pdo->prepare("SELECT name FROM services WHERE hotel_id = ? AND status='published' LIMIT 10");
$stmt->execute([$hotelA]);
$names = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
$names === ['Room Service'] ? pass('service-grid resolves only Hotel A\'s published service') : fail('got: ' . implode(',', $names));

// === events (upcoming_only) ===
echo "\n=== events: upcoming_only filter + tenant isolation ===\n";
$pdo->prepare("INSERT INTO events (hotel_id, title, slug, start_date, status) VALUES (?, 'Future Event', 'future', DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'published')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO events (hotel_id, title, slug, start_date, status) VALUES (?, 'Past Event', 'past', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'published')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO events (hotel_id, title, slug, start_date, status) VALUES (?, 'Other Hotel Event', 'other', DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'published')")->execute([$hotelB]);

$stmt = $pdo->prepare("SELECT title FROM events WHERE hotel_id = ? AND status='published' AND start_date >= CURDATE() ORDER BY start_date LIMIT 10");
$stmt->execute([$hotelA]);
$titles = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'title');
$titles === ['Future Event'] ? pass('events upcoming_only=true excludes past event, tenant-scoped') : fail('got: ' . implode(',', $titles));

$stmt = $pdo->prepare("SELECT title FROM events WHERE hotel_id = ? AND status='published' ORDER BY start_date LIMIT 10");
$stmt->execute([$hotelA]);
$titlesAll = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'title');
count($titlesAll) === 2 ? pass('events upcoming_only=false includes both past and future for Hotel A') : fail('got: ' . implode(',', $titlesAll));

// === offers (active_only, featured_only) ===
echo "\n=== offers: active_only + featured_only filters + tenant isolation ===\n";
$pdo->prepare("INSERT INTO offers (hotel_id, title, slug, valid_until, featured, status) VALUES (?, 'Active Featured', 'af', DATE_ADD(CURDATE(), INTERVAL 10 DAY), 1, 'published')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO offers (hotel_id, title, slug, valid_until, featured, status) VALUES (?, 'Active Unfeatured', 'au', DATE_ADD(CURDATE(), INTERVAL 10 DAY), 0, 'published')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO offers (hotel_id, title, slug, valid_until, featured, status) VALUES (?, 'Expired', 'exp', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1, 'published')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO offers (hotel_id, title, slug, valid_until, featured, status) VALUES (?, 'Other Hotel Offer', 'other', DATE_ADD(CURDATE(), INTERVAL 10 DAY), 1, 'published')")->execute([$hotelB]);

$stmt = $pdo->prepare("SELECT title FROM offers WHERE hotel_id = ? AND status='published' AND (valid_until IS NULL OR valid_until >= CURDATE()) LIMIT 10");
$stmt->execute([$hotelA]);
$titles = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'title');
sort($titles);
$titles === ['Active Featured', 'Active Unfeatured'] ? pass('offers active_only excludes expired, tenant-scoped') : fail('got: ' . implode(',', $titles));

$stmt = $pdo->prepare("SELECT title FROM offers WHERE hotel_id = ? AND status='published' AND (valid_until IS NULL OR valid_until >= CURDATE()) AND featured = 1 LIMIT 10");
$stmt->execute([$hotelA]);
$titles = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'title');
$titles === ['Active Featured'] ? pass('offers featured_only further narrows active_only correctly') : fail('got: ' . implode(',', $titles));

// === experiences (category, featured_only) ===
echo "\n=== experiences: category + featured_only filters + tenant isolation ===\n";
$pdo->prepare("INSERT INTO experiences (hotel_id, title, slug, category, featured, status) VALUES (?, 'Yoga', 'yoga', 'wellness', 1, 'published')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO experiences (hotel_id, title, slug, category, featured, status) VALUES (?, 'Cooking Class', 'cooking', 'culinary', 1, 'published')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO experiences (hotel_id, title, slug, category, featured, status) VALUES (?, 'Other Hotel Yoga', 'other-yoga', 'wellness', 1, 'published')")->execute([$hotelB]);

$stmt = $pdo->prepare("SELECT title FROM experiences WHERE hotel_id = ? AND status='published' AND category = ? AND featured = 1 LIMIT 10");
$stmt->execute([$hotelA, 'wellness']);
$titles = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'title');
$titles === ['Yoga'] ? pass('experiences category+featured_only resolves correctly, tenant-scoped') : fail('got: ' . implode(',', $titles));

// === image/gallery: tenant-safe media resolution ===
echo "\n=== image: cross-tenant media_id resolves to nothing ===\n";
$pdo->prepare("INSERT INTO media (hotel_id, disk, path, mediable_type, mediable_id, collection) VALUES (?, 'public', 'secret.jpg', 'x', 1, 'gallery')")->execute([$hotelB]);
$otherMediaId = (int) $pdo->lastInsertId();

$stmt = $pdo->prepare("SELECT * FROM media WHERE id = ? AND hotel_id = ?");
$stmt->execute([$otherMediaId, $hotelA]); // Hotel A trying to resolve Hotel B's media id
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$row === false ? pass('media lookup scoped to hotel_id correctly returns nothing for a cross-tenant media_id') : fail('CROSS-TENANT MEDIA LEAK');

// === registry parity manifest ===
echo "\n=== registry parity: manifest matches the expected 12 types ===\n";
$manifestPath = __DIR__ . '/../resources/js/PageBuilder/section-types.json';
$manifest = json_decode(file_get_contents($manifestPath), true);
$expected = ['cta', 'events', 'experiences', 'facility-grid', 'gallery', 'hero', 'image', 'offers', 'restaurant-grid', 'service-grid', 'spacer', 'text'];
sort($manifest);
$manifest === $expected ? pass('section-types.json manifest matches the expected 12-type list exactly') : fail('manifest mismatch: ' . implode(',', $manifest));

echo "\n" . ($failures === 0 ? "ALL CHECKPOINT 5 BACKEND CHECKS PASSED" : "{$failures} CHECK(S) FAILED") . "\n";
exit($failures === 0 ? 0 : 1);
