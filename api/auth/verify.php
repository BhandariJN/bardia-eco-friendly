<?php
/**
 * POST /api/auth/verify
 * Validates JWT token
 */

if (getRequestMethod() !== 'POST') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use POST.');
}

$token = getBearerToken();

if (!$token) {
    jsonResponse(401, 'error', null, 'No token provided.');
}

$decoded = decodeToken($token);

if (!$decoded) {
    jsonResponse(401, 'error', null, 'Invalid or expired token.');
}

jsonResponse(200, 'success', [
    'valid' => true,
    'user_id' => $decoded->user_id,
    'role' => $decoded->role
]);
