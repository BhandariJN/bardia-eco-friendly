<?php
/**
 * POST /api/emails/send-reply
 * Send email reply to contact submission (Protected)
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/jwt.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/mailer.php';

setCorsHeaders();

if (getRequestMethod() !== 'POST') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use POST.');
}

$auth = requireAuth();
$input = getJsonInput();

$submissionId = (int) ($input['submission_id'] ?? 0);
$subject      = sanitize($input['subject'] ?? '');
$bodyHtml     = $input['body_html'] ?? ''; // Don't sanitize yet, will be done in mailer
$bodyPlain    = $input['body_plain'] ?? null;

// Validation
if ($submissionId <= 0) {
    jsonResponse(400, 'error', null, 'Valid submission_id is required.');
}

if (empty($subject) || strlen($subject) < 5) {
    jsonResponse(400, 'error', null, 'Subject must be at least 5 characters.');
}

if (strlen($subject) > 500) {
    jsonResponse(400, 'error', null, 'Subject must not exceed 500 characters.');
}

if (empty($bodyHtml) || strlen($bodyHtml) < 10) {
    jsonResponse(400, 'error', null, 'Email body must be at least 10 characters.');
}

if (strlen($bodyHtml) > 500000) {
    jsonResponse(400, 'error', null, 'Email body is too large (max 500KB).');
}

// Sanitize HTML
$bodyHtml = sanitizeEmailHtml($bodyHtml);

// Send email
$result = sendReplyEmail(
    $submissionId,
    $subject,
    $bodyHtml,
    $bodyPlain,
    $auth->user_id
);

if ($result['success']) {
    jsonResponse(200, 'success', [
        'email_id' => $result['email_id'],
        'sent_at'  => date('Y-m-d H:i:s')
    ], 'Email sent successfully.');
} else {
    jsonResponse(500, 'error', null, $result['message']);
}
