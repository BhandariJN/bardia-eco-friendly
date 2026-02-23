<?php
/**
 * Authentication Middleware
 * Validates JWT token on protected routes
 */

/**
 * Require authentication - call this at the top of protected endpoints
 * Returns the decoded token payload
 */
function requireAuth(): object
{
    $token = getBearerToken();

    if (!$token) {
        jsonResponse(401, 'error', null, 'Access denied. No token provided.');
    }

    $decoded = decodeToken($token);

    if (!$decoded) {
        jsonResponse(401, 'error', null, 'Invalid or expired token.');
    }

    return $decoded;
}
