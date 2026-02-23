<?php
/**
 * PUT /api/bookings/update
 * Update a booking (Protected)
 */

if (getRequestMethod() !== 'PUT') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use PUT.');
}

$auth = requireAuth();
$input = getJsonInput();

$id = (int) ($input['id'] ?? 0);
if ($id <= 0) {
    jsonResponse(400, 'error', null, 'Valid booking ID is required.');
}

// Check if booking exists
$check = $conn->prepare("SELECT id FROM bookings WHERE id = ?");
$check->bind_param('i', $id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    $check->close();
    jsonResponse(404, 'error', null, 'Booking not found.');
}
$check->close();

$guestName = sanitize($input['guest_name'] ?? '');
$guestEmail = sanitize($input['guest_email'] ?? '');
$guestPhone = sanitize($input['guest_phone'] ?? '');
$checkIn = sanitize($input['check_in'] ?? '');
$checkOut = sanitize($input['check_out'] ?? '');
$guestsCount = (int) ($input['guests_count'] ?? 1);
$totalPrice = (float) ($input['total_price'] ?? 0);
$status = sanitize($input['status'] ?? 'confirmed');

if (empty($guestName) || empty($checkIn) || empty($checkOut)) {
    jsonResponse(400, 'error', null, 'guest_name, check_in, and check_out are required.');
}

$stmt = $conn->prepare("UPDATE bookings SET guest_name = ?, guest_email = ?, guest_phone = ?, check_in = ?, check_out = ?, guests_count = ?, total_price = ?, status = ? WHERE id = ?");
$stmt->bind_param('sssssidsi', $guestName, $guestEmail, $guestPhone, $checkIn, $checkOut, $guestsCount, $totalPrice, $status, $id);

if ($stmt->execute()) {
    $stmt->close();
    jsonResponse(200, 'success', null, 'Booking updated successfully.');
} else {
    logError('Booking update failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to update booking.');
}
