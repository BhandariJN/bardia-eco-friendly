<?php
/**
 * PUT /api/contact-methods/update
 * Update a contact method card (auth required)
 * Body: { "id":1, "icon":"📞", "title":"Call Us", "detail":"+91 98765 43210",
 *          "description":"...", "display_order":0, "is_active":true }
 */

if (getRequestMethod() !== 'PUT') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use PUT.');
}

$auth  = requireAuth();
$input = getJsonInput();

$id           = (int)   ($input['id']            ?? 0);
$icon         = sanitize($input['icon']          ?? '');
$title        = sanitize($input['title']         ?? '');
$detail       = sanitize($input['detail']        ?? '');
$description  = sanitize($input['description']   ?? '');
$displayOrder = (int)   ($input['display_order'] ?? 0);
$isActive     = isset($input['is_active']) ? (int)(bool)$input['is_active'] : 1;

if ($id <= 0)       { jsonResponse(400, 'error', null, 'Valid id is required.'); }
if (empty($title))  { jsonResponse(400, 'error', null, 'Title is required.'); }
if (empty($detail)) { jsonResponse(400, 'error', null, 'Detail is required.'); }

$check = $conn->prepare("SELECT id FROM contact_methods WHERE id = ?");
$check->bind_param('i', $id);
$check->execute(); $check->store_result();
if ($check->num_rows === 0) { $check->close(); jsonResponse(404, 'error', null, 'Contact method not found.'); }
$check->close();

// Types: s s s s i i i  (7 params)
$stmt = $conn->prepare(
    "UPDATE contact_methods SET icon=?, title=?, detail=?, description=?, display_order=?, is_active=? WHERE id=?"
);
$stmt->bind_param('ssssiii', $icon, $title, $detail, $description, $displayOrder, $isActive, $id);

if ($stmt->execute()) {
    $stmt->close();
    jsonResponse(200, 'success', ['id' => $id], 'Contact method updated successfully.');
} else {
    logError('Contact method update failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to update contact method.');
}
