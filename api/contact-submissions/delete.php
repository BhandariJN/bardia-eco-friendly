<?php
/**
 * DELETE /api/contact-submissions/delete
 * Delete a contact submission (auth required)
 * Body: { "id": 1 }
 */

if (getRequestMethod() !== 'DELETE') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use DELETE.');
}

$auth  = requireAuth();
$input = getJsonInput();
$id    = (int) ($input['id'] ?? 0);

if ($id <= 0) { jsonResponse(400, 'error', null, 'Valid id is required.'); }

$check = $conn->prepare("SELECT id FROM contact_submissions WHERE id = ?");
$check->bind_param('i', $id); $check->execute(); $check->store_result();
if ($check->num_rows === 0) { $check->close(); jsonResponse(404, 'error', null, 'Submission not found.'); }
$check->close();

$stmt = $conn->prepare("DELETE FROM contact_submissions WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $stmt->close();
    jsonResponse(200, 'success', null, 'Submission deleted successfully.');
} else {
    logError('Contact submission delete failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to delete submission.');
}
