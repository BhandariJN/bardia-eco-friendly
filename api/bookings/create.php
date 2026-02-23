<?php
/**
 * POST /api/bookings/create
 * Create a new booking (Protected)
 */

if (getRequestMethod() !== 'POST') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use POST.');
}

$auth = requireAuth();
$input = getJsonInput();

$homestayId = (int) ($input['homestay_id'] ?? 0);
$guestName = sanitize($input['guest_name'] ?? '');
$guestEmail = sanitize($input['guest_email'] ?? '');
$guestPhone = sanitize($input['guest_phone'] ?? '');
$checkIn = sanitize($input['check_in'] ?? '');
$checkOut = sanitize($input['check_out'] ?? '');
$guestsCount = (int) ($input['guests_count'] ?? 1);
$totalPrice = (float) ($input['total_price'] ?? 0);

if ($homestayId <= 0 || empty($guestName) || empty($checkIn) || empty($checkOut)) {
    jsonResponse(400, 'error', null, 'homestay_id, guest_name, check_in, and check_out are required.');
}

// Check if homestay exists
$check = $conn->prepare("SELECT id FROM homestays WHERE id = ?");
$check->bind_param('i', $homestayId);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    $check->close();
    jsonResponse(404, 'error', null, 'Homestay not found.');
}
$check->close();

$stmt = $conn->prepare("INSERT INTO bookings (homestay_id, guest_name, guest_email, guest_phone, check_in, check_out, guests_count, total_price, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', NOW())");
$stmt->bind_param('isssssid', $homestayId, $guestName, $guestEmail, $guestPhone, $checkIn, $checkOut, $guestsCount, $totalPrice);

if ($stmt->execute()) {
    $newId = $stmt->insert_id;
    $stmt->close();
    jsonResponse(201, 'success', ['id' => $newId], 'Booking created successfully.');
} else {
    logError('Booking create failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to create booking.');
}
