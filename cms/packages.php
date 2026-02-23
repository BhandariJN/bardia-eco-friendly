<?php
/**
 * CMS — Packages Management (with Category support)
 */

$pageTitle = 'Packages';
require_once __DIR__ . '/includes/header.php';

$allowedCurrencies = ['₹', '$', '€', '£'];
$success = '';
$error   = '';

// ---------- Fetch categories for dropdown ----------
$catRes = $conn->query("SELECT id, name FROM package_categories WHERE is_active = 1 ORDER BY display_order ASC, id ASC");
$pkgCategories = [];
while ($c = $catRes->fetch_assoc()) { $c['id'] = (int) $c['id']; $pkgCategories[] = $c; }

// ---------- Handle POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM packages WHERE id = ?");
        $stmt->bind_param('i', $id);
        $success = $stmt->execute() ? 'Package deleted.' : '';
        if (!$success) $error = 'Delete failed: ' . $stmt->error;
        $stmt->close();

    } elseif (in_array($action, ['create', 'update'], true)) {
        $id           = (int)    ($_POST['id']           ?? 0);
        $categoryId   = (int)    ($_POST['category_id']  ?? 0);
        $name         = trim($_POST['name']          ?? '');
        $icon         = trim($_POST['icon']          ?? '');
        $duration     = trim($_POST['duration']      ?? '');
        $price        = (float)  ($_POST['price']        ?? 0);
        $currency     =           $_POST['currency']     ?? '₹';
        $priceNote    = trim($_POST['price_note']    ?? '');
        $description  = trim($_POST['description']   ?? '');
        $isFeatured   = isset($_POST['is_featured'])  ? 1 : 0;
        $displayOrder = (int)    ($_POST['display_order'] ?? 0);
        $isActive     = isset($_POST['is_active'])    ? 1 : 0;

        if ($categoryId <= 0)                               { $error = 'Please select a category.'; }
        elseif (empty($name))                               { $error = 'Package name is required.'; }
        elseif ($price <= 0)                                { $error = 'Price must be greater than 0.'; }
        elseif (!in_array($currency, $allowedCurrencies, true)) { $error = 'Invalid currency.'; }
        else {
            if ($action === 'create') {
                $stmt = $conn->prepare(
                    "INSERT INTO packages (category_id, icon, name, duration, price, currency, price_note, description, is_featured, display_order, is_active)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param('isssdsssiii', $categoryId, $icon, $name, $duration, $price, $currency, $priceNote, $description, $isFeatured, $displayOrder, $isActive);
                $success = $stmt->execute() ? 'Package created.' : '';
                if (!$success) $error = 'Create failed: ' . $stmt->error;
                $stmt->close();
            } else {
                if ($id <= 0) { $error = 'Invalid ID.'; }
                else {
                    $stmt = $conn->prepare(
                        "UPDATE packages SET category_id=?, icon=?, name=?, duration=?, price=?, currency=?, price_note=?, description=?, is_featured=?, display_order=?, is_active=? WHERE id=?"
                    );
                    $stmt->bind_param('isssdsssiiii', $categoryId, $icon, $name, $duration, $price, $currency, $priceNote, $description, $isFeatured, $displayOrder, $isActive, $id);
                    $success = $stmt->execute() ? 'Package updated.' : '';
                    if (!$success) $error = 'Update failed: ' . $stmt->error;
                    $stmt->close();
                }
            }
        }
    }
}

// ---------- Fetch packages ----------
$packages = [];
$res = $conn->query(
    "SELECT p.id, p.category_id, p.icon, p.name, p.duration, p.price, p.currency, p.price_note, p.description, p.is_featured, p.display_order, p.is_active, pc.name AS category_name
     FROM packages p LEFT JOIN package_categories pc ON p.category_id = pc.id
     ORDER BY pc.display_order ASC, p.display_order ASC, p.id ASC"
);
while ($r = $res->fetch_assoc()) {
    $r['id'] = (int) $r['id']; $r['category_id'] = (int) $r['category_id'];
    $r['price'] = (float) $r['price']; $r['is_featured'] = (bool) $r['is_featured'];
    $r['display_order'] = (int) $r['display_order']; $r['is_active'] = (bool) $r['is_active'];
    $packages[] = $r;
}

$currencyOptions = ['₹' => '₹ (NPR)', '$' => '$ (USD)', '€' => '€ (EUR)', '£' => '£ (GBP)'];
?>

<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<?php if (empty($pkgCategories)): ?>
    <div class="alert alert-error">No categories found. <a href="package-categories.php">Create a category first.</a></div>
<?php else: ?>

<div style="margin-bottom:16px;">
    <button class="btn btn-primary" onclick="openModal()">+ Add Package</button>
</div>

<div class="card">
    <div class="tbl-wrap">
        <table>
            <thead><tr><th>Icon</th><th>Name</th><th>Category</th><th>Duration</th><th>Price</th><th>Active</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($packages as $p): ?>
                <tr>
                    <td style="font-size:1.4rem;"><?= htmlspecialchars($p['icon']) ?></td>
                    <td>
                        <strong><?= htmlspecialchars($p['name']) ?></strong>
                        <?php if ($p['is_featured']): ?>&nbsp;<span class="badge badge-gold">★</span><?php endif; ?>
                        <?php if ($p['price_note']): ?><br><small style="color:#6b7280;"><?= htmlspecialchars($p['price_note']) ?></small><?php endif; ?>
                    </td>
                    <td><small><?= htmlspecialchars($p['category_name'] ?? '—') ?></small></td>
                    <td><?= htmlspecialchars($p['duration']) ?></td>
                    <td><?= htmlspecialchars($p['currency']) ?> <?= number_format($p['price'], 2) ?></td>
                    <td><?= $p['is_active'] ? '<span class="badge badge-green">Active</span>' : '<span class="badge badge-red">Off</span>' ?></td>
                    <td>
                        <button class="btn btn-secondary btn-sm" onclick='openModal(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)'>Edit</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirmDelete('Delete package &quot;<?= htmlspecialchars(addslashes($p['name'])) ?>&quot;?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($packages)): ?>
                <tr><td colspan="7" style="color:#6b7280;text-align:center;">No packages yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="m-cards">
    <?php foreach ($packages as $p): ?>
    <div class="m-card">
        <div class="m-title"><?= htmlspecialchars($p['icon']) ?> <?= htmlspecialchars($p['name']) ?></div>
        <div class="m-row"><span>Category</span><span><?= htmlspecialchars($p['category_name'] ?? '—') ?></span></div>
        <div class="m-row"><span>Price</span><span><?= htmlspecialchars($p['currency']) ?> <?= number_format($p['price'], 2) ?></span></div>
        <div class="m-row"><span>Duration</span><span><?= htmlspecialchars($p['duration']) ?></span></div>
        <div class="m-row"><span>Active</span><span><?= $p['is_active'] ? 'Yes' : 'No' ?></span></div>
        <div class="m-actions">
            <button class="btn btn-secondary btn-sm" onclick='openModal(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)'>Edit</button>
            <form method="POST" onsubmit="return confirmDelete()">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Add/Edit Modal -->
<div class="modal-backdrop" id="pkgModal">
    <div class="modal">
        <h3 id="modalTitle">Add Package</h3>
        <form method="POST" id="pkgForm">
            <input type="hidden" name="action" id="fAction" value="create">
            <input type="hidden" name="id" id="fId" value="">
            <div class="form-group">
                <label for="fCat">Category <span style="color:red">*</span></label>
                <select class="form-control" id="fCat" name="category_id" required>
                    <option value="">— Select category —</option>
                    <?php foreach ($pkgCategories as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group" style="flex:0 0 80px;">
                    <label for="fIcon">Icon</label>
                    <input type="text" class="form-control" id="fIcon" name="icon" maxlength="5" placeholder="🌿">
                </div>
                <div class="form-group">
                    <label for="fName">Name <span style="color:red">*</span></label>
                    <input type="text" class="form-control" id="fName" name="name" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="fDuration">Duration</label>
                    <input type="text" class="form-control" id="fDuration" name="duration" placeholder="2 Nights · 3 Days">
                </div>
                <div class="form-group">
                    <label for="fPriceNote">Price Note</label>
                    <input type="text" class="form-control" id="fPriceNote" name="price_note" placeholder="Twin sharing">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="fPrice">Price <span style="color:red">*</span></label>
                    <input type="number" class="form-control" id="fPrice" name="price" min="0.01" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="fCurrency">Currency</label>
                    <select class="form-control" id="fCurrency" name="currency">
                        <?php foreach ($currencyOptions as $val => $label): ?>
                            <option value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fOrder">Order</label>
                    <input type="number" class="form-control" id="fOrder" name="display_order" value="0" min="0">
                </div>
            </div>
            <div class="form-group">
                <label for="fDesc">Description</label>
                <textarea class="form-control" id="fDesc" name="description" rows="3"></textarea>
            </div>
            <div style="display:flex;gap:20px;flex-wrap:wrap;">
                <div class="check-group">
                    <input type="checkbox" id="fFeatured" name="is_featured" value="1">
                    <label for="fFeatured">Mark as Featured</label>
                </div>
                <div class="check-group">
                    <input type="checkbox" id="fActive" name="is_active" value="1" checked>
                    <label for="fActive">Active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">Save Package</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(pkg) {
    document.getElementById('pkgModal').classList.add('active');
    if (pkg) {
        document.getElementById('modalTitle').textContent = 'Edit Package';
        document.getElementById('fAction').value    = 'update';
        document.getElementById('fId').value         = pkg.id;
        document.getElementById('fCat').value        = pkg.category_id;
        document.getElementById('fIcon').value       = pkg.icon || '';
        document.getElementById('fName').value       = pkg.name || '';
        document.getElementById('fDuration').value   = pkg.duration || '';
        document.getElementById('fPrice').value      = pkg.price || '';
        document.getElementById('fCurrency').value   = pkg.currency || '₹';
        document.getElementById('fPriceNote').value  = pkg.price_note || '';
        document.getElementById('fDesc').value       = pkg.description || '';
        document.getElementById('fOrder').value      = pkg.display_order || 0;
        document.getElementById('fFeatured').checked = !!pkg.is_featured;
        document.getElementById('fActive').checked   = !!pkg.is_active;
        document.getElementById('submitBtn').textContent = 'Update Package';
    } else {
        document.getElementById('modalTitle').textContent = 'Add Package';
        document.getElementById('fAction').value = 'create';
        document.getElementById('pkgForm').reset();
        document.getElementById('fActive').checked = true;
        document.getElementById('submitBtn').textContent = 'Save Package';
    }
}
function closeModal() {
    document.getElementById('pkgModal').classList.remove('active');
    document.getElementById('pkgForm').reset();
}
document.getElementById('pkgModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
