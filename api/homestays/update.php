<?php
/**
 * PUT /api/homestays/update
 * Update an existing homestay (Protected)
 */

if (getRequestMethod() !== 'PUT') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use PUT.');
}

$auth = requireAuth();
$input = getJsonInput();

$id = (int) ($input['id'] ?? 0);
if ($id <= 0) {
    jsonResponse(400, 'error', null, 'Valid homestay ID is required.');
}

// Check if homestay exists
$check = $conn->prepare("SELECT id FROM homestays WHERE id = ?");
$check->bind_param('i', $id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    $check->close();
    jsonResponse(404, 'error', null, 'Homestay not found.');
}
$check->close();

$name = sanitize($input['name'] ?? '');
$description = sanitize($input['description'] ?? '');
$location = sanitize($input['location'] ?? '');
$pricePerNight = (float) ($input['price_per_night'] ?? 0);
$maxGuests = (int) ($input['max_guests'] ?? 0);
$imageUrl = sanitize($input['image_url'] ?? '');
$isAvailable = (int) ($input['is_available'] ?? 1);

if (empty($name) || empty($location) || $pricePerNight <= 0) {
    jsonResponse(400, 'error', null, 'Name, location, and valid price_per_night are required.');
}

$stmt = $conn->prepare("UPDATE homestays SET name = ?, description = ?, location = ?, price_per_night = ?, max_guests = ?, image_url = ?, is_available = ?, updated_at = NOW() WHERE id = ?");
$stmt->bind_param('sssdiisi', $name, $description, $location, $pricePerNight, $maxGuests, $imageUrl, $isAvailable, $id);

if ($stmt->execute()) {
    $stmt->close();
    jsonResponse(200, 'success', null, 'Homestay updated successfully.');
} else {
    logError('Homestay update failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to update homestay.');
}
