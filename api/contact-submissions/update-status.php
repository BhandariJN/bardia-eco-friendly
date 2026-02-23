<?php
/**
 * PUT /api/contact-submissions/update-status
 * Update the status of a contact submission (auth required)
 * Body: { "id": 1, "status": "read" }
 * Allowed statuses: new, read, replied, archived
 */

if (getRequestMethod() !== 'PUT') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use PUT.');
}

$auth  = requireAuth();
$input = getJsonInput();

$id     = (int)    ($input['id']     ?? 0);
$status =           $input['status'] ?? '';

$allowedStatuses = ['new', 'read', 'replied', 'archived'];

if ($id <= 0) { jsonResponse(400, 'error', null, 'Valid id is required.'); }
if (!in_array($status, $allowedStatuses, true)) {
    jsonResponse(400, 'error', null, 'Invalid status. Allowed: new, read, replied, archived.');
}

$check = $conn->prepare("SELECT id FROM contact_submissions WHERE id = ?");
$check->bind_param('i', $id); $check->execute(); $check->store_result();
if ($check->num_rows === 0) { $check->close(); jsonResponse(404, 'error', null, 'Submission not found.'); }
$check->close();

$stmt = $conn->prepare("UPDATE contact_submissions SET status = ? WHERE id = ?");
$stmt->bind_param('si', $status, $id);

if ($stmt->execute()) {
    $stmt->close();
    jsonResponse(200, 'success', ['id' => $id, 'status' => $status], 'Status updated successfully.');
} else {
    logError('Contact submission status update failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to update status.');
}
