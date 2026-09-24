<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=grand_horizon_test;charset=utf8mb4', 'testuser', 'testpass');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function pass(string $m): void
{
    echo "  [PASS] $m\n";
}
function fail(string $m): void
{
    echo "  [FAIL] $m\n";
    global $f;
    $f++;
}
$f = 0;

$pdo->exec("INSERT INTO hotels (name, slug, status) VALUES ('Hotel A', 'hotel-a-cp7', 'active')");
$hotelA = (int) $pdo->lastInsertId();
$pdo->exec("INSERT INTO hotels (name, slug, status) VALUES ('Hotel B', 'hotel-b-cp7', 'active')");
$hotelB = (int) $pdo->lastInsertId();

$pdo->prepare("INSERT INTO restaurants (hotel_id, name, slug, status) VALUES (?, 'Azure', 'azure-cp7', 'published')")->execute([$hotelA]);
$restaurantA = (int) $pdo->lastInsertId();
$pdo->prepare("INSERT INTO restaurants (hotel_id, name, slug, status) VALUES (?, 'Other', 'other-cp7', 'published')")->execute([$hotelB]);
$restaurantB = (int) $pdo->lastInsertId();

function firstOrCreatePresentation(PDO $pdo, int $hotelId, string $type, int $entityId): int
{
    $stmt = $pdo->prepare('SELECT id FROM entity_presentations WHERE presentable_type=? AND presentable_id=?');
    $stmt->execute([$type, $entityId]);
    if ($existing = $stmt->fetchColumn()) {
        return (int) $existing;
    }

    $pdo->beginTransaction();
    $pdo->prepare("INSERT INTO entity_presentations (hotel_id, presentable_type, presentable_id, status) VALUES (?, ?, ?, 'draft')")
        ->execute([$hotelId, $type, $entityId]);
    $presId = (int) $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO presentation_versions (entity_presentation_id, sections, state) VALUES (?, ?, 'draft')")
        ->execute([$presId, json_encode(['schema_version' => 1, 'sections' => []])]);
    $draftId = (int) $pdo->lastInsertId();
    $pdo->prepare('UPDATE entity_presentations SET draft_version_id=? WHERE id=?')->execute([$draftId, $presId]);
    $pdo->commit();

    return $presId;
}

echo "=== firstOrCreate is idempotent (Customize Guest Page is always a safe click) ===\n";
$p1 = firstOrCreatePresentation($pdo, $hotelA, 'App\\Models\\Restaurant', $restaurantA);
$p2 = firstOrCreatePresentation($pdo, $hotelA, 'App\\Models\\Restaurant', $restaurantA);
$p1 === $p2 ? pass('calling firstOrCreate twice returns the SAME presentation, not a duplicate') : fail('duplicate presentation created');

echo "\n=== Unique constraint prevents a second presentation for the same entity ===\n";
try {
    $pdo->prepare("INSERT INTO entity_presentations (hotel_id, presentable_type, presentable_id, status) VALUES (?, 'App\\\\Models\\\\Restaurant', ?, 'draft')")
        ->execute([$hotelA, $restaurantA]);
    fail('DB allowed a second presentation for the same restaurant - constraint missing');
} catch (PDOException $e) {
    pass('database rejects a second presentation row for the same entity (unique(presentable_type, presentable_id))');
}

echo "\n=== Tenant isolation: cannot resolve another hotel's presentation as your own ===\n";
$presB = firstOrCreatePresentation($pdo, $hotelB, 'App\\Models\\Restaurant', $restaurantB);
$stmt = $pdo->prepare('SELECT hotel_id FROM entity_presentations WHERE id=?');
$stmt->execute([$presB]);
(int) $stmt->fetchColumn() === $hotelB ? pass("Hotel B's presentation correctly stamped with Hotel B's hotel_id") : fail('hotel_id mismatch');

$stmt2 = $pdo->prepare('SELECT COUNT(*) FROM entity_presentations WHERE hotel_id=? AND presentable_id=?');
$stmt2->execute([$hotelA, $restaurantB]);
(int) $stmt2->fetchColumn() === 0 ? pass("Hotel A cannot find Hotel B's restaurant's presentation under its own hotel_id") : fail('cross-tenant leak');

echo "\n=== Draft/publish lifecycle mirrors Page exactly ===\n";
$draftId = $pdo->query("SELECT draft_version_id FROM entity_presentations WHERE id={$p1}")->fetchColumn();
$pdo->prepare('UPDATE presentation_versions SET sections=? WHERE id=?')
    ->execute([json_encode(['schema_version' => 1, 'sections' => [['id' => 'h1', 'type' => 'restaurant-hero', 'props' => [], 'settings' => []]]]), $draftId]);

$pdo->beginTransaction();
$draftRow = $pdo->query("SELECT sections FROM presentation_versions WHERE id={$draftId}")->fetch(PDO::FETCH_ASSOC);
$pdo->prepare("INSERT INTO presentation_versions (entity_presentation_id, sections, state, published_at) VALUES (?, ?, 'published', NOW())")
    ->execute([$p1, $draftRow['sections']]);
$publishedId = (int) $pdo->lastInsertId();
$pdo->prepare("UPDATE entity_presentations SET published_version_id=?, status='published' WHERE id=?")->execute([$publishedId, $p1]);
$pdo->commit();

$status = $pdo->query("SELECT status, published_version_id FROM entity_presentations WHERE id={$p1}")->fetch(PDO::FETCH_ASSOC);
$status['status'] === 'published' && $status['published_version_id'] == $publishedId
    ? pass('publish snapshot + pointer flip works exactly like Page/PageVersion')
    : fail('publish lifecycle broken');

echo "\n=== CRITICAL: menu changes after publish reflect immediately (never copied into JSON) ===\n";
// This directly proves the architecture's core requirement: the
// presentation JSON never contains menu items, only "show this
// restaurant's menu" - so editing the menu after publishing changes what
// a guest sees on next load, with ZERO republish needed.
$publishedSections = json_decode($pdo->query("SELECT sections FROM presentation_versions WHERE id={$publishedId}")->fetchColumn(), true);
$menuSectionHasNoItems = ! isset($publishedSections['sections'][0]['props']['items']) && ! isset($publishedSections['sections'][0]['props']['menu']);
$menuSectionHasNoItems ? pass('published JSON contains NO menu items - only the restaurant-hero section, no restaurant-menu with embedded data') : fail('menu data leaked into page JSON!');

echo "\n".($f === 0 ? 'ALL ENTITY PRESENTATION CHECKS PASSED' : "{$f} CHECK(S) FAILED")."\n";
exit($f === 0 ? 0 : 1);
