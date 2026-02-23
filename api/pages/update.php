<?php
/**
 * PUT /api/pages/update
 * Update a CMS page (Protected)
 */

if (getRequestMethod() !== 'PUT') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use PUT.');
}

$auth = requireAuth();
$input = getJsonInput();

$id = (int) ($input['id'] ?? 0);
if ($id <= 0) {
    jsonResponse(400, 'error', null, 'Valid page ID is required.');
}

// Check if page exists
$check = $conn->prepare("SELECT id FROM pages WHERE id = ?");
$check->bind_param('i', $id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    $check->close();
    jsonResponse(404, 'error', null, 'Page not found.');
}
$check->close();

$title = sanitize($input['title'] ?? '');
$slug = sanitize($input['slug'] ?? '');
$content = $input['content'] ?? '';
$status = sanitize($input['status'] ?? 'draft');

if (empty($title) || empty($slug)) {
    jsonResponse(400, 'error', null, 'Title and slug are required.');
}

// Check for duplicate slug (excluding current page)
$slugCheck = $conn->prepare("SELECT id FROM pages WHERE slug = ? AND id != ?");
$slugCheck->bind_param('si', $slug, $id);
$slugCheck->execute();
if ($slugCheck->get_result()->num_rows > 0) {
    $slugCheck->close();
    jsonResponse(409, 'error', null, 'A page with this slug already exists.');
}
$slugCheck->close();

$stmt = $conn->prepare("UPDATE pages SET title = ?, slug = ?, content = ?, status = ?, updated_at = NOW() WHERE id = ?");
$stmt->bind_param('ssssi', $title, $slug, $content, $status, $id);

if ($stmt->execute()) {
    $stmt->close();
    jsonResponse(200, 'success', null, 'Page updated successfully.');
} else {
    logError('Page update failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to update page.');
}
