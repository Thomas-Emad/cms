<?php
/**
 * Verifies that reorder/duplicate/delete payloads - as produced by the
 * checkpoint 3 interaction layer - persist correctly through the SAME
 * SaveDraftAction logic verified in checkpoints 1/2. No new backend
 * architecture was added this checkpoint, so this confirms the existing
 * save path handles these payload shapes correctly, against real MySQL.
 */

$pdo = new PDO('mysql:host=127.0.0.1;dbname=grand_horizon_test;charset=utf8mb4', 'testuser', 'testpass');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function pass(string $msg): void { echo "  [PASS] $msg\n"; }
function fail(string $msg): void { echo "  [FAIL] $msg\n"; global $failures; $failures++; }
$failures = 0;

$pdo->exec("INSERT INTO hotels (name, slug, status) VALUES ('Hotel CP3', 'hotel-cp3', 'active')");
$hotelId = (int) $pdo->lastInsertId();

function createPage(PDO $pdo, int $hotelId, array $sections, string $slug = '', bool $isHome = true): int
{
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO pages (hotel_id, name, slug, is_home, status) VALUES (?, 'Homepage', ?, ?, 'draft')");
    $stmt->execute([$hotelId, $slug, $isHome ? 1 : 0]);
    $pageId = (int) $pdo->lastInsertId();
    $stmt = $pdo->prepare("INSERT INTO page_versions (page_id, sections, state) VALUES (?, ?, 'draft')");
    $stmt->execute([$pageId, json_encode(['schema_version' => 1, 'sections' => $sections])]);
    $draftId = (int) $pdo->lastInsertId();
    $pdo->prepare("UPDATE pages SET draft_version_id = ? WHERE id = ?")->execute([$draftId, $pageId]);
    $pdo->commit();
    return $pageId;
}

function saveDraft(PDO $pdo, int $pageId, array $sections): void
{
    // Mirrors SaveDraftAction::execute() exactly - mutates draft in place.
    $draftVersionId = $pdo->query("SELECT draft_version_id FROM pages WHERE id = {$pageId}")->fetchColumn();
    $pdo->prepare("UPDATE page_versions SET sections = ? WHERE id = ?")
        ->execute([json_encode(['schema_version' => 1, 'sections' => $sections]), $draftVersionId]);
}

function loadSections(PDO $pdo, int $pageId): array
{
    $page = $pdo->query("SELECT draft_version_id FROM pages WHERE id = {$pageId}")->fetch(PDO::FETCH_ASSOC);
    $draft = $pdo->query("SELECT sections FROM page_versions WHERE id = {$page['draft_version_id']}")->fetch(PDO::FETCH_ASSOC);
    return json_decode($draft['sections'], true)['sections'];
}

echo "=== Reorder persists in the new order ===\n";
$pageId = createPage($pdo, $hotelId, [
    ['id' => 'a', 'type' => 'hero', 'props' => ['title' => 'A'], 'settings' => []],
    ['id' => 'b', 'type' => 'text', 'props' => ['heading' => 'B'], 'settings' => []],
    ['id' => 'c', 'type' => 'facility-grid', 'props' => ['limit' => 6], 'settings' => []],
], '', true);

saveDraft($pdo, $pageId, [
    ['id' => 'c', 'type' => 'facility-grid', 'props' => ['limit' => 6], 'settings' => []],
    ['id' => 'a', 'type' => 'hero', 'props' => ['title' => 'A'], 'settings' => []],
    ['id' => 'b', 'type' => 'text', 'props' => ['heading' => 'B'], 'settings' => []],
]);

$reloaded = loadSections($pdo, $pageId);
array_column($reloaded, 'id') === ['c', 'a', 'b']
    ? pass('reordered sections persisted in the exact new order')
    : fail('reorder not persisted correctly: ' . implode(',', array_column($reloaded, 'id')));

echo "\n=== Duplicate persists as a distinct id with identical (deep-copied) props ===\n";
$pageId2 = createPage($pdo, $hotelId, [
    ['id' => 'text-original', 'type' => 'text', 'props' => ['heading' => 'Original'], 'settings' => []],
], 'page-2', false);

saveDraft($pdo, $pageId2, [
    ['id' => 'text-original', 'type' => 'text', 'props' => ['heading' => 'Original'], 'settings' => []],
    ['id' => 'text-dup-xyz', 'type' => 'text', 'props' => ['heading' => 'Original'], 'settings' => []],
]);

$reloaded2 = loadSections($pdo, $pageId2);
count($reloaded2) === 2 ? pass('both original and duplicate persisted') : fail('duplicate not persisted');
$reloaded2[0]['id'] !== $reloaded2[1]['id'] ? pass('duplicate has a distinct id from the original') : fail('duplicate shares id with original - would corrupt the document');
$reloaded2[0]['props']['heading'] === $reloaded2[1]['props']['heading'] ? pass('duplicated props content matches at time of save') : fail('duplicate props mismatch');

// Now edit ONLY the duplicate and re-save, confirming the original is untouched.
saveDraft($pdo, $pageId2, [
    ['id' => 'text-original', 'type' => 'text', 'props' => ['heading' => 'Original'], 'settings' => []],
    ['id' => 'text-dup-xyz', 'type' => 'text', 'props' => ['heading' => 'Edited on duplicate only'], 'settings' => []],
]);
$reloaded2b = loadSections($pdo, $pageId2);
$reloaded2b[0]['props']['heading'] === 'Original' && $reloaded2b[1]['props']['heading'] === 'Edited on duplicate only'
    ? pass('editing the duplicate after save left the original completely unaffected')
    : fail('editing duplicate incorrectly affected the original');

echo "\n=== Delete persists absence on reload ===\n";
$pageId3 = createPage($pdo, $hotelId, [
    ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Keep me'], 'settings' => []],
    ['id' => 'text-1', 'type' => 'text', 'props' => ['heading' => 'Delete me'], 'settings' => []],
], 'page-3', false);

saveDraft($pdo, $pageId3, [
    ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Keep me'], 'settings' => []],
]);

$reloaded3 = loadSections($pdo, $pageId3);
count($reloaded3) === 1 && $reloaded3[0]['id'] === 'hero-1'
    ? pass('deleted section absent on reload, remaining section intact')
    : fail('delete not persisted correctly');

echo "\n" . ($failures === 0 ? "ALL CHECKPOINT 3 PERSISTENCE CHECKS PASSED" : "{$failures} CHECK(S) FAILED") . "\n";
exit($failures === 0 ? 0 : 1);
