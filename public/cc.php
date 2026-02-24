<?php
// Temporary deployment helper - will be removed after use
if (($_GET['t'] ?? '') !== 'Kd9xM2pL7qRnW4vZ') {
    http_response_code(403);
    exit;
}

header('Content-Type: text/plain');
$out = [];

// Check if PHP files on disk are updated
$controller = dirname(__DIR__) . '/app/Http/Controllers/QrAttendanceController.php';
if (file_exists($controller)) {
    $src = file_get_contents($controller);
    $out[] = 'Controller has Rule::in fix: ' . (str_contains($src, 'Rule::in(') ? 'YES' : 'NO - files not updated on disk');
}

// Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    $out[] = 'OPcache: cleared';
} else {
    $out[] = 'OPcache: not available';
}

// Run artisan cache clear
$artisan = dirname(__DIR__) . '/artisan';
if (file_exists($artisan)) {
    exec('/usr/local/bin/php ' . escapeshellarg($artisan) . ' optimize:clear 2>&1', $lines);
    $out[] = 'artisan optimize:clear: ' . implode(', ', $lines);
}

echo implode("\n", $out) . "\n";
