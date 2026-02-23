<?php
/**
 * DELETE /api/social-links/delete
 * Delete a social link (auth required)
 * Body: { "id": 1 }
 */

if (getRequestMethod() !== 'DELETE') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use DELETE.');
}

$auth  = requireAuth();
$input = getJsonInput();
$id    = (int) ($input['id'] ?? 0);

if ($id <= 0) { jsonResponse(400, 'error', null, 'Valid id is required.'); }

$check = $conn->prepare("SELECT id FROM social_links WHERE id = ?");
$check->bind_param('i', $id); $check->execute(); $check->store_result();
if ($check->num_rows === 0) { $check->close(); jsonResponse(404, 'error', null, 'Social link not found.'); }
$check->close();

$stmt = $conn->prepare("DELETE FROM social_links WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $stmt->close();
    jsonResponse(200, 'success', null, 'Social link deleted successfully.');
} else {
    logError('Social link delete failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to delete social link.');
}
