<?php
/**
 * JWT Utilities
 * Uses firebase/php-jwt for encoding and decoding tokens
 */

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

/**
 * Generate a JWT token
 */
function generateToken(int $userId, string $role): string
{
    $secret = $_ENV['JWT_SECRET'] ?? 'default_secret';
    $expiry = (int) ($_ENV['JWT_EXPIRY'] ?? 3600);

    $payload = [
        'user_id' => $userId,
        'role' => $role,
        'iat' => time(),
        'exp' => time() + $expiry
    ];

    return JWT::encode($payload, $secret, 'HS256');
}

/**
 * Decode and validate a JWT token
 * Returns the decoded payload or null on failure
 */
function decodeToken(string $token): ?object
{
    $secret = $_ENV['JWT_SECRET'] ?? 'default_secret';

    try {
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));
        return $decoded;
    } catch (ExpiredException $e) {
        return null;
    } catch (\Exception $e) {
        return null;
    }
}

/**
 * Extract Bearer token from Authorization header
 */
function getBearerToken(): ?string
{
    $headers = '';

    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $headers = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        if (isset($requestHeaders['Authorization'])) {
            $headers = $requestHeaders['Authorization'];
        }
    }

    if (!empty($headers) && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
        return $matches[1];
    }

    return null;
}
