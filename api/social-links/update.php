<?php
/**
 * PUT /api/social-links/update
 * Update a social link (auth required)
 * Body: { "id":1, "icon":"📷", "label":"Instagram", "href":"...", "display_order":0, "is_active":true }
 */

if (getRequestMethod() !== 'PUT') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use PUT.');
}

$auth  = requireAuth();
$input = getJsonInput();

$id           = (int)   ($input['id']            ?? 0);
$iconName     = sanitize($input['icon_name']      ?? '');
$label        = sanitize($input['label']         ?? '');
$href         = sanitize($input['href']          ?? '');
$displayOrder = (int)   ($input['display_order'] ?? 0);
$isActive     = isset($input['is_active']) ? (int)(bool)$input['is_active'] : 1;

if ($id <= 0)      { jsonResponse(400, 'error', null, 'Valid id is required.'); }
if (empty($label)) { jsonResponse(400, 'error', null, 'Label is required.'); }
if (empty($href))  { jsonResponse(400, 'error', null, 'href (URL) is required.'); }

$check = $conn->prepare("SELECT id FROM social_links WHERE id = ?");
$check->bind_param('i', $id); $check->execute(); $check->store_result();
if ($check->num_rows === 0) { $check->close(); jsonResponse(404, 'error', null, 'Social link not found.'); }
$check->close();

// Types: s s s i i i  (6 params)
$stmt = $conn->prepare(
    "UPDATE social_links SET icon_name=?, label=?, href=?, display_order=?, is_active=? WHERE id=?"
);
$stmt->bind_param('sssiii', $iconName, $label, $href, $displayOrder, $isActive, $id);


if ($stmt->execute()) {
    $stmt->close();
    jsonResponse(200, 'success', ['id' => $id], 'Social link updated successfully.');
} else {
    logError('Social link update failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to update social link.');
}
