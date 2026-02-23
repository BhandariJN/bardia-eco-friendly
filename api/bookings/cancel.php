<?php
/**
 * DELETE /api/bookings/cancel
 * Cancel a booking (Protected)
 */

if (getRequestMethod() !== 'DELETE') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use DELETE.');
}

$auth = requireAuth();
$input = getJsonInput();

$id = (int) ($input['id'] ?? 0);
if ($id <= 0) {
    jsonResponse(400, 'error', null, 'Valid booking ID is required.');
}

// Check if booking exists
$check = $conn->prepare("SELECT id, status FROM bookings WHERE id = ?");
$check->bind_param('i', $id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    $check->close();
    jsonResponse(404, 'error', null, 'Booking not found.');
}

$booking = $result->fetch_assoc();
$check->close();

if ($booking['status'] === 'cancelled') {
    jsonResponse(400, 'error', null, 'Booking is already cancelled.');
}

// Soft cancel - update status instead of deleting
$stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $stmt->close();
    jsonResponse(200, 'success', null, 'Booking cancelled successfully.');
} else {
    logError('Booking cancel failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to cancel booking.');
}
