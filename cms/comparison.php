<?php
/**
 * CMS — Comparison Table Management
 * Matrix editor: rows = comparison features, columns = packages
 */

$pageTitle = 'Comparison Table';
require_once __DIR__ . '/includes/header.php';

$success = '';
$error   = '';

// ---------- Fetch packages ----------
$pkgRes  = $conn->query("SELECT id, name FROM packages ORDER BY id ASC");
$packages = [];
while ($p = $pkgRes->fetch_assoc()) {
    $p['id'] = (int) $p['id'];
    $packages[] = $p;
}

// ---------- Handle POST (save) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $featureNames = $_POST['feature_name'] ?? [];
    $conn->begin_transaction();

    // Wipe
    if (!$conn->query("DELETE FROM comparison_values") || !$conn->query("DELETE FROM comparison_features")) {
        $conn->rollback();
        $error = 'Save failed: ' . $conn->error;
    } else {
        $insFeat = $conn->prepare("INSERT INTO comparison_features (feature) VALUES (?)");
        $insVal  = $conn->prepare("INSERT INTO comparison_values (comparison_feature_id, package_id, type, text) VALUES (?, ?, ?, ?)");
        $allowed = ['yes', 'no', 'text'];

        foreach ($featureNames as $fi => $featName) {
            $featName = trim($featName);
            if ($featName === '') continue;
            $insFeat->bind_param('s', $featName);
            if (!$insFeat->execute()) {
                $conn->rollback();
                $error = 'Save failed.';
                break;
            }
            $featId = (int) $conn->insert_id;

            foreach ($packages as $pkg) {
                $pkgId = $pkg['id'];
                $type  = $_POST['cv_type'][$fi][$pkgId] ?? 'no';
                $text  = trim($_POST['cv_text'][$fi][$pkgId] ?? '');
                if (!in_array($type, $allowed, true)) $type = 'no';
                $insVal->bind_param('iiss', $featId, $pkgId, $type, $text);
                if (!$insVal->execute()) {
                    $conn->rollback();
                    $error = 'Save failed.';
                    break 2;
                }
            }
        }

        if (!$error) {
            $conn->commit();
            $success = 'Comparison table saved.';
        }
        $insFeat->close();
        $insVal->close();
    }
}

// ---------- Fetch existing features + values ----------
$features = [];
$featRes  = $conn->query("SELECT id, feature FROM comparison_features ORDER BY id ASC");
while ($f = $featRes->fetch_assoc()) {
    $f['id'] = (int) $f['id'];
    $f['values'] = [];
    $features[] = $f;
}

$valMatrix = [];  // [feature_index][package_id] = ['type'=>..,'text'=>..]
if (!empty($features)) {
    $valRes = $conn->query("SELECT comparison_feature_id, package_id, type, text FROM comparison_values");
    $featIdxMap = [];
    foreach ($features as $idx => $f) $featIdxMap[$f['id']] = $idx;
    while ($v = $valRes->fetch_assoc()) {
        $fIdx = $featIdxMap[(int)$v['comparison_feature_id']] ?? null;
        if ($fIdx !== null) {
            $valMatrix[$fIdx][(int)$v['package_id']] = ['type' => $v['type'], 'text' => $v['text']];
        }
    }
}
?>

<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<?php if (empty($packages)): ?>
    <div class="alert alert-error">No packages found. <a href="packages.php">Create packages first.</a></div>
<?php else: ?>

<div class="card">
    <div class="card-header">
        <h2>Comparison Matrix</h2>
        <button type="button" class="btn btn-secondary btn-sm" onclick="addRow()">+ Add Row</button>
    </div>
    <div class="card-body" style="overflow-x:auto;">
        <form method="POST" id="compForm">
            <input type="hidden" name="action" value="save">

            <table id="compTable" style="min-width:100%;">
                <thead>
                    <tr>
                        <th style="min-width:200px;">Feature</th>
                        <?php foreach ($packages as $p): ?>
                            <th style="min-width:140px;"><?= htmlspecialchars($p['name']) ?></th>
                        <?php endforeach; ?>
                        <th style="width:48px;"></th>
                    </tr>
                </thead>
                <tbody id="compBody">
                <?php foreach ($features as $fi => $feat): ?>
                    <?php
                    $rowVals = $valMatrix[$fi] ?? [];
                    $this_feat_name = htmlspecialchars($feat['feature']);
                    ?>
                    <tr class="comp-row">
                        <td>
                            <input type="text" name="feature_name[<?= $fi ?>]"
                                   class="form-control" value="<?= $this_feat_name ?>"
                                   placeholder="e.g. Organic Meals" required>
                        </td>
                        <?php foreach ($packages as $p): ?>
                            <?php
                            $val  = $rowVals[$p['id']] ?? ['type' => 'no', 'text' => ''];
                            $type = $val['type'];
                            $txt  = htmlspecialchars($val['text'] ?? '');
                            ?>
                            <td>
                                <select name="cv_type[<?= $fi ?>][<?= $p['id'] ?>]"
                                        class="form-control type-select" style="margin-bottom:4px;"
                                        onchange="toggleText(this)">
                                    <option value="yes"  <?= $type==='yes'  ? 'selected' : '' ?>>✅ Yes</option>
                                    <option value="no"   <?= $type==='no'   ? 'selected' : '' ?>>❌ No</option>
                                    <option value="text" <?= $type==='text' ? 'selected' : '' ?>>📝 Text</option>
                                </select>
                                <input type="text" name="cv_text[<?= $fi ?>][<?= $p['id'] ?>]"
                                       class="form-control text-input"
                                       style="display:<?= $type==='text'?'block':'none' ?>;"
                                       value="<?= $txt ?>"
                                       placeholder="Custom text">
                            </td>
                        <?php endforeach; ?>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)" title="Remove row">✕</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top:16px;">
                <button type="submit" class="btn btn-primary">💾 Save Comparison Table</button>
            </div>
        </form>
    </div>
</div>

<script>
var rowCount = <?= count($features) ?>;
var packageIds = <?= json_encode(array_column($packages, 'id')) ?>;
var packageNames = <?= json_encode(array_column($packages, 'name')) ?>;

function toggleText(select) {
    var row = select.closest('td');
    var textInput = row.querySelector('.text-input');
    textInput.style.display = select.value === 'text' ? 'block' : 'none';
}

function addRow() {
    var fi = rowCount++;
    var tbody = document.getElementById('compBody');
    var tr = document.createElement('tr');
    tr.className = 'comp-row';

    var td1 = '<td><input type="text" name="feature_name[' + fi + ']" class="form-control" placeholder="e.g. Organic Meals" required></td>';
    var tds = '';
    for (var i = 0; i < packageIds.length; i++) {
        var pid = packageIds[i];
        tds += '<td>' +
            '<select name="cv_type[' + fi + '][' + pid + ']" class="form-control type-select" style="margin-bottom:4px;" onchange="toggleText(this)">' +
                '<option value="yes">✅ Yes</option>' +
                '<option value="no" selected>❌ No</option>' +
                '<option value="text">📝 Text</option>' +
            '</select>' +
            '<input type="text" name="cv_text[' + fi + '][' + pid + ']" class="form-control text-input" style="display:none;" placeholder="Custom text">' +
        '</td>';
    }
    var tdDel = '<td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)" title="Remove row">✕</button></td>';
    tr.innerHTML = td1 + tds + tdDel;
    tbody.appendChild(tr);
}

function removeRow(btn) {
    if (!confirmDelete('Remove this comparison row?')) return;
    btn.closest('tr').remove();
}
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
