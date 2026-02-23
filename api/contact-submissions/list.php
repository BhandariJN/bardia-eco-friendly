<?php
/**
 * GET /api/contact-submissions/list
 * List all contact form submissions (auth required)
 * Optional filter: ?status=new|read|replied|archived
 * Optional pagination: ?limit=50&offset=0
 */

if (getRequestMethod() !== 'GET') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use GET.');
}

$auth = requireAuth();

$allowedStatuses = ['new', 'read', 'replied', 'archived'];
$status = $_GET['status'] ?? '';
$limit  = max(1, min(200, (int) ($_GET['limit']  ?? 50)));
$offset = max(0, (int) ($_GET['offset'] ?? 0));

$sql    = "SELECT id, full_name, email, phone, num_guests, preferred_package, travel_dates, message, status, created_at FROM contact_submissions";
$params = [];
$types  = '';

if ($status !== '' && in_array($status, $allowedStatuses, true)) {
    $sql    .= " WHERE status = ?";
    $types   = 's';
    $params[] = $status;
}

$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$types   .= 'ii';
$params[] = $limit;
$params[] = $offset;

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    logError('Contact submissions list failed: ' . $conn->error);
    jsonResponse(500, 'error', null, 'Failed to fetch submissions.');
}

$submissions = [];
while ($row = $result->fetch_assoc()) {
    $row['id'] = (int) $row['id'];
    $submissions[] = $row;
}
$stmt->close();

// Count totals per status for badge display
$counts = [];
$cRes = $conn->query("SELECT status, COUNT(*) AS c FROM contact_submissions GROUP BY status");
if ($cRes) {
    while ($cr = $cRes->fetch_assoc()) { $counts[$cr['status']] = (int) $cr['c']; }
}

jsonResponse(200, 'success', ['submissions' => $submissions, 'counts' => $counts]);
