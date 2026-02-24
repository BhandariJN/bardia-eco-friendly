<?php
/**
 * GET /api/emails/history
 * Get email history for a contact submission (Protected)
 * Query: ?submission_id=X
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/jwt.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/mailer.php';

setCorsHeaders();

if (getRequestMethod() !== 'GET') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use GET.');
}

$auth = requireAuth();

$submissionId = (int) ($_GET['submission_id'] ?? 0);

if ($submissionId <= 0) {
    jsonResponse(400, 'error', null, 'Valid submission_id query parameter is required.');
}

$history = getEmailHistory($submissionId);

jsonResponse(200, 'success', $history);
