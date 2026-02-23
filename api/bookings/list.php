<?php
/**
 * GET /api/bookings/list
 * List all bookings (Protected)
 */

if (getRequestMethod() !== 'GET') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use GET.');
}

$auth = requireAuth();

$result = $conn->query("SELECT b.id, b.homestay_id, h.name AS homestay_name, b.guest_name, b.guest_email, b.guest_phone, b.check_in, b.check_out, b.guests_count, b.total_price, b.status, b.created_at FROM bookings b LEFT JOIN homestays h ON b.homestay_id = h.id ORDER BY b.created_at DESC");

if (!$result) {
    logError('Bookings list query failed: ' . $conn->error);
    jsonResponse(500, 'error', null, 'Failed to fetch bookings.');
}

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $row['id'] = (int) $row['id'];
    $row['homestay_id'] = (int) $row['homestay_id'];
    $row['guests_count'] = (int) $row['guests_count'];
    $row['total_price'] = (float) $row['total_price'];
    $bookings[] = $row;
}

jsonResponse(200, 'success', $bookings);
