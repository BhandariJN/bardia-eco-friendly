<?php
/**
 * POST /api/contact-methods/create
 * Create a contact method card (auth required)
 * Body: { "icon":"📞", "title":"Call Us", "detail":"+91 98765 43210",
 *          "description":"Available 8 AM – 9 PM",
 *          "display_order":0, "is_active":true }
 */

if (getRequestMethod() !== 'POST') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use POST.');
}

$auth  = requireAuth();
$input = getJsonInput();

$icon         = sanitize($input['icon']         ?? '');
$title        = sanitize($input['title']        ?? '');
$detail       = sanitize($input['detail']       ?? '');
$description  = sanitize($input['description']  ?? '');
$displayOrder = (int)   ($input['display_order'] ?? 0);
$isActive     = isset($input['is_active']) ? (int)(bool)$input['is_active'] : 1;

if (empty($title))  { jsonResponse(400, 'error', null, 'Title is required.'); }
if (empty($detail)) { jsonResponse(400, 'error', null, 'Detail is required.'); }

// Types: s s s s i i  (6 params)
$stmt = $conn->prepare(
    "INSERT INTO contact_methods (icon, title, detail, description, display_order, is_active)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param('ssssii', $icon, $title, $detail, $description, $displayOrder, $isActive);

if ($stmt->execute()) {
    $newId = $stmt->insert_id;
    $stmt->close();
    jsonResponse(201, 'success', ['id' => $newId], 'Contact method created successfully.');
} else {
    logError('Contact method create failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to create contact method.');
}
