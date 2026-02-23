<?php
/**
 * DELETE /api/homestays/delete
 * Delete a homestay (Protected)
 */

if (getRequestMethod() !== 'DELETE') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use DELETE.');
}

$auth = requireAuth();
$input = getJsonInput();

$id = (int) ($input['id'] ?? 0);
if ($id <= 0) {
    jsonResponse(400, 'error', null, 'Valid homestay ID is required.');
}

// Check if homestay exists
$check = $conn->prepare("SELECT id FROM homestays WHERE id = ?");
$check->bind_param('i', $id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    $check->close();
    jsonResponse(404, 'error', null, 'Homestay not found.');
}
$check->close();

$stmt = $conn->prepare("DELETE FROM homestays WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $stmt->close();
    jsonResponse(200, 'success', null, 'Homestay deleted successfully.');
} else {
    logError('Homestay delete failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to delete homestay.');
}
