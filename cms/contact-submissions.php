<?php
/**
 * CMS — Contact Submissions Inbox
 */

$pageTitle = 'Contact Submissions';
require_once __DIR__ . '/includes/header.php';

$success = '';
$error   = '';

$allowedStatuses = ['new', 'read', 'replied', 'archived'];

// ---------- Handle POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id   = (int) ($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM contact_submissions WHERE id = ?");
        $stmt->bind_param('i', $id);
        $success = $stmt->execute() ? 'Submission deleted.' : '';
        if (!$success) $error = 'Delete failed: ' . $stmt->error;
        $stmt->close();

    } elseif ($action === 'update_status') {
        $id     = (int)   ($_POST['id']     ?? 0);
        $status =          $_POST['status'] ?? '';
        if ($id > 0 && in_array($status, $allowedStatuses, true)) {
            $stmt = $conn->prepare("UPDATE contact_submissions SET status = ? WHERE id = ?");
            $stmt->bind_param('si', $status, $id);
            $success = $stmt->execute() ? 'Status updated to "' . $status . '".' : '';
            if (!$success) $error = 'Update failed: ' . $stmt->error;
            $stmt->close();
        } else {
            $error = 'Invalid ID or status.';
        }
    }
}

// ---------- Count per status for badges ----------
$counts = ['new' => 0, 'read' => 0, 'replied' => 0, 'archived' => 0];
$cRes = $conn->query("SELECT status, COUNT(*) AS c FROM contact_submissions GROUP BY status");
while ($cr = $cRes->fetch_assoc()) { $counts[$cr['status']] = (int) $cr['c']; }

// ---------- Active filter ----------
$filterStatus = $_GET['status'] ?? '';
if (!in_array($filterStatus, $allowedStatuses, true)) $filterStatus = '';

// ---------- Fetch submissions ----------
$submissions = [];
$sql = "SELECT id, full_name, email, phone, num_guests, preferred_package, travel_dates, message, status, created_at FROM contact_submissions";
if ($filterStatus !== '') {
    $stmt = $conn->prepare($sql . " WHERE status = ? ORDER BY created_at DESC");
    $stmt->bind_param('s', $filterStatus);
} else {
    $stmt = $conn->prepare($sql . " ORDER BY created_at DESC");
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) { $row['id'] = (int) $row['id']; $submissions[] = $row; }
$stmt->close();

$statusBadge = [
    'new'      => 'badge-green',
    'read'     => 'badge-gold',
    'replied'  => 'badge',
    'archived' => 'badge-red',
];
$statusLabels = ['new' => '🆕 New', 'read' => '👁 Read', 'replied' => '✅ Replied', 'archived' => '📦 Archived'];
?>

<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<!-- Status filter tabs -->
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <a href="contact-submissions.php" class="btn btn-sm <?= $filterStatus === '' ? 'btn-primary' : 'btn-secondary' ?>">
        All <span style="opacity:.7;">(<?= array_sum($counts) ?>)</span>
    </a>
    <?php foreach ($allowedStatuses as $s): ?>
    <a href="contact-submissions.php?status=<?= $s ?>" class="btn btn-sm <?= $filterStatus === $s ? 'btn-primary' : 'btn-secondary' ?>">
        <?= htmlspecialchars($statusLabels[$s]) ?>
        <?php if ($counts[$s] > 0): ?><span style="background:rgba(255,255,255,.25);border-radius:99px;padding:0 6px;margin-left:2px;"><?= $counts[$s] ?></span><?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="tbl-wrap">
        <table>
            <thead><tr><th>Name</th><th>Email / Phone</th><th>Guests</th><th>Package</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($submissions as $s): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($s['full_name']) ?></strong><br>
                        <small style="color:#6b7280;"><?= date('d M Y, H:i', strtotime($s['created_at'])) ?></small>
                    </td>
                    <td>
                        <?= htmlspecialchars($s['email']) ?><br>
                        <small><?= htmlspecialchars($s['phone']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($s['num_guests']) ?></td>
                    <td>
                        <?= $s['preferred_package'] ? htmlspecialchars($s['preferred_package']) : '<span style="color:#9ca3af">—</span>' ?>
                        <?php if ($s['travel_dates']): ?><br><small style="color:#6b7280;"><?= htmlspecialchars($s['travel_dates']) ?></small><?php endif; ?>
                    </td>
                    <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
                    <td><span class="badge <?= $statusBadge[$s['status']] ?? 'badge' ?>"><?= ucfirst($s['status']) ?></span></td>
                    <td>
                        <button class="btn btn-secondary btn-sm" onclick='viewSubmission(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)'>View</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirmDelete('Delete this submission?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">✕</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($submissions)): ?>
                <tr><td colspan="7" style="color:#6b7280;text-align:center;padding:24px;">
                    <?= $filterStatus ? 'No ' . $filterStatus . ' submissions.' : 'No submissions yet.' ?>
                </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Mobile cards -->
<div class="m-cards">
    <?php foreach ($submissions as $s): ?>
    <div class="m-card">
        <div class="m-title"><?= htmlspecialchars($s['full_name']) ?> <span class="badge <?= $statusBadge[$s['status']] ?? '' ?>"><?= ucfirst($s['status']) ?></span></div>
        <div class="m-row"><span>Email</span><span><?= htmlspecialchars($s['email']) ?></span></div>
        <div class="m-row"><span>Phone</span><span><?= htmlspecialchars($s['phone']) ?></span></div>
        <div class="m-row"><span>Guests</span><span><?= htmlspecialchars($s['num_guests']) ?></span></div>
        <div class="m-row"><span>Received</span><span><?= date('d M Y', strtotime($s['created_at'])) ?></span></div>
        <div class="m-actions">
            <button class="btn btn-secondary btn-sm" onclick='viewSubmission(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)'>View</button>
            <form method="POST" onsubmit="return confirmDelete()">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- View / Status Modal -->
<div class="modal-backdrop" id="viewModal">
    <div class="modal" style="width:min(640px,96vw);">
        <h3 id="vmName" style="margin-bottom:4px;"></h3>
        <p id="vmMeta" style="color:#6b7280;font-size:.82rem;margin-bottom:16px;"></p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 16px;font-size:.88rem;margin-bottom:14px;">
            <div><span style="color:#6b7280;">Email</span><br><strong id="vmEmail"></strong></div>
            <div><span style="color:#6b7280;">Phone</span><br><strong id="vmPhone"></strong></div>
            <div><span style="color:#6b7280;">Guests</span><br><span id="vmGuests"></span></div>
            <div><span style="color:#6b7280;">Status</span><br><span id="vmStatus"></span></div>
            <div><span style="color:#6b7280;">Package</span><br><span id="vmPackage"></span></div>
            <div><span style="color:#6b7280;">Travel Dates</span><br><span id="vmDates"></span></div>
        </div>

        <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:12px;font-size:.9rem;line-height:1.6;margin-bottom:16px;">
            <div style="font-size:.75rem;color:#6b7280;margin-bottom:4px;font-weight:600;">MESSAGE</div>
            <div id="vmMessage" style="white-space:pre-wrap;"></div>
        </div>

        <!-- Quick status update -->
        <form method="POST" id="statusForm" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:16px;">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="id" id="vmId">
            <label style="font-size:.85rem;font-weight:500;">Change status:</label>
            <?php foreach ($allowedStatuses as $st): ?>
            <button type="submit" name="status" value="<?= $st ?>" class="btn btn-secondary btn-sm"><?= htmlspecialchars($statusLabels[$st]) ?></button>
            <?php endforeach; ?>
        </form>

        <div style="text-align:right;">
            <button class="btn btn-secondary" onclick="closeView()">Close</button>
        </div>
    </div>
</div>

<script>
function viewSubmission(s) {
    document.getElementById('viewModal').classList.add('active');
    document.getElementById('vmName').textContent    = s.full_name;
    document.getElementById('vmMeta').textContent    = 'Received: ' + s.created_at;
    document.getElementById('vmEmail').textContent   = s.email;
    document.getElementById('vmPhone').textContent   = s.phone;
    document.getElementById('vmGuests').textContent  = s.num_guests;
    document.getElementById('vmStatus').textContent  = s.status;
    document.getElementById('vmPackage').textContent = s.preferred_package || '—';
    document.getElementById('vmDates').textContent   = s.travel_dates || '—';
    document.getElementById('vmMessage').textContent = s.message;
    document.getElementById('vmId').value            = s.id;
}
function closeView() { document.getElementById('viewModal').classList.remove('active'); }
document.getElementById('viewModal').addEventListener('click', e => { if (e.target === document.getElementById('viewModal')) closeView(); });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
