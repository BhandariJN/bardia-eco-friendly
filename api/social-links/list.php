<?php
/**
 * GET /api/social-links/list
 * List active social links (public, no auth)
 */

if (getRequestMethod() !== 'GET') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use GET.');
}

$result = $conn->query(
    "SELECT id, icon_name, label, href, display_order, is_active
     FROM social_links
     ORDER BY display_order ASC, id ASC"
);


if (!$result) {
    logError('Social links list failed: ' . $conn->error);
    jsonResponse(500, 'error', null, 'Failed to fetch social links.');
}

$links = [];
while ($row = $result->fetch_assoc()) {
    $row['id']            = (int)  $row['id'];
    $row['display_order'] = (int)  $row['display_order'];
    $row['is_active']     = (bool) $row['is_active'];
    $links[] = $row;
}

jsonResponse(200, 'success', $links);
