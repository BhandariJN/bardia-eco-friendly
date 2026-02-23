<?php
/**
 * POST /api/auth/login
 * Authenticates user and returns JWT token
 */

if (getRequestMethod() !== 'POST') {
    jsonResponse(405, 'error', null, 'Method not allowed. Use POST.');
}

$input = getJsonInput();

$username = sanitize($input['username'] ?? '');
$password = $input['password'] ?? '';

if (empty($username) || empty($password)) {
    jsonResponse(400, 'error', null, 'Username and password are required.');
}

// Query user from database
$stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    jsonResponse(401, 'error', null, 'Invalid credentials.');
}

$user = $result->fetch_assoc();
$stmt->close();

// Verify password
if (!password_verify($password, $user['password'])) {
    jsonResponse(401, 'error', null, 'Invalid credentials.');
}

// Generate JWT
$token = generateToken((int) $user['id'], $user['role']);

jsonResponse(200, 'success', [
    'token' => $token,
    'user' => [
        'id' => (int) $user['id'],
        'username' => $user['username'],
        'role' => $user['role']
    ]
]);
