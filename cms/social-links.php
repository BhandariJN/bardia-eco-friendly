<?php
/**
 * CMS — Social Links Management
 */

$pageTitle = 'Social Links';
require_once __DIR__ . '/includes/header.php';

$success = '';
$error   = '';

// ---------- Handle POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id   = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM social_links WHERE id = ?");
        $stmt->bind_param('i', $id);
        $success = $stmt->execute() ? 'Social link deleted.' : '';
        if (!$success) $error = 'Delete failed: ' . $stmt->error;
        $stmt->close();

    } elseif (in_array($action, ['create', 'update'], true)) {
        $id           = (int)   ($_POST['id']            ?? 0);
        $iconName     = trim($_POST['icon_name']     ?? '');
        $label        = trim($_POST['label']        ?? '');
        $href         = trim($_POST['href']         ?? '');
        $displayOrder = (int)   ($_POST['display_order'] ?? 0);
        $isActive     = isset($_POST['is_active']) ? 1 : 0;

        if (empty($label)) { $error = 'Label is required.'; }
        elseif (empty($href)) { $error = 'URL (href) is required.'; }
        else {
            if ($action === 'create') {
                $stmt = $conn->prepare(
                    "INSERT INTO social_links (icon_name, label, href, display_order, is_active) VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->bind_param('sssii', $iconName, $label, $href, $displayOrder, $isActive);
                $success = $stmt->execute() ? 'Social link created.' : '';
                if (!$success) $error = 'Create failed: ' . $stmt->error;
                $stmt->close();
            } else {
                if ($id <= 0) { $error = 'Invalid ID.'; }
                else {
                    $stmt = $conn->prepare(
                        "UPDATE social_links SET icon_name=?, label=?, href=?, display_order=?, is_active=? WHERE id=?"
                    );
                    $stmt->bind_param('sssiii', $iconName, $label, $href, $displayOrder, $isActive, $id);
                    $success = $stmt->execute() ? 'Social link updated.' : '';
                    if (!$success) $error = 'Update failed: ' . $stmt->error;
                    $stmt->close();
                }
            }
        }
    }

}

// ---------- Fetch ----------
$links = [];
$res = $conn->query("SELECT id, icon_name, label, href, display_order, is_active FROM social_links ORDER BY display_order ASC, id ASC");
while ($r = $res->fetch_assoc()) {
    $r['id'] = (int) $r['id']; $r['display_order'] = (int) $r['display_order']; $r['is_active'] = (bool) $r['is_active'];
    $links[] = $r;
}
?>

<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div style="margin-bottom:16px;">
    <button class="btn btn-primary" onclick="openModal()">+ Add Social Link</button>
</div>

<div class="card">
    <div class="tbl-wrap">
        <table>
            <thead><tr><th>Icon Name</th><th>Label</th><th>URL</th><th>Order</th><th>Active</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($links as $l): ?>
                <tr>
                    <td><code style="background:#f0f0f0;padding:2px 6px;border-radius:4px;"><?= htmlspecialchars($l['icon_name']) ?></code></td>
                    <td><strong><?= htmlspecialchars($l['label']) ?></strong></td>
                    <td>
                        <a href="<?= htmlspecialchars($l['href']) ?>" target="_blank" rel="noopener"
                           style="color:var(--brand);font-size:.82rem;word-break:break-all;">
                            <?= htmlspecialchars($l['href']) ?>
                        </a>
                    </td>
                    <td><?= $l['display_order'] ?></td>
                    <td><?= $l['is_active'] ? '<span class="badge badge-green">Active</span>' : '<span class="badge badge-red">Off</span>' ?></td>
                    <td>
                        <button class="btn btn-secondary btn-sm" onclick='openModal(<?= htmlspecialchars(json_encode($l), ENT_QUOTES) ?>)'>Edit</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirmDelete('Delete &quot;<?= htmlspecialchars(addslashes($l['label'])) ?>&quot;?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $l['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($links)): ?>
                <tr><td colspan="6" style="color:#6b7280;text-align:center;">No social links yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="m-cards">
    <?php foreach ($links as $l): ?>
    <div class="m-card">
        <div class="m-title"><?= htmlspecialchars($l['label']) ?> <small>(<?= htmlspecialchars($l['icon_name']) ?>)</small></div>
        <div class="m-row"><span>URL</span><span style="word-break:break-all;font-size:.78rem;"><?= htmlspecialchars($l['href']) ?></span></div>
        <div class="m-row"><span>Active</span><span><?= $l['is_active'] ? 'Yes' : 'No' ?></span></div>
        <div class="m-actions">
            <button class="btn btn-secondary btn-sm" onclick='openModal(<?= htmlspecialchars(json_encode($l), ENT_QUOTES) ?>)'>Edit</button>
            <form method="POST" onsubmit="return confirmDelete()">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $l['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal -->
<div class="modal-backdrop" id="slModal">
    <div class="modal">
        <h3 id="modalTitle">Add Social Link</h3>
        <form method="POST" id="slForm">
            <input type="hidden" name="action" id="fAction" value="create">
            <input type="hidden" name="id"     id="fId"     value="">
            <div class="form-row">
                <div class="form-group">
                    <label for="fIconName">Icon Name <span style="color:red">*</span></label>
                    <input type="text" class="form-control" id="fIconName" name="icon_name" required placeholder="e.g. facebook, instagram, twitter">
                </div>
                <div class="form-group">
                    <label for="fLabel">Label <span style="color:red">*</span></label>
                    <input type="text" class="form-control" id="fLabel" name="label" required placeholder="Instagram">
                </div>
            </div>

            <div class="form-group">
                <label for="fHref">URL <span style="color:red">*</span></label>
                <input type="url" class="form-control" id="fHref" name="href" required placeholder="https://instagram.com/yourhandle">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="fOrder">Display Order</label>
                    <input type="number" class="form-control" id="fOrder" name="display_order" value="0" min="0">
                </div>
                <div class="form-group" style="display:flex;align-items:flex-end;">
                    <div class="check-group">
                        <input type="checkbox" id="fActive" name="is_active" value="1" checked>
                        <label for="fActive">Active</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(l) {
    document.getElementById('slModal').classList.add('active');
    if (l) {
        document.getElementById('modalTitle').textContent = 'Edit Social Link';
        document.getElementById('fAction').value   = 'update';
        document.getElementById('fId').value       = l.id;
        document.getElementById('fIconName').value = l.icon_name || '';
        document.getElementById('fLabel').value    = l.label || '';

        document.getElementById('fHref').value     = l.href || '';
        document.getElementById('fOrder').value    = l.display_order || 0;
        document.getElementById('fActive').checked = !!l.is_active;
        document.getElementById('submitBtn').textContent = 'Update';
    } else {
        document.getElementById('modalTitle').textContent = 'Add Social Link';
        document.getElementById('fAction').value = 'create';
        document.getElementById('slForm').reset();
        document.getElementById('fActive').checked = true;
        document.getElementById('submitBtn').textContent = 'Save';
    }
}
function closeModal() { document.getElementById('slModal').classList.remove('active'); }
document.getElementById('slModal').addEventListener('click', e => { if (e.target === document.getElementById('slModal')) closeModal(); });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
