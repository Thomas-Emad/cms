<?php
/**
 * Verifies the draft/publish lifecycle DIRECTLY against real MySQL using
 * the exact same SQL a Laravel/Eloquent implementation of
 * CreatePageAction / SaveDraftAction / PublishPageAction would issue.
 *
 * This sandbox cannot install the Laravel framework itself (composer's
 * packagist.org is not reachable from here - see network allowlist), so
 * the actual PHPUnit suite in tests/Feature/PagePublishLifecycleTest.php
 * cannot be executed end-to-end in this environment. This script exists
 * to independently verify the exact data-level behavior those tests
 * assert, against a REAL MySQL 8.0.46 instance, rather than asserting it
 * by inspection alone.
 */

$pdo = new PDO('mysql:host=127.0.0.1;dbname=grand_horizon_test;charset=utf8mb4', 'testuser', 'testpass');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function pass(string $msg): void { echo "  [PASS] $msg\n"; }
function fail(string $msg): void { echo "  [FAIL] $msg\n"; global $failures; $failures++; }
$failures = 0;

// --- fixture: one hotel ---
$pdo->exec("SET FOREIGN_KEY_CHECKS=0");
$pdo->exec("DELETE FROM page_versions");
$pdo->exec("DELETE FROM pages");
$pdo->exec("DELETE FROM hotels");
$pdo->exec("SET FOREIGN_KEY_CHECKS=1");

$pdo->exec("INSERT INTO hotels (name, slug, status) VALUES ('Grand Horizon', 'grand-horizon-lifecycle', 'active')");
$hotelId = (int) $pdo->lastInsertId();

function createPage(PDO $pdo, int $hotelId, string $name, string $slug, array $sections): array
{
    // Mirrors CreatePageAction::execute() exactly: create Page, create
    // draft PageVersion, set pages.draft_version_id ONCE.
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO pages (hotel_id, name, slug, is_home, status) VALUES (?, ?, ?, 1, 'draft')");
    $stmt->execute([$hotelId, $name, $slug]);
    $pageId = (int) $pdo->lastInsertId();

    $sectionsJson = json_encode(['schema_version' => 1, 'sections' => $sections]);
    $stmt = $pdo->prepare("INSERT INTO page_versions (page_id, sections, state) VALUES (?, ?, 'draft')");
    $stmt->execute([$pageId, $sectionsJson]);
    $draftId = (int) $pdo->lastInsertId();

    $pdo->prepare("UPDATE pages SET draft_version_id = ? WHERE id = ?")->execute([$draftId, $pageId]);

    $pdo->commit();

    return ['page_id' => $pageId, 'draft_id' => $draftId];
}

function saveDraft(PDO $pdo, int $pageId, array $sections): void
{
    // Mirrors SaveDraftAction::execute(): mutates the EXISTING draft row
    // in place. Never creates a new PageVersion.
    $sectionsJson = json_encode(['schema_version' => 1, 'sections' => $sections]);
    $draftVersionId = $pdo->query("SELECT draft_version_id FROM pages WHERE id = {$pageId}")->fetchColumn();
    $pdo->prepare("UPDATE page_versions SET sections = ? WHERE id = ?")->execute([$sectionsJson, $draftVersionId]);
}

function publishPage(PDO $pdo, int $pageId): int
{
    // Mirrors PublishPageAction::execute() exactly: snapshot draft's
    // current sections into a NEW published row, then flip the pointer -
    // both inside one transaction.
    $pdo->beginTransaction();

    $page = $pdo->query("SELECT draft_version_id FROM pages WHERE id = {$pageId}")->fetch(PDO::FETCH_ASSOC);
    $draft = $pdo->query("SELECT sections FROM page_versions WHERE id = {$page['draft_version_id']}")->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("INSERT INTO page_versions (page_id, sections, state, published_at) VALUES (?, ?, 'published', NOW())");
    $stmt->execute([$pageId, $draft['sections']]);
    $publishedId = (int) $pdo->lastInsertId();

    $pdo->prepare("UPDATE pages SET published_version_id = ?, status = 'published' WHERE id = ?")
        ->execute([$publishedId, $pageId]);

    $pdo->commit();

    return $publishedId;
}

function getPage(PDO $pdo, int $pageId): array
{
    return $pdo->query("SELECT * FROM pages WHERE id = {$pageId}")->fetch(PDO::FETCH_ASSOC);
}

function getVersionTitle(PDO $pdo, int $versionId): string
{
    $row = $pdo->query("SELECT sections FROM page_versions WHERE id = {$versionId}")->fetch(PDO::FETCH_ASSOC);
    $decoded = json_decode($row['sections'], true);
    return $decoded['sections'][0]['props']['title'];
}

echo "=== Test 1: first_publish_creates_a_published_version_and_sets_the_pointer ===\n";
$p = createPage($pdo, $hotelId, 'Homepage', '', [
    ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Welcome'], 'settings' => []],
]);
$page = getPage($pdo, $p['page_id']);
$page['published_version_id'] === null ? pass('published_version_id starts null') : fail('published_version_id should start null');
$page['draft_version_id'] !== null ? pass('draft_version_id is set at creation') : fail('draft_version_id should be set');

$publishedId = publishPage($pdo, $p['page_id']);
$page = getPage($pdo, $p['page_id']);
$page['published_version_id'] == $publishedId ? pass('published_version_id now points at the new version') : fail('pointer not updated');
$page['status'] === 'published' ? pass('page status flipped to published') : fail('status not updated');
getVersionTitle($pdo, $publishedId) === 'Welcome' ? pass('published version has correct content') : fail('published content mismatch');

echo "\n=== Test 2: editing_the_draft_after_publish_does_not_affect_the_published_version ===\n";
$draftIdBefore = getPage($pdo, $p['page_id'])['draft_version_id'];
saveDraft($pdo, $p['page_id'], [
    ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Version B (unpublished)'], 'settings' => []],
]);
$page = getPage($pdo, $p['page_id']);
$page['draft_version_id'] == $draftIdBefore ? pass('draft row identity unchanged (mutated in place)') : fail('draft row was replaced, should be mutated in place');
getVersionTitle($pdo, $publishedId) === 'Welcome' ? pass('published version STILL unaffected by draft edit') : fail('published version leaked draft changes!');
$page['published_version_id'] == $publishedId ? pass('published pointer unchanged') : fail('published pointer moved without a publish action');

echo "\n=== Test 3: second_publish_creates_a_new_version_row_and_moves_the_pointer ===\n";
$publishedId2 = publishPage($pdo, $p['page_id']);
$publishedId2 !== $publishedId ? pass('second publish created a genuinely NEW row') : fail('second publish reused the old row id');
$page = getPage($pdo, $p['page_id']);
$page['published_version_id'] == $publishedId2 ? pass('pointer moved to the second published version') : fail('pointer did not move');
getVersionTitle($pdo, $publishedId) === 'Welcome' ? pass('first published version (id=' . $publishedId . ') still intact as revision history') : fail('first published version was mutated');
getVersionTitle($pdo, $publishedId2) === 'Version B (unpublished)' ? pass('second published version has the new content') : fail('second published content wrong');
$publishedCount = (int) $pdo->query("SELECT COUNT(*) FROM page_versions WHERE page_id = {$p['page_id']} AND state='published'")->fetchColumn();
$publishedCount === 2 ? pass('exactly 2 published rows exist (append-only revision log)') : fail("expected 2 published rows, got {$publishedCount}");

echo "\n=== Test 4: draft_changes_never_affect_the_live_page_before_publish ===\n";
saveDraft($pdo, $p['page_id'], [
    ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Yet another unpublished edit'], 'settings' => []],
]);
// Simulate "guest reads published_version_id" (exactly what
// Guest\PageController does) rather than draft_version_id.
$page = getPage($pdo, $p['page_id']);
getVersionTitle($pdo, $page['published_version_id']) === 'Version B (unpublished)'
    ? pass('guest-visible (published) content unaffected by the latest unpublished draft edit')
    : fail('guest-visible content leaked an unpublished draft edit');

echo "\n=== Test 5: append-only integrity across the whole sequence ===\n";
$allVersions = $pdo->query("SELECT id, state FROM page_versions WHERE page_id = {$p['page_id']} ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$states = array_column($allVersions, 'state');
$states === ['draft', 'published', 'published']
    ? pass('exactly one draft row + two published rows total, in creation order - nothing was deleted or overwritten')
    : fail('unexpected version row states: ' . implode(',', $states));

echo "\n" . ($failures === 0 ? "ALL LIFECYCLE CHECKS PASSED" : "{$failures} CHECK(S) FAILED") . "\n";
exit($failures === 0 ? 0 : 1);
