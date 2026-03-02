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
try {
    $res = db_query($conn, "SELECT id, icon_name, label, href, display_order, is_active FROM social_links ORDER BY display_order ASC, id ASC");
    while ($r = $res->fetch_assoc()) {
        $r['id'] = (int) $r['id']; $r['display_order'] = (int) $r['display_order']; $r['is_active'] = (bool) $r['is_active'];
        $links[] = $r;
    }
} catch (RuntimeException $e) {
    error_log('[social-links] fetch: ' . $e->getMessage());
    $error = 'Could not load social links. Please try again.';
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
            <thead><tr><th>Icon</th><th>Label</th><th>URL</th><th>Order</th><th>Active</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($links as $l): ?>
                <tr>
                    <td style="font-size:1.3rem;"><?php
                        $socialIcons = ['facebook'=>'📘','instagram'=>'📸','twitter'=>'🐦','youtube'=>'▶️','tiktok'=>'🎵','linkedin'=>'💼','whatsapp'=>'💬','pinterest'=>'📌','snapchat'=>'👻','telegram'=>'✈️','reddit'=>'🔴','discord'=>'🎮','viber'=>'💜','messenger'=>'💙','wechat'=>'💚','line'=>'🟢','tumblr'=>'📝','github'=>'🐙','website'=>'🌐','email'=>'✉️','blog'=>'📰'];
                        echo ($socialIcons[strtolower($l['icon_name'])] ?? '🔗') . ' ';
                    ?><code style="font-size:.75rem;background:#f0f0f0;padding:1px 5px;border-radius:3px;"><?= htmlspecialchars($l['icon_name']) ?></code></td>
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
        <div class="m-title"><?php $socialIcons = $socialIcons ?? []; echo ($socialIcons[strtolower($l['icon_name'])] ?? '🔗'); ?> <?= htmlspecialchars($l['label']) ?></div>
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

<!-- Icon Picker Styles -->
<style>
.sl-icon-picker-wrapper { position: relative; }
.sl-icon-picker-btn {
    display: flex; align-items: center;
    width: 100%; height: 42px;
    background: #fff; border: 1px solid #d1d5db; border-radius: 8px;
    cursor: pointer; font-size: .9rem; transition: all .2s;
    gap: 8px; padding: 0 12px;
}
.sl-icon-picker-btn:hover { border-color: #2e7d32; background: #f0fdf4; }
.sl-icon-preview-empty { color: #9ca3af; font-size: .85rem; font-family: 'Inter', sans-serif; }
.sl-icon-preview-selected { font-size: .9rem; display: flex; align-items: center; gap: 6px; }
.sl-icon-preview-selected .sl-emoji { font-size: 1.3rem; }
.sl-icon-picker-dropdown {
    display: none; position: absolute; top: calc(100% + 6px); left: 0;
    z-index: 1000; background: #fff; border: 1px solid #e5e7eb;
    border-radius: 10px; box-shadow: 0 8px 30px rgba(0,0,0,.15);
    padding: 8px; width: 320px; max-height: 300px; overflow-y: auto;
}
.sl-icon-picker-dropdown.open { display: block; }
.sl-icon-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px; }
.sl-icon-item {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 10px; font-size: .85rem; border-radius: 8px;
    cursor: pointer; border: 2px solid transparent; transition: all .15s;
    background: transparent; font-family: 'Inter', sans-serif;
}
.sl-icon-item:hover { background: #f0fdf4; border-color: #a7d7ab; }
.sl-icon-item.selected { background: #e8f5e9; border-color: #2e7d32; }
.sl-icon-item .sl-emoji { font-size: 1.2rem; flex-shrink: 0; }
</style>

<!-- Modal -->
<div class="modal-backdrop" id="slModal">
    <div class="modal">
        <h3 id="modalTitle">Add Social Link</h3>
        <form method="POST" id="slForm">
            <input type="hidden" name="action" id="fAction" value="create">
            <input type="hidden" name="id"     id="fId"     value="">
            <div class="form-row">
                <div class="form-group">
                    <label>Icon <span style="color:red">*</span></label>
                    <input type="hidden" id="fIconName" name="icon_name" value="">
                    <div class="sl-icon-picker-wrapper" id="slIconPickerWrapper">
                        <button type="button" class="sl-icon-picker-btn" id="slIconPickerBtn" onclick="toggleSlIconPicker()">
                            <span id="slIconPreview" class="sl-icon-preview-empty">Select an icon…</span>
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" style="margin-left:auto;"><path d="M3 4.5L6 7.5L9 4.5" stroke="#6b7280" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <div class="sl-icon-picker-dropdown" id="slIconDropdown"></div>
                    </div>
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
// ── Social Icon Picker ───────────────────────────────────────────
const slIcons = [
    { name: 'facebook',  emoji: '📘' },
    { name: 'instagram', emoji: '📸' },
    { name: 'twitter',   emoji: '🐦' },
    { name: 'youtube',   emoji: '▶️' },
    { name: 'tiktok',    emoji: '🎵' },
    { name: 'linkedin',  emoji: '💼' },
    { name: 'whatsapp',  emoji: '💬' },
    { name: 'pinterest', emoji: '📌' },
    { name: 'snapchat',  emoji: '👻' },
    { name: 'telegram',  emoji: '✈️' },
    { name: 'reddit',    emoji: '🔴' },
    { name: 'discord',   emoji: '🎮' },
    { name: 'viber',     emoji: '💜' },
    { name: 'messenger', emoji: '💙' },
    { name: 'wechat',    emoji: '💚' },
    { name: 'line',      emoji: '🟢' },
    { name: 'tumblr',    emoji: '📝' },
    { name: 'github',    emoji: '🐙' },
    { name: 'website',   emoji: '🌐' },
    { name: 'email',     emoji: '✉️' },
    { name: 'blog',      emoji: '📰' },
];

const slDropdown = document.getElementById('slIconDropdown');
const slGrid = document.createElement('div');
slGrid.className = 'sl-icon-grid';
slIcons.forEach(item => {
    const el = document.createElement('span');
    el.className = 'sl-icon-item';
    el.dataset.name = item.name;
    el.innerHTML = '<span class="sl-emoji">' + item.emoji + '</span>' + item.name;
    el.addEventListener('click', () => selectSlIcon(item.name, item.emoji));
    slGrid.appendChild(el);
});
slDropdown.appendChild(slGrid);

function selectSlIcon(name, emoji) {
    document.getElementById('fIconName').value = name;
    const preview = document.getElementById('slIconPreview');
    preview.innerHTML = '<span class="sl-emoji">' + emoji + '</span>' + name;
    preview.className = 'sl-icon-preview-selected';
    slDropdown.querySelectorAll('.sl-icon-item').forEach(el => {
        el.classList.toggle('selected', el.dataset.name === name);
    });
    slDropdown.classList.remove('open');
}

function setSlIconPreview(name) {
    const preview = document.getElementById('slIconPreview');
    const match = slIcons.find(i => i.name === name);
    if (match) {
        preview.innerHTML = '<span class="sl-emoji">' + match.emoji + '</span>' + match.name;
        preview.className = 'sl-icon-preview-selected';
        slDropdown.querySelectorAll('.sl-icon-item').forEach(el => {
            el.classList.toggle('selected', el.dataset.name === name);
        });
    } else if (name) {
        preview.innerHTML = '<span class="sl-emoji">🔗</span>' + name;
        preview.className = 'sl-icon-preview-selected';
        slDropdown.querySelectorAll('.sl-icon-item').forEach(el => el.classList.remove('selected'));
    } else {
        preview.textContent = 'Select an icon…';
        preview.className = 'sl-icon-preview-empty';
        slDropdown.querySelectorAll('.sl-icon-item').forEach(el => el.classList.remove('selected'));
    }
}

function toggleSlIconPicker() {
    slDropdown.classList.toggle('open');
}

document.addEventListener('click', e => {
    if (!document.getElementById('slIconPickerWrapper').contains(e.target)) {
        slDropdown.classList.remove('open');
    }
});

// ── Modal ────────────────────────────────────────────────────────
function openModal(l) {
    document.getElementById('slModal').classList.add('active');
    if (l) {
        document.getElementById('modalTitle').textContent = 'Edit Social Link';
        document.getElementById('fAction').value   = 'update';
        document.getElementById('fId').value       = l.id;
        document.getElementById('fIconName').value = l.icon_name || '';
        setSlIconPreview(l.icon_name || '');
        document.getElementById('fLabel').value    = l.label || '';
        document.getElementById('fHref').value     = l.href || '';
        document.getElementById('fOrder').value    = l.display_order || 0;
        document.getElementById('fActive').checked = !!l.is_active;
        document.getElementById('submitBtn').textContent = 'Update';
    } else {
        document.getElementById('modalTitle').textContent = 'Add Social Link';
        document.getElementById('fAction').value = 'create';
        document.getElementById('slForm').reset();
        document.getElementById('fIconName').value = '';
        setSlIconPreview('');
        document.getElementById('fActive').checked = true;
        document.getElementById('submitBtn').textContent = 'Save';
    }
}
function closeModal() {
    document.getElementById('slModal').classList.remove('active');
    slDropdown.classList.remove('open');
}
document.getElementById('slModal').addEventListener('click', e => { if (e.target === document.getElementById('slModal')) closeModal(); });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
