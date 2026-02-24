<?php
// Temporary deployment helper - will be removed after use
if (($_GET['t'] ?? '') !== 'Kd9xM2pL7qRnW4vZ') {
    http_response_code(403);
    exit;
}

header('Content-Type: text/plain');
$out = [];
$root = dirname(__DIR__);

// Force git pull
exec('git -C ' . escapeshellarg($root) . ' pull origin main 2>&1', $gitLines, $gitCode);
$out[] = 'git pull: ' . implode(' | ', $gitLines);

// Check CategoryType.php on disk
$enumFile = $root . '/app/Enums/CategoryType.php';
if (file_exists($enumFile)) {
    $src = file_get_contents($enumFile);
    $out[] = 'CategoryType has Mpkk positions: ' . (str_contains($src, "'Pengerusi'") ? 'YES' : 'NO - file is old');
}

// Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    $out[] = 'OPcache: cleared';
} else {
    $out[] = 'OPcache: not available';
}

// Run artisan cache clear
exec('/usr/local/bin/php ' . escapeshellarg($root . '/artisan') . ' optimize:clear 2>&1', $artLines);
$out[] = 'artisan optimize:clear: ' . implode(', ', $artLines);

echo implode("\n", $out) . "\n";
