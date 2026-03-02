<?php
/**
 * CMS — Package Features Management
 */

$pageTitle = 'Package Features';
require_once __DIR__ . '/includes/header.php';

$success = '';
$error   = '';

// ---------- Fetch all packages with category names ----------
$packages = [];
try {
    $pkgRes = db_query($conn,
        "SELECT p.id, p.name, pc.name AS category_name
         FROM packages p
         LEFT JOIN package_categories pc ON p.category_id = pc.id
         ORDER BY pc.display_order ASC, p.display_order ASC, p.id ASC"
    );
    while ($p = $pkgRes->fetch_assoc()) { $p['id'] = (int) $p['id']; $packages[] = $p; }
} catch (RuntimeException $e) {
    error_log('[package-features] packages fetch: ' . $e->getMessage());
    $error = 'Could not load packages. Please try again.';
}

$selectedPkgId = (int) ($_GET['package_id'] ?? ($_POST['package_id'] ?? 0));

// ---------- Handle save ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $selectedPkgId = (int) ($_POST['package_id'] ?? 0);
    $rawFeatures   = explode("\n", $_POST['features_text'] ?? '');

    $features = [];
    foreach ($rawFeatures as $line) {
        $line = trim($line);
        if ($line !== '') $features[] = $line;
    }

    if ($selectedPkgId <= 0) {
        $error = 'Please select a package.';
    } else {
        $conn->begin_transaction();
        $del = $conn->prepare("DELETE FROM package_features WHERE package_id = ?");
        $del->bind_param('i', $selectedPkgId);
        if (!$del->execute()) {
            $conn->rollback(); $error = 'Save failed.';
        } else {
            $del->close();
            if (!empty($features)) {
                $ins = $conn->prepare("INSERT INTO package_features (package_id, feature_text, display_order) VALUES (?, ?, ?)");
                $order = 0;
                foreach ($features as $feat) {
                    $ins->bind_param('isi', $selectedPkgId, $feat, $order);
                    if (!$ins->execute()) { $conn->rollback(); $error = 'Save failed: ' . $ins->error; break; }
                    $order++;
                }
                $ins->close();
            }
            if (!$error) { $conn->commit(); $success = 'Features saved successfully.'; }
        }
    }
}

// ---------- Fetch current features ----------
$currentFeatures = [];
if ($selectedPkgId > 0) {
    $fRes = $conn->prepare("SELECT feature_text FROM package_features WHERE package_id = ? ORDER BY display_order ASC, id ASC");
    $fRes->bind_param('i', $selectedPkgId);
    $fRes->execute();
    $fResult = $fRes->get_result();
    while ($row = $fResult->fetch_assoc()) $currentFeatures[] = $row['feature_text'];
    $fRes->close();
}
$featuresText = implode("\n", $currentFeatures);

// Get package name for heading
$selectedPkgName = '';
foreach ($packages as $p) { if ($p['id'] === $selectedPkgId) { $selectedPkgName = $p['name']; break; } }
?>

<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<?php if (empty($packages)): ?>
    <div class="alert alert-error">No packages found. <a href="packages.php">Create one first.</a></div>
<?php else: ?>

<div class="card" style="margin-bottom:16px;">
    <div class="card-body">
        <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <div class="form-group" style="margin-bottom:0;flex:1;min-width:200px;">
                <label for="pkgSelect">Select Package</label>
                <select id="pkgSelect" name="package_id" class="form-control">
                    <option value="">— Choose a package —</option>
                    <?php
                    $lastCat = null;
                    foreach ($packages as $p):
                        $cat = $p['category_name'] ?? 'Uncategorised';
                        if ($cat !== $lastCat) { echo '<optgroup label="' . htmlspecialchars($cat) . '">'; $lastCat = $cat; }
                    ?>
                        <option value="<?= $p['id'] ?>" <?= $p['id'] === $selectedPkgId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">Load</button>
        </form>
    </div>
</div>

<?php if ($selectedPkgId > 0): ?>
<div class="card">
    <div class="card-header">
        <h2>Features for: <?= htmlspecialchars($selectedPkgName) ?></h2>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="package_id" value="<?= $selectedPkgId ?>">
            <div class="form-group">
                <label for="featuresText">Features (one per line)</label>
                <textarea class="form-control" id="featuresText" name="features_text"
                          rows="10" style="font-family:monospace;"
                          placeholder="Guided jungle walk&#10;Organic breakfast&#10;Transport included"
                ><?= htmlspecialchars($featuresText) ?></textarea>
                <small style="color:#6b7280;font-size:.78rem;margin-top:4px;display:block;">
                    Each line = one bullet-point feature. Order is preserved. Blank lines are ignored.
                </small>
            </div>
            <button type="submit" class="btn btn-primary">💾 Save Features</button>
        </form>
    </div>
</div>
<?php else: ?>
    <p style="color:#6b7280;">Select a package above to manage its features.</p>
<?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
