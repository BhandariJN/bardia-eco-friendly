<?php
/**
 * POST /api/social-links/create
 * Create a social link (auth required)
 * Body: { "icon":"📷", "label":"Instagram", "href":"https://instagram.com/...",
 *          "display_order":0, "is_active":true }
 */

if (getRequestMethod() !== 'POST') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use POST.');
}

$auth  = requireAuth();
$input = getJsonInput();

$iconName     = sanitize($input['icon_name']      ?? '');
$label        = sanitize($input['label']         ?? '');
$href         = sanitize($input['href']          ?? '');
$displayOrder = (int)   ($input['display_order'] ?? 0);
$isActive     = isset($input['is_active']) ? (int)(bool)$input['is_active'] : 1;

if (empty($label)) { jsonResponse(400, 'error', null, 'Label is required.'); }
if (empty($href))  { jsonResponse(400, 'error', null, 'href (URL) is required.'); }

// Types: s s s i i  (5 params)
$stmt = $conn->prepare(
    "INSERT INTO social_links (icon_name, label, href, display_order, is_active) VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param('sssii', $iconName, $label, $href, $displayOrder, $isActive);


if ($stmt->execute()) {
    $newId = $stmt->insert_id;
    $stmt->close();
    jsonResponse(201, 'success', ['id' => $newId], 'Social link created successfully.');
} else {
    logError('Social link create failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to create social link.');
}
