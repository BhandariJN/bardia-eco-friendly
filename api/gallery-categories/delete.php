<?php
/**
 * DELETE /api/gallery-categories/delete
 * Delete a gallery category (auth required). CASCADE removes images.
 */

if (getRequestMethod() !== 'DELETE') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use DELETE.');
}

$auth  = requireAuth();
$input = getJsonInput();

$id = (int) ($input['id'] ?? 0);

if ($id <= 0) {
    jsonResponse(400, 'error', null, 'Valid category id is required.');
}

$check = $conn->prepare("SELECT id FROM gallery_categories WHERE id = ?");
$check->bind_param('i', $id);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    $check->close();
    jsonResponse(404, 'error', null, 'Gallery category not found.');
}
$check->close();

$stmt = $conn->prepare("DELETE FROM gallery_categories WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $stmt->close();
    jsonResponse(200, 'success', null, 'Gallery category deleted successfully.');
} else {
    logError('Gallery category delete failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to delete gallery category.');
}
