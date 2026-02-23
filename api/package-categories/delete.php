<?php
/**
 * DELETE /api/package-categories/delete
 * Delete a package category (auth required)
 * Body: { "id": 1 }
 * Note: CASCADE will delete all packages (and their features) in this category.
 */

if (getRequestMethod() !== 'DELETE') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use DELETE.');
}

$auth  = requireAuth();
$input = getJsonInput();

$id = (int) ($input['id'] ?? 0);

if ($id <= 0) {
    jsonResponse(400, 'error', null, 'Valid id is required.');
}

// Verify exists
$check = $conn->prepare("SELECT id FROM package_categories WHERE id = ?");
$check->bind_param('i', $id);
$check->execute();
$check->store_result();
if ($check->num_rows === 0) {
    $check->close();
    jsonResponse(404, 'error', null, 'Package category not found.');
}
$check->close();

$stmt = $conn->prepare("DELETE FROM package_categories WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $stmt->close();
    jsonResponse(200, 'success', null, 'Package category deleted successfully.');
} else {
    logError('Package category delete failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to delete package category.');
}
