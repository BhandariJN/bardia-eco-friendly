<?php
/**
 * GET /api/contact-methods/list
 * List active contact methods (public, no auth)
 */

if (getRequestMethod() !== 'GET') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use GET.');
}

$result = $conn->query(
    "SELECT id, icon, title, detail, href, description, display_order, is_active
     FROM contact_methods
     ORDER BY display_order ASC, id ASC"
);

if (!$result) {
    logError('Contact methods list failed: ' . $conn->error);
    jsonResponse(500, 'error', null, 'Failed to fetch contact methods.');
}

$methods = [];
while ($row = $result->fetch_assoc()) {
    $row['id']            = (int)  $row['id'];
    $row['display_order'] = (int)  $row['display_order'];
    $row['is_active']     = (bool) $row['is_active'];
    $methods[] = $row;
}

jsonResponse(200, 'success', $methods);
