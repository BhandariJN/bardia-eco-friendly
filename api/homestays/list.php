<?php
/**
 * GET /api/homestays/list
 * List all homestays
 */

if (getRequestMethod() !== 'GET') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use GET.');
}

$result = $conn->query("SELECT id, name, description, location, price_per_night, max_guests, image_url, is_available, created_at, updated_at FROM homestays ORDER BY created_at DESC");

if (!$result) {
    logError('Homestays list query failed: ' . $conn->error);
    jsonResponse(500, 'error', null, 'Failed to fetch homestays.');
}

$homestays = [];
while ($row = $result->fetch_assoc()) {
    $row['id'] = (int) $row['id'];
    $row['price_per_night'] = (float) $row['price_per_night'];
    $row['max_guests'] = (int) $row['max_guests'];
    $row['is_available'] = (bool) $row['is_available'];
    $homestays[] = $row;
}

jsonResponse(200, 'success', $homestays);
