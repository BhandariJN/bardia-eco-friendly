<?php
/**
 * CMS — Contact Methods Management (Call Us, Email Us, WhatsApp cards)
 */

$pageTitle = 'Contact Methods';
require_once __DIR__ . '/includes/header.php';

$success = '';
$error   = '';

// ---------- Handle POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id   = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM contact_methods WHERE id = ?");
        $stmt->bind_param('i', $id);
        $success = $stmt->execute() ? 'Contact method deleted.' : '';
        if (!$success) $error = 'Delete failed: ' . $stmt->error;
        $stmt->close();

    } elseif (in_array($action, ['create', 'update'], true)) {
        $id           = (int)   ($_POST['id']            ?? 0);
        $icon         = trim($_POST['icon']         ?? '');
        $title        = trim($_POST['title']        ?? '');
        $detail       = trim($_POST['detail']       ?? '');
        $href         = trim($_POST['href']         ?? '');
        $description  = trim($_POST['description']  ?? '');
        $displayOrder = (int)   ($_POST['display_order'] ?? 0);
        $isActive     = isset($_POST['is_active']) ? 1 : 0;

        if (empty($title))  { $error = 'Title is required.'; }
        elseif (empty($detail)) { $error = 'Detail is required.'; }
        elseif (empty($href))   { $error = 'href is required.'; }
        else {
            if ($action === 'create') {
                $stmt = $conn->prepare(
                    "INSERT INTO contact_methods (icon, title, detail, href, description, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param('sssssii', $icon, $title, $detail, $href, $description, $displayOrder, $isActive);
                $success = $stmt->execute() ? 'Contact method created.' : '';
                if (!$success) $error = 'Create failed: ' . $stmt->error;
                $stmt->close();
            } else {
                if ($id <= 0) { $error = 'Invalid ID.'; }
                else {
                    $stmt = $conn->prepare(
                        "UPDATE contact_methods SET icon=?, title=?, detail=?, href=?, description=?, display_order=?, is_active=? WHERE id=?"
                    );
                    $stmt->bind_param('sssssiii', $icon, $title, $detail, $href, $description, $displayOrder, $isActive, $id);
                    $success = $stmt->execute() ? 'Contact method updated.' : '';
                    if (!$success) $error = 'Update failed: ' . $stmt->error;
                    $stmt->close();
                }
            }
        }
    }
}

// ---------- Fetch ----------
$methods = [];
$res = $conn->query("SELECT id, icon, title, detail, href, description, display_order, is_active FROM contact_methods ORDER BY display_order ASC, id ASC");
while ($r = $res->fetch_assoc()) {
    $r['id'] = (int) $r['id']; $r['display_order'] = (int) $r['display_order']; $r['is_active'] = (bool) $r['is_active'];
    $methods[] = $r;
}
?>

<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div style="margin-bottom:16px;">
    <button class="btn btn-primary" onclick="openModal()">+ Add Contact Method</button>
</div>

<div class="card">
    <div class="tbl-wrap">
        <table>
            <thead><tr><th>Icon</th><th>Title</th><th>Detail</th><th>href</th><th>Order</th><th>Active</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($methods as $m): ?>
                <tr>
                    <td style="font-size:1.5rem;"><?= htmlspecialchars($m['icon']) ?></td>
                    <td><strong><?= htmlspecialchars($m['title']) ?></strong></td>
                    <td><?= htmlspecialchars($m['detail']) ?></td>
                    <td><code style="font-size:.78rem;background:#f0f0f0;padding:1px 5px;border-radius:3px;"><?= htmlspecialchars($m['href']) ?></code></td>
                    <td><?= $m['display_order'] ?></td>
                    <td><?= $m['is_active'] ? '<span class="badge badge-green">Active</span>' : '<span class="badge badge-red">Off</span>' ?></td>
                    <td>
                        <button class="btn btn-secondary btn-sm" onclick='openModal(<?= htmlspecialchars(json_encode($m), ENT_QUOTES) ?>)'>Edit</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirmDelete('Delete &quot;<?= htmlspecialchars(addslashes($m['title'])) ?>&quot;?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($methods)): ?>
                <tr><td colspan="7" style="color:#6b7280;text-align:center;">No contact methods yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="m-cards">
    <?php foreach ($methods as $m): ?>
    <div class="m-card">
        <div class="m-title"><?= htmlspecialchars($m['icon']) ?> <?= htmlspecialchars($m['title']) ?></div>
        <div class="m-row"><span>Detail</span><span><?= htmlspecialchars($m['detail']) ?></span></div>
        <div class="m-row"><span>Active</span><span><?= $m['is_active'] ? 'Yes' : 'No' ?></span></div>
        <div class="m-actions">
            <button class="btn btn-secondary btn-sm" onclick='openModal(<?= htmlspecialchars(json_encode($m), ENT_QUOTES) ?>)'>Edit</button>
            <form method="POST" onsubmit="return confirmDelete()">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal -->
<div class="modal-backdrop" id="cmModal">
    <div class="modal">
        <h3 id="modalTitle">Add Contact Method</h3>
        <form method="POST" id="cmForm">
            <input type="hidden" name="action" id="fAction" value="create">
            <input type="hidden" name="id"     id="fId"     value="">
            <div class="form-row">
                <div class="form-group" style="flex:0 0 80px;">
                    <label for="fIcon">Icon</label>
                    <input type="text" class="form-control" id="fIcon" name="icon" maxlength="5" placeholder="📞">
                </div>
                <div class="form-group">
                    <label for="fTitle">Title <span style="color:red">*</span></label>
                    <input type="text" class="form-control" id="fTitle" name="title" required placeholder="Call Us">
                </div>
            </div>
            <div class="form-group">
                <label for="fDetail">Detail <span style="color:red">*</span></label>
                <input type="text" class="form-control" id="fDetail" name="detail" required placeholder="+91 98765 43210">
            </div>
            <div class="form-group">
                <label for="fHref">href (link) <span style="color:red">*</span></label>
                <input type="text" class="form-control" id="fHref" name="href" required placeholder="tel:+919876543210">
                <small style="color:#6b7280;font-size:.78rem;">e.g. tel:..., mailto:..., https://wa.me/...</small>
            </div>
            <div class="form-group">
                <label for="fDesc">Description</label>
                <textarea class="form-control" id="fDesc" name="description" rows="2" placeholder="Available 8 AM – 9 PM, 7 days a week"></textarea>
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
function openModal(m) {
    document.getElementById('cmModal').classList.add('active');
    if (m) {
        document.getElementById('modalTitle').textContent = 'Edit Contact Method';
        document.getElementById('fAction').value   = 'update';
        document.getElementById('fId').value       = m.id;
        document.getElementById('fIcon').value     = m.icon || '';
        document.getElementById('fTitle').value    = m.title || '';
        document.getElementById('fDetail').value   = m.detail || '';
        document.getElementById('fHref').value     = m.href || '';
        document.getElementById('fDesc').value     = m.description || '';
        document.getElementById('fOrder').value    = m.display_order || 0;
        document.getElementById('fActive').checked = !!m.is_active;
        document.getElementById('submitBtn').textContent = 'Update';
    } else {
        document.getElementById('modalTitle').textContent = 'Add Contact Method';
        document.getElementById('fAction').value = 'create';
        document.getElementById('cmForm').reset();
        document.getElementById('fActive').checked = true;
        document.getElementById('submitBtn').textContent = 'Save';
    }
}
function closeModal() { document.getElementById('cmModal').classList.remove('active'); }
document.getElementById('cmModal').addEventListener('click', e => { if (e.target === document.getElementById('cmModal')) closeModal(); });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
