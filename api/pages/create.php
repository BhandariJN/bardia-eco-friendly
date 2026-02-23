<?php
/**
 * POST /api/pages/create
 * Create a new CMS page (Protected)
 */

if (getRequestMethod() !== 'POST') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use POST.');
}

$auth = requireAuth();
$input = getJsonInput();

$title = sanitize($input['title'] ?? '');
$slug = sanitize($input['slug'] ?? '');
$content = $input['content'] ?? '';
$status = sanitize($input['status'] ?? 'draft');

if (empty($title) || empty($slug)) {
    jsonResponse(400, 'error', null, 'Title and slug are required.');
}

// Check for duplicate slug
$check = $conn->prepare("SELECT id FROM pages WHERE slug = ?");
$check->bind_param('s', $slug);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    $check->close();
    jsonResponse(409, 'error', null, 'A page with this slug already exists.');
}
$check->close();

$stmt = $conn->prepare("INSERT INTO pages (title, slug, content, status, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
$stmt->bind_param('ssss', $title, $slug, $content, $status);

if ($stmt->execute()) {
    $newId = $stmt->insert_id;
    $stmt->close();
    jsonResponse(201, 'success', ['id' => $newId], 'Page created successfully.');
} else {
    logError('Page create failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to create page.');
}
