<?php
/**
 * POST /api/homestays/create
 * Create a new homestay (Protected)
 */

if (getRequestMethod() !== 'POST') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use POST.');
}

$auth = requireAuth();
$input = getJsonInput();

// Validate required fields
$name = sanitize($input['name'] ?? '');
$description = sanitize($input['description'] ?? '');
$location = sanitize($input['location'] ?? '');
$pricePerNight = (float) ($input['price_per_night'] ?? 0);
$maxGuests = (int) ($input['max_guests'] ?? 0);
$imageUrl = sanitize($input['image_url'] ?? '');

if (empty($name) || empty($location) || $pricePerNight <= 0) {
    jsonResponse(400, 'error', null, 'Name, location, and valid price_per_night are required.');
}

$stmt = $conn->prepare("INSERT INTO homestays (name, description, location, price_per_night, max_guests, image_url, is_available, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())");
$stmt->bind_param('sssdis', $name, $description, $location, $pricePerNight, $maxGuests, $imageUrl);

if ($stmt->execute()) {
    $newId = $stmt->insert_id;
    $stmt->close();
    jsonResponse(201, 'success', ['id' => $newId], 'Homestay created successfully.');
} else {
    logError('Homestay create failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to create homestay.');
}
