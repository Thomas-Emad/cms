<?php

/**
 * Same approach as every prior checkpoint: replicate the exact SQL/logic
 * the new AdminPageController::index()/store() and StorePageRequest
 * would issue, against real MySQL, since the full Laravel/Inertia HTTP
 * stack can't be installed in this sandbox (no Composer/Packagist
 * access).
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

$pdo->exec("INSERT INTO hotels (name, slug, status) VALUES ('Hotel A', 'hotel-a-cp6', 'active')");
$hotelA = (int) $pdo->lastInsertId();
$pdo->exec("INSERT INTO hotels (name, slug, status) VALUES ('Hotel B', 'hotel-b-cp6', 'active')");
$hotelB = (int) $pdo->lastInsertId();

function createPage(PDO $pdo, int $hotelId, string $name, string $slug, bool $isHome = false): int
{
    // Mirrors CreatePageAction::execute() exactly.
    $pdo->beginTransaction();
    if ($isHome) {
        $pdo->prepare('UPDATE pages SET is_home = 0 WHERE hotel_id = ?')->execute([$hotelId]);
    }
    $stmt = $pdo->prepare("INSERT INTO pages (hotel_id, name, slug, is_home, status) VALUES (?, ?, ?, ?, 'draft')");
    $stmt->execute([$hotelId, $name, $slug, $isHome ? 1 : 0]);
    $pageId = (int) $pdo->lastInsertId();
    $stmt = $pdo->prepare("INSERT INTO page_versions (page_id, sections, state) VALUES (?, ?, 'draft')");
    $stmt->execute([$pageId, json_encode(['schema_version' => 1, 'sections' => []])]);
    $draftId = (int) $pdo->lastInsertId();
    $pdo->prepare('UPDATE pages SET draft_version_id = ? WHERE id = ?')->execute([$draftId, $pageId]);
    $pdo->commit();

    return $pageId;
}

echo "=== Index is tenant-scoped ===\n";
createPage($pdo, $hotelA, 'My Page', 'my-page-cp6');
createPage($pdo, $hotelB, 'Other Hotel Page', 'other-page-cp6');

$stmt = $pdo->prepare('SELECT name FROM pages WHERE hotel_id = ?');
$stmt->execute([$hotelA]);
$names = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
$names === ['My Page'] ? pass("index query scoped to hotel_id returns only that hotel's pages") : fail('tenant scoping broken: '.implode(',', $names));

echo "\n=== New page starts as draft with a draft version attached ===\n";
$newPageId = createPage($pdo, $hotelA, 'New Page', 'new-page-cp6');
$page = $pdo->query("SELECT status, draft_version_id, published_version_id FROM pages WHERE id={$newPageId}")->fetch(PDO::FETCH_ASSOC);
$page['status'] === 'draft' ? pass('new page has status=draft') : fail('new page status wrong: '.$page['status']);
$page['draft_version_id'] !== null ? pass('new page has a draft_version_id attached') : fail('new page missing draft_version_id');
$page['published_version_id'] === null ? pass('new page has no published_version_id yet') : fail('new page should not have a published version');

echo "\n=== Slug uniqueness is per-hotel, not global ===\n";
createPage($pdo, $hotelB, 'Shared Slug Elsewhere', 'promo-cp6');
// Same slug, different hotel - this INSERT mirrors what the unique rule
// (scoped `where hotel_id`) would ALLOW through to the database.
try {
    createPage($pdo, $hotelA, 'My Promo', 'promo-cp6');
    pass('same slug in a DIFFERENT hotel is allowed (uniqueness correctly scoped per hotel)');
} catch (PDOException $e) {
    fail('same slug in a different hotel was incorrectly rejected: '.$e->getMessage());
}

echo "\n=== Slug uniqueness IS enforced within the same hotel ===\n";
createPage($pdo, $hotelA, 'First', 'duplicate-cp6');
$stmt = $pdo->prepare('SELECT COUNT(*) FROM pages WHERE hotel_id = ? AND slug = ?');
$stmt->execute([$hotelA, 'duplicate-cp6']);
$existingCount = (int) $stmt->fetchColumn();
// This is what StorePageRequest's Rule::unique('pages','slug')->where('hotel_id',...)
// checks BEFORE attempting the insert - simulating that check here since
// the DB itself has no unique index on (hotel_id, slug)... wait, it does:
$existingCount === 1 ? pass('exactly one page with this slug exists before the duplicate attempt (setup correct)') : fail('test setup wrong');

try {
    // pages table already has a UNIQUE KEY on (hotel_id, slug) from Phase 3's
    // migration - so the DB itself is a second line of defense here too,
    // same defense-in-depth pattern as the home_marker constraint.
    $pdo->prepare("INSERT INTO pages (hotel_id, name, slug, is_home, status) VALUES (?, 'Second', 'duplicate-cp6', 0, 'draft')")
        ->execute([$hotelA]);
    fail('duplicate slug within the same hotel was NOT rejected by the database - unique(hotel_id, slug) constraint missing or broken');
} catch (PDOException $e) {
    pass('duplicate slug within the same hotel correctly rejected at the DATABASE level (unique(hotel_id, slug))');
}

echo "\n=== Home page slug normalization ===\n";
$homeId = createPage($pdo, $hotelA, 'Homepage', '', true);
$homePage = $pdo->query("SELECT slug, is_home FROM pages WHERE id={$homeId}")->fetch(PDO::FETCH_ASSOC);
$homePage['slug'] === '' && (int) $homePage['is_home'] === 1 ? pass('home page has empty slug and is_home=1') : fail('home page fields wrong');

echo "\n".($failures === 0 ? 'ALL PAGES ADMIN UI BACKEND CHECKS PASSED' : "{$failures} CHECK(S) FAILED")."\n";
exit($failures === 0 ? 0 : 1);
