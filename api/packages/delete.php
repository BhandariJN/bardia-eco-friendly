<?php
/**
 * DELETE /api/packages/delete
 * Delete a package (auth required). CASCADE removes features + comparison values.
 */

if (getRequestMethod() !== 'DELETE') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use DELETE.');
}

$auth  = requireAuth();
$input = getJsonInput();

$id = (int) ($input['id'] ?? 0);

if ($id <= 0) {
    jsonResponse(400, 'error', null, 'Valid package id is required.');
}

$check = $conn->prepare("SELECT id FROM packages WHERE id = ?");
$check->bind_param('i', $id);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    $check->close();
    jsonResponse(404, 'error', null, 'Package not found.');
}
$check->close();

$stmt = $conn->prepare("DELETE FROM packages WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $stmt->close();
    jsonResponse(200, 'success', null, 'Package deleted successfully.');
} else {
    logError('Package delete failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to delete package.');
}
