<?php

/**
 * Same approach as checkpoint 1's verify_*.php scripts: replicate the
 * exact SQL/logic the real Admin\PageController methods would issue,
 * against real MySQL, since the full Laravel/Inertia HTTP stack can't be
 * installed in this sandbox (no Composer/Packagist access).
 *
 * This does NOT execute BuilderPlumbingTest.php itself - it independently
 * verifies the same underlying behaviors that test file asserts.
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

$pdo->exec("INSERT INTO hotels (name, slug, status) VALUES ('Hotel A', 'hotel-a-cp2', 'active')");
$hotelA = (int) $pdo->lastInsertId();
$pdo->exec("INSERT INTO hotels (name, slug, status) VALUES ('Hotel B', 'hotel-b-cp2', 'active')");
$hotelB = (int) $pdo->lastInsertId();

$pdo->prepare("INSERT INTO users (hotel_id, role, status, name, email, password) VALUES (?, 'hotel_admin', 'active', 'Admin A', 'admin-a@example.com', 'x')")->execute([$hotelA]);
$adminA = (int) $pdo->lastInsertId();

function createPage(PDO $pdo, int $hotelId, array $sections, string $slug = '', bool $isHome = true): int
{
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO pages (hotel_id, name, slug, is_home, status) VALUES (?, 'Homepage', ?, ?, 'draft')");
    $stmt->execute([$hotelId, $slug, $isHome ? 1 : 0]);
    $pageId = (int) $pdo->lastInsertId();
    $stmt = $pdo->prepare("INSERT INTO page_versions (page_id, sections, state) VALUES (?, ?, 'draft')");
    $stmt->execute([$pageId, json_encode(['schema_version' => 1, 'sections' => $sections])]);
    $draftId = (int) $pdo->lastInsertId();
    $pdo->prepare('UPDATE pages SET draft_version_id = ? WHERE id = ?')->execute([$draftId, $pageId]);
    $pdo->commit();

    return $pageId;
}

// Mirrors Admin\PageController::edit()'s data shaping exactly.
function loadBuilderData(PDO $pdo, int $pageId): array
{
    $page = $pdo->query("SELECT * FROM pages WHERE id = {$pageId}")->fetch(PDO::FETCH_ASSOC);
    $draft = $pdo->query("SELECT sections FROM page_versions WHERE id = {$page['draft_version_id']}")->fetch(PDO::FETCH_ASSOC);
    $decoded = json_decode($draft['sections'], true);

    return ['page' => $page, 'sections' => $decoded['sections']];
}

// Mirrors PagePolicy::update()'s tenant check exactly.
function canUpdate(PDO $pdo, int $userId, int $pageId): bool
{
    $user = $pdo->query("SELECT hotel_id, role FROM users WHERE id = {$userId}")->fetch(PDO::FETCH_ASSOC);
    $page = $pdo->query("SELECT hotel_id FROM pages WHERE id = {$pageId}")->fetch(PDO::FETCH_ASSOC);
    if ($user['role'] === 'super_admin') {
        return true;
    }

    return $user['hotel_id'] !== null && (int) $user['hotel_id'] === (int) $page['hotel_id']
        && in_array($user['role'], ['hotel_admin', 'hotel_staff'], true);
}

echo "=== Builder loads the DRAFT, never the published version ===\n";
$pageId = createPage($pdo, $hotelA, [['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Draft Title'], 'settings' => []]]);
$loaded = loadBuilderData($pdo, $pageId);
$loaded['sections'][0]['props']['title'] === 'Draft Title'
    ? pass('Builder correctly loaded the draft version')
    : fail('Builder did not load draft content correctly');

// Simulate publish + a subsequent draft edit, per BuilderPlumbingTest's
// "never loads published version even after publishing" scenario.
$pdo->beginTransaction();
$draftRow = $pdo->query("SELECT draft_version_id, sections FROM pages p JOIN page_versions pv ON pv.id = p.draft_version_id WHERE p.id = {$pageId}")->fetch(PDO::FETCH_ASSOC);
$stmt = $pdo->prepare("INSERT INTO page_versions (page_id, sections, state, published_at) VALUES (?, ?, 'published', NOW())");
$stmt->execute([$pageId, $draftRow['sections']]);
$publishedId = (int) $pdo->lastInsertId();
$pdo->prepare("UPDATE pages SET published_version_id = ?, status = 'published' WHERE id = ?")->execute([$publishedId, $pageId]);
$pdo->commit();

$pdo->prepare('UPDATE page_versions SET sections = ? WHERE id = ?')
    ->execute([json_encode(['schema_version' => 1, 'sections' => [['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Newer Draft Title'], 'settings' => []]]]), $draftRow['draft_version_id']]);

$loadedAfterPublish = loadBuilderData($pdo, $pageId);
$loadedAfterPublish['sections'][0]['props']['title'] === 'Newer Draft Title'
    ? pass('Builder shows the latest draft edit, not the published snapshot, even after a publish happened')
    : fail('Builder incorrectly showed stale/published content instead of the current draft');

echo "\n=== Adding/saving sections preserves valid JSON on reload ===\n";
$pageId2 = createPage($pdo, $hotelA, [], 'page-2-cp2', false);
$multiSections = [
    ['id' => 'hero-1', 'type' => 'hero', 'props' => ['title' => 'Welcome'], 'settings' => ['padding' => 'large']],
    ['id' => 'text-1', 'type' => 'text', 'props' => ['heading' => 'About', 'body' => 'Some text.'], 'settings' => []],
    ['id' => 'fg-1', 'type' => 'facility-grid', 'props' => ['category' => 'wellness', 'featured_only' => true, 'limit' => 4, 'columns' => 3], 'settings' => []],
];
$draftId2 = $pdo->query("SELECT draft_version_id FROM pages WHERE id = {$pageId2}")->fetchColumn();
$pdo->prepare('UPDATE page_versions SET sections = ? WHERE id = ?')->execute([json_encode(['schema_version' => 1, 'sections' => $multiSections]), $draftId2]);

$reloaded = loadBuilderData($pdo, $pageId2);
count($reloaded['sections']) === 3 ? pass('all 3 sections preserved on reload') : fail('section count mismatch: '.count($reloaded['sections']));
$reloaded['sections'][2]['props']['limit'] === 4 ? pass('nested prop values (limit=4) preserved exactly') : fail('nested prop value corrupted');
$reloaded['sections'][2]['props']['featured_only'] === true ? pass('boolean prop (featured_only) preserved as boolean, not stringified') : fail('boolean prop type corrupted');

echo "\n=== Tenant isolation: Hotel A's admin cannot act on Hotel B's page ===\n";
$otherPageId = createPage($pdo, $hotelB, [], 'page-3-cp2', false);
canUpdate($pdo, $adminA, $pageId2) ? pass("Hotel A admin CAN update Hotel A's own page") : fail('incorrectly denied own-hotel access');
! canUpdate($pdo, $adminA, $otherPageId) ? pass("Hotel A admin CANNOT update Hotel B's page") : fail('CROSS-TENANT ACCESS ALLOWED - this would be a serious bug');

echo "\n=== Preview resolves facility-grid using latest SAVED props, not stale data ===\n";
$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
$pdo->exec('DELETE FROM facilities');
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');
$pdo->prepare("INSERT INTO facilities (hotel_id, name, slug, category, status) VALUES (?, 'Wellness Spa', 'wellness-spa-cp2', 'wellness', 'published')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO facilities (hotel_id, name, slug, category, status) VALUES (?, 'Fitness Gym', 'fitness-gym-cp2', 'fitness', 'published')")->execute([$hotelA]);
$pdo->prepare("INSERT INTO facilities (hotel_id, name, slug, category, status) VALUES (?, 'Other Hotel Spa', 'other-hotel-spa-cp2', 'wellness', 'published')")->execute([$hotelB]);

function resolveFacilityGridForPage(PDO $pdo, int $pageId, int $hotelId): array
{
    $draft = $pdo->query("SELECT pv.sections FROM pages p JOIN page_versions pv ON pv.id = p.draft_version_id WHERE p.id = {$pageId}")->fetch(PDO::FETCH_ASSOC);
    $sections = json_decode($draft['sections'], true)['sections'];
    $fgProps = $sections[0]['props'];

    $sql = "SELECT name FROM facilities WHERE hotel_id = ? AND status = 'published'";
    $params = [$hotelId];
    if (! empty($fgProps['category'])) {
        $sql .= ' AND category = ?';
        $params[] = $fgProps['category'];
    }
    $sql .= ' LIMIT '.(int) ($fgProps['limit'] ?? 6);

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
}

$previewPageId = createPage($pdo, $hotelA, [['id' => 'fg-1', 'type' => 'facility-grid', 'props' => ['category' => 'wellness', 'limit' => 10], 'settings' => []]], 'page-4-cp2', false);
$firstPreview = resolveFacilityGridForPage($pdo, $previewPageId, $hotelA);
$firstPreview === ['Wellness Spa'] ? pass('first preview correctly resolves wellness facility only') : fail('first preview wrong: '.implode(',', $firstPreview));

$draftIdP = $pdo->query("SELECT draft_version_id FROM pages WHERE id = {$previewPageId}")->fetchColumn();
$pdo->prepare('UPDATE page_versions SET sections = ? WHERE id = ?')->execute([
    json_encode(['schema_version' => 1, 'sections' => [['id' => 'fg-1', 'type' => 'facility-grid', 'props' => ['category' => 'fitness', 'limit' => 10], 'settings' => []]]]),
    $draftIdP,
]);
$secondPreview = resolveFacilityGridForPage($pdo, $previewPageId, $hotelA);
$secondPreview === ['Fitness Gym'] ? pass('after editing+saving, preview reflects the NEW category (fitness), not the stale wellness result') : fail('preview showed stale data: '.implode(',', $secondPreview));

echo "\n".($failures === 0 ? 'ALL CHECKPOINT 2 PLUMBING CHECKS PASSED' : "{$failures} CHECK(S) FAILED")."\n";
exit($failures === 0 ? 0 : 1);
