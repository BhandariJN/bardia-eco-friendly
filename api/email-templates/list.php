<?php
/**
 * GET /api/email-templates/list
 * List all active email templates (Protected)
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/jwt.php';
require_once __DIR__ . '/../../includes/auth.php';

setCorsHeaders();

if (getRequestMethod() !== 'GET') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use GET.');
}

$auth = requireAuth();

$result = $conn->query(
    "SELECT id, name, subject, body_html, description, is_active, created_at, updated_at
     FROM email_templates
     WHERE is_active = 1
     ORDER BY name ASC"
);

if (!$result) {
    logError('Email templates list failed: ' . $conn->error);
    jsonResponse(500, 'error', null, 'Failed to fetch email templates.');
}

$templates = [];
while ($row = $result->fetch_assoc()) {
    $row['id']        = (int)  $row['id'];
    $row['is_active'] = (bool) $row['is_active'];
    $templates[] = $row;
}

jsonResponse(200, 'success', $templates);
