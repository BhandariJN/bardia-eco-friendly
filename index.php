<?php
/**
 * Root Entry Point — Bardiya Eco Friendly
 *
 * Immediately redirects to the CMS login page.
 * Works whether the project is served at the web root or in a subdirectory
 * (e.g., http://localhost/bardiya-eco-friendly/).
 */

// Derive the public base path dynamically so this works in any sub-directory.
// SCRIPT_NAME will be something like /bardiya-eco-friendly/index.php
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);   // normalize on Windows
$basePath   = rtrim(dirname($scriptName), '/');                   // e.g. /bardiya-eco-friendly

// Build the absolute URL to cms/login.php
$scheme     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host       = $_SERVER['HTTP_HOST'];
$loginUrl   = $scheme . '://' . $host . $basePath . '/cms/login.php';

header('Location: ' . $loginUrl, true, 302);
exit;
