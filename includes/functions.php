<?php
/**
 * Helper Functions
 */

/**
 * Send JSON response and exit
 */
function jsonResponse(int $statusCode, string $status, $data = null, string $message = ''): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');

    $response = ['status' => $status];

    if ($message) {
        $response['message'] = $message;
    }

    if ($data !== null) {
        $response['data'] = $data;
    }

    echo json_encode($response);
    exit;
}

/**
 * Sanitize input string
 */
function sanitize(string $input): string
{
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Get JSON input from request body
 */
function getJsonInput(): array
{
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        jsonResponse(400, 'error', null, 'Invalid JSON input');
    }

    return $data ?? [];
}

/**
 * Set CORS headers for React frontend
 */
function setCorsHeaders(): void
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

/**
 * Get the request method
 */
function getRequestMethod(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD']);
}

/**
 * Log errors to storage/logs
 */
function logError(string $message): void
{
    $logFile = __DIR__ . '/../storage/logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
}
