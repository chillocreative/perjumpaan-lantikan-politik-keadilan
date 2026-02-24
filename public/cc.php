<?php
// Temporary deployment helper - will be removed after use
if (($_GET['t'] ?? '') !== 'Kd9xM2pL7qRnW4vZ') {
    http_response_code(403);
    exit;
}

header('Content-Type: text/plain');
$out = [];
$root = dirname(__DIR__);

// Target files that need recompiling
$files = [
    $root . '/app/Http/Controllers/QrAttendanceController.php',
    $root . '/app/Enums/CategoryType.php',
];

// Try opcache_invalidate() per-file (less restricted than opcache_reset)
foreach ($files as $file) {
    if (function_exists('opcache_invalidate')) {
        $r = opcache_invalidate($file, true);
        $out[] = 'opcache_invalidate(' . basename($file) . '): ' . ($r ? 'OK' : 'FAILED');
    } else {
        $out[] = 'opcache_invalidate: not available';
    }
    // Touch file to update mtime - forces OPcache to recompile if validate_timestamps=1
    touch($file);
    $out[] = 'touch(' . basename($file) . '): ' . (filemtime($file) > 0 ? 'OK mtime=' . filemtime($file) : 'FAILED');
}

// Verify file contents on disk
$enumFile = $root . '/app/Enums/CategoryType.php';
$src = file_get_contents($enumFile);
$out[] = 'CategoryType has Pengerusi: ' . (str_contains($src, "'Pengerusi'") ? 'YES' : 'NO - old file on disk');

$ctrlFile = $root . '/app/Http/Controllers/QrAttendanceController.php';
$src2 = file_get_contents($ctrlFile);
$out[] = 'Controller has Rule::in: ' . (str_contains($src2, 'Rule::in(') ? 'YES' : 'NO - old file on disk');

// artisan optimize:clear
exec('/usr/local/bin/php ' . escapeshellarg($root . '/artisan') . ' optimize:clear 2>&1', $artLines);
$out[] = 'artisan optimize:clear: ' . implode(', ', $artLines);

echo implode("\n", $out) . "\n";
