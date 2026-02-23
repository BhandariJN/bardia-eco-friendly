<?php
/**
 * DELETE /api/gallery-images/delete
 * Delete a gallery image (auth required). Also removes the physical file.
 */

if (getRequestMethod() !== 'DELETE') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use DELETE.');
}

$auth  = requireAuth();
$input = getJsonInput();

$id = (int) ($input['id'] ?? 0);

if ($id <= 0) {
    jsonResponse(400, 'error', null, 'Valid image id is required.');
}

// Fetch image record to get the file path
$fetch = $conn->prepare("SELECT id, image_url FROM gallery_images WHERE id = ?");
$fetch->bind_param('i', $id);
$fetch->execute();
$result = $fetch->get_result();
$image  = $result->fetch_assoc();
$fetch->close();

if (!$image) {
    jsonResponse(404, 'error', null, 'Gallery image not found.');
}

// Delete DB record
$stmt = $conn->prepare("DELETE FROM gallery_images WHERE id = ?");
$stmt->bind_param('i', $id);

if (!$stmt->execute()) {
    logError('Gallery image delete failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to delete gallery image.');
}
$stmt->close();

// Remove physical file (best-effort)
// image_url is like /bardiya-eco-friendly/storage/gallery/filename.jpg
$filename   = basename($image['image_url']);
$filePath   = __DIR__ . '/../../storage/gallery/' . $filename;
if (file_exists($filePath)) {
    @unlink($filePath);
}

jsonResponse(200, 'success', null, 'Gallery image deleted successfully.');
