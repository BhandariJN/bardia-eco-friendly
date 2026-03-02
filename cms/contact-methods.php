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
        $description  = trim($_POST['description']  ?? '');
        $displayOrder = (int)   ($_POST['display_order'] ?? 0);
        $isActive     = isset($_POST['is_active']) ? 1 : 0;

        if (empty($title))  { $error = 'Title is required.'; }
        elseif (empty($detail)) { $error = 'Detail is required.'; }
        else {
            if ($action === 'create') {
                $stmt = $conn->prepare(
                    "INSERT INTO contact_methods (icon, title, detail, description, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt->bind_param('ssssii', $icon, $title, $detail, $description, $displayOrder, $isActive);
                $success = $stmt->execute() ? 'Contact method created.' : '';
                if (!$success) $error = 'Create failed: ' . $stmt->error;
                $stmt->close();
            } else {
                if ($id <= 0) { $error = 'Invalid ID.'; }
                else {
                    $stmt = $conn->prepare(
                        "UPDATE contact_methods SET icon=?, title=?, detail=?, description=?, display_order=?, is_active=? WHERE id=?"
                    );
                    $stmt->bind_param('ssssiii', $icon, $title, $detail, $description, $displayOrder, $isActive, $id);
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
try {
    $res = db_query($conn, "SELECT id, icon, title, detail, description, display_order, is_active FROM contact_methods ORDER BY display_order ASC, id ASC");
    while ($r = $res->fetch_assoc()) {
        $r['id'] = (int) $r['id']; $r['display_order'] = (int) $r['display_order']; $r['is_active'] = (bool) $r['is_active'];
        $methods[] = $r;
    }
} catch (RuntimeException $e) {
    error_log('[contact-methods] fetch: ' . $e->getMessage());
    $error = 'Could not load contact methods. Please try again.';
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
            <thead><tr><th>Icon</th><th>Title</th><th>Detail</th><th>Order</th><th>Active</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($methods as $m): ?>
                <tr>
                    <td style="font-size:1.5rem;"><?= htmlspecialchars($m['icon']) ?></td>
                    <td><strong><?= htmlspecialchars($m['title']) ?></strong></td>
                    <td><?= htmlspecialchars($m['detail']) ?></td>
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
                <tr><td colspan="6" style="color:#6b7280;text-align:center;">No contact methods yet.</td></tr>
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

<!-- Icon Picker Styles -->
<style>
.icon-picker-wrapper { position: relative; }
.icon-picker-btn {
    display: flex; align-items: center; justify-content: center;
    width: 100%; height: 42px;
    background: #fff; border: 1px solid #d1d5db; border-radius: 8px;
    cursor: pointer; font-size: 1.4rem; transition: all .2s;
    gap: 4px; padding: 0 10px;
}
.icon-picker-btn:hover { border-color: #2e7d32; background: #f0fdf4; }
.icon-preview-empty { color: #9ca3af; font-size: .85rem; font-family: 'Inter', sans-serif; }
.icon-preview-selected { font-size: 1.5rem; line-height: 1; }
.icon-picker-dropdown {
    display: none; position: absolute; top: calc(100% + 6px); left: 0;
    z-index: 1000; background: #fff; border: 1px solid #e5e7eb;
    border-radius: 10px; box-shadow: 0 8px 30px rgba(0,0,0,.15);
    padding: 10px; width: 280px;
}
.icon-picker-dropdown.open { display: block; }
.icon-picker-section { font-size: .7rem; font-weight: 600; color: #6b7280;
    text-transform: uppercase; letter-spacing: .5px; padding: 6px 4px 4px; }
.icon-picker-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 2px; }
.icon-picker-item {
    display: flex; align-items: center; justify-content: center;
    width: 40px; height: 40px; font-size: 1.4rem; border-radius: 8px;
    cursor: pointer; border: 2px solid transparent; transition: all .15s;
    background: transparent;
}
.icon-picker-item:hover { background: #f0fdf4; border-color: #a7d7ab; transform: scale(1.15); }
.icon-picker-item.selected { background: #e8f5e9; border-color: #2e7d32; }
.icon-picker-footer {
    margin-top: 10px; padding: 8px; background: #f9fafb; border-radius: 6px;
    font-size: .8rem; color: #374151; text-align: center; border-top: 1px solid #e5e7eb;
    font-weight: 500; min-height: 34px; display: flex; align-items: center; justify-content: center;
}
</style>

<!-- Modal -->
<div class="modal-backdrop" id="cmModal">
    <div class="modal">
        <h3 id="modalTitle">Add Contact Method</h3>
        <form method="POST" id="cmForm">
            <input type="hidden" name="action" id="fAction" value="create">
            <input type="hidden" name="id"     id="fId"     value="">
            <div class="form-row">
                <div class="form-group" style="flex:0 0 120px;">
                    <label>Icon</label>
                    <input type="hidden" id="fIcon" name="icon" value="">
                    <div class="icon-picker-wrapper" id="iconPickerWrapper">
                        <button type="button" class="icon-picker-btn" id="iconPickerBtn" onclick="toggleIconPicker()">
                            <span id="iconPreview" class="icon-preview-empty">?</span>
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" style="margin-left:4px;"><path d="M3 4.5L6 7.5L9 4.5" stroke="#6b7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <div class="icon-picker-dropdown" id="iconPickerDropdown"></div>
                    </div>
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
// ── Icon Picker ──────────────────────────────────────────────────
const iconSections = [
    { label: 'Communication', icons: [
        { char: '📞', name: 'Phone' }, { char: '📱', name: 'Mobile' }, { char: '☎️', name: 'Landline' }, 
        { char: '💬', name: 'Chat' }, { char: '✉️', name: 'Message' }, { char: '📧', name: 'Email' }, 
        { char: '📩', name: 'Received' }, { char: '📨', name: 'Incoming' }, { char: '📤', name: 'Outgoing' }, 
        { char: '📥', name: 'Inbox' }, { char: '🗨️', name: 'Speech' }, { char: '💌', name: 'Love Letter' }
    ]},
    { label: 'Social & Web', icons: [
        { char: '🌐', name: 'Website' }, { char: '💻', name: 'Online' }, { char: '🔗', name: 'Link' }, 
        { char: '📢', name: 'Announcement' }, { char: '📣', name: 'Megaphone' }, { char: '🎯', name: 'Target' }, 
        { char: '🤝', name: 'Partnership' }, { char: '👥', name: 'Community' }, { char: '🏢', name: 'Office' }, 
        { char: '🏠', name: 'Address' }
    ]},
    { label: 'Location', icons: [
        { char: '📍', name: 'Pin' }, { char: '🗺️', name: 'Map' }, { char: '🧭', name: 'Compass' }, 
        { char: '🚗', name: 'Car' }, { char: '✈️', name: 'Flight' }, { char: '🏔️', name: 'Mountains' }
    ]},
    { label: 'Misc', icons: [
        { char: '⏰', name: 'Time' }, { char: '🕐', name: 'Clock' }, { char: '📅', name: 'Calendar' }, 
        { char: 'ℹ️', name: 'Info' }, { char: '❓', name: 'Help' }, { char: '⚡', name: 'Quick' }, 
        { char: '🔔', name: 'Alert' }, { char: '✅', name: 'Ok' }, { char: '⭐', name: 'Star' }, { char: '❤️', name: 'Like' }
    ] }
];

const dropdown = document.getElementById('iconPickerDropdown');
iconSections.forEach(section => {
    const lbl = document.createElement('div');
    lbl.className = 'icon-picker-section';
    lbl.textContent = section.label;
    dropdown.appendChild(lbl);
    const grid = document.createElement('div');
    grid.className = 'icon-picker-grid';
    section.icons.forEach(ico => {
        const item = document.createElement('span');
        item.className = 'icon-picker-item';
        item.textContent = ico.char;
        item.title = ico.name;
        item.addEventListener('click', () => selectIcon(ico.char));
        
        // Hover effect for name display
        item.addEventListener('mouseenter', () => {
            pickerFooter.textContent = ico.name;
            pickerFooter.style.color = 'var(--primary)';
        });
        item.addEventListener('mouseleave', () => {
            pickerFooter.textContent = 'Select an icon';
            pickerFooter.style.color = '#374151';
        });
        
        grid.appendChild(item);
    });
    dropdown.appendChild(grid);
});

// Create footer for hover names
const pickerFooter = document.createElement('div');
pickerFooter.className = 'icon-picker-footer';
pickerFooter.textContent = 'Select an icon';
dropdown.appendChild(pickerFooter);

function selectIcon(icon) {
    document.getElementById('fIcon').value = icon;
    const preview = document.getElementById('iconPreview');
    preview.textContent = icon;
    preview.className = 'icon-preview-selected';
    // highlight selected
    dropdown.querySelectorAll('.icon-picker-item').forEach(el => {
        el.classList.toggle('selected', el.textContent === icon);
    });
    dropdown.classList.remove('open');
}

function setIconPreview(icon) {
    const preview = document.getElementById('iconPreview');
    if (icon) {
        preview.textContent = icon;
        preview.className = 'icon-preview-selected';
        dropdown.querySelectorAll('.icon-picker-item').forEach(el => {
            el.classList.toggle('selected', el.textContent === icon);
        });
    } else {
        preview.textContent = '?';
        preview.className = 'icon-preview-empty';
        dropdown.querySelectorAll('.icon-picker-item').forEach(el => el.classList.remove('selected'));
    }
}

function toggleIconPicker() {
    dropdown.classList.toggle('open');
}

// Close picker when clicking outside
document.addEventListener('click', e => {
    if (!document.getElementById('iconPickerWrapper').contains(e.target)) {
        dropdown.classList.remove('open');
    }
});

// ── Modal ────────────────────────────────────────────────────────
function openModal(m) {
    document.getElementById('cmModal').classList.add('active');
    if (m) {
        document.getElementById('modalTitle').textContent = 'Edit Contact Method';
        document.getElementById('fAction').value   = 'update';
        document.getElementById('fId').value       = m.id;
        document.getElementById('fIcon').value     = m.icon || '';
        setIconPreview(m.icon || '');
        document.getElementById('fTitle').value    = m.title || '';
        document.getElementById('fDetail').value   = m.detail || '';
        document.getElementById('fDesc').value     = m.description || '';
        document.getElementById('fOrder').value    = m.display_order || 0;
        document.getElementById('fActive').checked = !!m.is_active;
        document.getElementById('submitBtn').textContent = 'Update';
    } else {
        document.getElementById('modalTitle').textContent = 'Add Contact Method';
        document.getElementById('fAction').value = 'create';
        document.getElementById('cmForm').reset();
        document.getElementById('fIcon').value = '';
        setIconPreview('');
        document.getElementById('fActive').checked = true;
        document.getElementById('submitBtn').textContent = 'Save';
    }
}
function closeModal() {
    document.getElementById('cmModal').classList.remove('active');
    dropdown.classList.remove('open');
}
document.getElementById('cmModal').addEventListener('click', e => { if (e.target === document.getElementById('cmModal')) closeModal(); });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
