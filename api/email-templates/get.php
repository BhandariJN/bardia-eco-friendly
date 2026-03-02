<?php
/**
 * GET /api/email-templates/get
 * Get a specific email template with variables replaced (Protected)
 * Query: ?template_id=X&submission_id=Y
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/jwt.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/template-engine.php';

setCorsHeaders();

if (getRequestMethod() !== 'GET') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use GET.');
}

$auth = requireAuth();

$templateId   = (int) ($_GET['template_id'] ?? 0);
$submissionId = (int) ($_GET['submission_id'] ?? 0);

if ($templateId <= 0) {
    jsonResponse(400, 'error', null, 'Valid template_id is required.');
}

if ($submissionId <= 0) {
    jsonResponse(400, 'error', null, 'Valid submission_id is required.');
}

// Get template
$stmt = $conn->prepare("SELECT name, subject, body_html FROM email_templates WHERE id = ? AND is_active = 1");
$stmt->bind_param('i', $templateId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    jsonResponse(404, 'error', null, 'Template not found.');
}

$template = $result->fetch_assoc();
$stmt->close();

// Get submission data
$stmt = $conn->prepare(
    "SELECT full_name, email, phone, num_guests, preferred_package, travel_dates, message
     FROM contact_submissions WHERE id = ?"
);
$stmt->bind_param('i', $submissionId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    jsonResponse(404, 'error', null, 'Submission not found.');
}

$submission = $result->fetch_assoc();
$stmt->close();

// Get admin name from JWT
$adminName = 'Admin'; // Default
$userStmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$userStmt->bind_param('i', $auth->user_id);
$userStmt->execute();
$userResult = $userStmt->get_result();
if ($userResult->num_rows > 0) {
    $adminName = $userResult->fetch_assoc()['username'];
}
$userStmt->close();

// Render template with variables
$variables = getSubmissionVariables($submission, $adminName);
$renderedSubject = renderEmailTemplate($template['subject'], $variables);
$renderedBody = renderEmailTemplate($template['body_html'], $variables);

jsonResponse(200, 'success', [
    'template_name' => $template['name'],
    'subject'       => $renderedSubject,
    'body_html'     => $renderedBody
]);
