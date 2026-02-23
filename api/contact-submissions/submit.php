<?php
/**
 * POST /api/contact-submissions/submit
 * Public contact form submission (no auth required)
 * Body: { "full_name":"...", "email":"...", "phone":"...", "num_guests":"2 Guests",
 *          "preferred_package":"...", "travel_dates":"...", "message":"..." }
 */

if (getRequestMethod() !== 'POST') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use POST.');
}

$input = getJsonInput();

$fullName         = sanitize($input['full_name']         ?? '');
$email            = sanitize($input['email']             ?? '');
$phone            = sanitize($input['phone']             ?? '');
$numGuests        = sanitize($input['num_guests']        ?? '');
$preferredPackage = sanitize($input['preferred_package'] ?? '');
$travelDates      = sanitize($input['travel_dates']      ?? '');
$message          = sanitize($input['message']           ?? '');

if (empty($fullName)) { jsonResponse(400, 'error', null, 'Full name is required.'); }
if (empty($email))    { jsonResponse(400, 'error', null, 'Email is required.'); }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { jsonResponse(400, 'error', null, 'Invalid email address.'); }
if (empty($phone))    { jsonResponse(400, 'error', null, 'Phone number is required.'); }
if (empty($numGuests)){ jsonResponse(400, 'error', null, 'Number of guests is required.'); }
if (empty($message))  { jsonResponse(400, 'error', null, 'Message is required.'); }

// Types: s s s s s s s  (7 params)
$stmt = $conn->prepare(
    "INSERT INTO contact_submissions (full_name, email, phone, num_guests, preferred_package, travel_dates, message)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param('sssssss', $fullName, $email, $phone, $numGuests, $preferredPackage, $travelDates, $message);

if ($stmt->execute()) {
    $newId = $stmt->insert_id;
    $stmt->close();
    jsonResponse(201, 'success', ['id' => $newId], 'Your enquiry has been submitted. We will be in touch shortly.');
} else {
    logError('Contact submission failed: ' . $stmt->error);
    $stmt->close();
    jsonResponse(500, 'error', null, 'Failed to submit enquiry. Please try again.');
}
