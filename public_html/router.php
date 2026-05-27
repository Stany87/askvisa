<?php
/**
 * Router script for PHP built-in development server.
 * Serves both B2C (/) and B2B (/b2b/) on the same port.
 *
 * Replicates the .htaccess rules:
 *   - DirectoryIndex: landing.php, index.php
 *   - RewriteRule: /login/b2b -> partner_access_7f2b.php
 *
 * Usage:
 *   php -S localhost:8000 router.php
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// ── .htaccess rewrite: /login/b2b -> partner_access_7f2b.php ──
if (preg_match('#^/login/b2b/?$#i', $uri)) {
    require __DIR__ . '/partner_access_7f2b.php';
    return true;
}

// ── Serve existing files/static assets directly ──
$requested = __DIR__ . $uri;

if ($uri !== '/' && is_file($requested)) {
    // Let the built-in server handle static files (images, CSS, JS, etc.)
    return false;
}

// ── Directory index resolution ──
if (is_dir($requested)) {
    // Try landing.php first, then index.php, then index.html (matches DirectoryIndex order)
    foreach (['landing.php', 'index.php', 'index.html'] as $index) {
        $candidate = rtrim($requested, '/\\') . '/' . $index;
        if (is_file($candidate)) {
            require $candidate;
            return true;
        }
    }
}

// ── Fallback: 404 ──
http_response_code(404);
echo "<h1>404 Not Found</h1><p>The requested URL was not found on this server.</p>";
return true;
