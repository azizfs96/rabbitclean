<?php
/**
 * Laravel - A PHP Framework
 *
 * This server.php file is used by the PHP built-in web server to serve
 * the application during local development. It emulates Apache's
 * mod_rewrite by returning false when a requested file exists so the
 * built-in server will serve that file directly. Otherwise it forwards
 * the request to the framework's front controller at `public/index.php`.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// If the requested resource exists as a file in the public folder, let the
// built-in PHP server return it directly.
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';
