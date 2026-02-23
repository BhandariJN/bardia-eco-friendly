<?php
/**
 * GET /api/package-features/list
 * List features for a specific package (public, no auth)
 * Query: ?package_id=1
 */

if (getRequestMethod() !== 'GET') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use GET.');
}

$packageId = (int) ($_GET['package_id'] ?? 0);

if ($packageId <= 0) {
    jsonResponse(400, 'error', null, 'Valid package_id query parameter is required.');
}

$stmt = $conn->prepare(
    "SELECT id, package_id, feature_text, display_order
     FROM package_features
     WHERE package_id = ?
     ORDER BY display_order ASC, id ASC"
);
$stmt->bind_param('i', $packageId);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    logError('Package features list failed: ' . $conn->error);
    jsonResponse(500, 'error', null, 'Failed to fetch package features.');
}

$features = [];
while ($row = $result->fetch_assoc()) {
    $row['id']            = (int) $row['id'];
    $row['package_id']    = (int) $row['package_id'];
    $row['display_order'] = (int) $row['display_order'];
    $features[] = $row;
}
$stmt->close();

jsonResponse(200, 'success', $features);
