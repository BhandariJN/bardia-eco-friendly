<?php
/**
 * Entry Point - Routes API Requests
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/jwt.php';
require_once __DIR__ . '/../includes/auth.php';

// Set CORS headers
setCorsHeaders();

// Get request URI and clean it
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = str_replace('/bardia-eco-friendly/public', '', parse_url($requestUri, PHP_URL_PATH));
$basePath = str_replace('/bardia-eco-friendly', '', $basePath);
$route = rtrim($basePath, '/');

// Route mapping
$apiDir = __DIR__ . '/../api';

$routes = [
    // Auth
    '/api/auth/login'        => $apiDir . '/auth/login.php',
    '/api/auth/verify'       => $apiDir . '/auth/verify.php',

    // Homestays
    '/api/homestays/list'    => $apiDir . '/homestays/list.php',
    '/api/homestays/create'  => $apiDir . '/homestays/create.php',
    '/api/homestays/update'  => $apiDir . '/homestays/update.php',
    '/api/homestays/delete'  => $apiDir . '/homestays/delete.php',

    // Bookings
    '/api/bookings/list'     => $apiDir . '/bookings/list.php',
    '/api/bookings/create'   => $apiDir . '/bookings/create.php',
    '/api/bookings/update'   => $apiDir . '/bookings/update.php',
    '/api/bookings/cancel'   => $apiDir . '/bookings/cancel.php',

    // Pages
    '/api/pages/list'        => $apiDir . '/pages/list.php',
    '/api/pages/create'      => $apiDir . '/pages/create.php',
    '/api/pages/update'      => $apiDir . '/pages/update.php',
    '/api/pages/delete'      => $apiDir . '/pages/delete.php',

    // Package Categories
    '/api/package-categories/list'   => $apiDir . '/package-categories/list.php',
    '/api/package-categories/create' => $apiDir . '/package-categories/create.php',
    '/api/package-categories/update' => $apiDir . '/package-categories/update.php',
    '/api/package-categories/delete' => $apiDir . '/package-categories/delete.php',

    // Packages
    '/api/packages/list'          => $apiDir . '/packages/list.php',
    '/api/packages/create'        => $apiDir . '/packages/create.php',
    '/api/packages/update'        => $apiDir . '/packages/update.php',
    '/api/packages/delete'        => $apiDir . '/packages/delete.php',

    // Package Features
    '/api/package-features/list'  => $apiDir . '/package-features/list.php',
    '/api/package-features/save'  => $apiDir . '/package-features/save.php',

    // Comparison
    '/api/comparison/list'        => $apiDir . '/comparison/list.php',
    '/api/comparison/save'        => $apiDir . '/comparison/save.php',

    // Gallery Categories
    '/api/gallery-categories/list'   => $apiDir . '/gallery-categories/list.php',
    '/api/gallery-categories/create' => $apiDir . '/gallery-categories/create.php',
    '/api/gallery-categories/update' => $apiDir . '/gallery-categories/update.php',
    '/api/gallery-categories/delete' => $apiDir . '/gallery-categories/delete.php',

    // Gallery Images
    '/api/gallery-images/list'    => $apiDir . '/gallery-images/list.php',
    '/api/gallery-images/upload'  => $apiDir . '/gallery-images/upload.php',
    '/api/gallery-images/update'  => $apiDir . '/gallery-images/update.php',
    '/api/gallery-images/delete'  => $apiDir . '/gallery-images/delete.php',

    // Contact Methods
    '/api/contact-methods/list'   => $apiDir . '/contact-methods/list.php',
    '/api/contact-methods/create' => $apiDir . '/contact-methods/create.php',
    '/api/contact-methods/update' => $apiDir . '/contact-methods/update.php',
    '/api/contact-methods/delete' => $apiDir . '/contact-methods/delete.php',

    // Contact Submissions
    '/api/contact-submissions/submit'        => $apiDir . '/contact-submissions/submit.php',
    '/api/contact-submissions/list'          => $apiDir . '/contact-submissions/list.php',
    '/api/contact-submissions/update-status' => $apiDir . '/contact-submissions/update-status.php',
    '/api/contact-submissions/delete'        => $apiDir . '/contact-submissions/delete.php',

    // Social Links
    '/api/social-links/list'   => $apiDir . '/social-links/list.php',
    '/api/social-links/create' => $apiDir . '/social-links/create.php',
    '/api/social-links/update' => $apiDir . '/social-links/update.php',
    '/api/social-links/delete' => $apiDir . '/social-links/delete.php',

    // Emails
    '/api/emails/send-reply'   => $apiDir . '/emails/send-reply.php',
    '/api/emails/history'      => $apiDir . '/emails/history.php',

    // Email Templates
    '/api/email-templates/list' => $apiDir . '/email-templates/list.php',
    '/api/email-templates/get'  => $apiDir . '/email-templates/get.php',
];

// Match route
if (isset($routes[$route])) {
    require_once $routes[$route];
} else {
    jsonResponse(404, 'error', null, 'Endpoint not found.');
}
