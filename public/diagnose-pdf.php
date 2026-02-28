<?php

/**
 * PDF Export Diagnostic Script
 * Upload to public/ on the production server, visit /diagnose-pdf.php in browser.
 * DELETE THIS FILE AFTER DEBUGGING.
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

header('Content-Type: text/plain; charset=utf-8');

echo "=== PDF EXPORT DIAGNOSTIC ===\n\n";

// 1. Environment
echo "--- 1. ENVIRONMENT ---\n";
echo "APP_URL: " . config('app.url') . "\n";
echo "APP_ENV: " . config('app.env') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Laravel Version: " . app()->version() . "\n";
echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'unknown') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'unknown') . "\n";
echo "Script Path: " . __FILE__ . "\n";
echo "\n";

// 2. Required PHP extensions
echo "--- 2. PHP EXTENSIONS ---\n";
$required = ['dom', 'gd', 'mbstring', 'xml', 'json', 'openssl'];
foreach ($required as $ext) {
    $loaded = extension_loaded($ext);
    echo ($loaded ? '[OK]' : '[MISSING]') . " $ext\n";
}
echo "\n";

// 3. Storage directories
echo "--- 3. STORAGE DIRECTORIES ---\n";
$dirs = [
    storage_path() => 'storage/',
    storage_path('fonts') => 'storage/fonts/',
    storage_path('framework/views') => 'storage/framework/views/',
    storage_path('logs') => 'storage/logs/',
    sys_get_temp_dir() => 'sys_get_temp_dir()',
];
foreach ($dirs as $path => $label) {
    $exists = is_dir($path);
    $writable = $exists && is_writable($path);
    echo ($exists ? '[EXISTS]' : '[MISSING]') . ' ' . ($writable ? '[WRITABLE]' : '[NOT WRITABLE]') . " $label ($path)\n";
}
echo "\n";

// 4. DomPDF config
echo "--- 4. DOMPDF CONFIG ---\n";
echo "font_dir: " . config('dompdf.options.font_dir', 'NOT SET') . "\n";
echo "font_cache: " . config('dompdf.options.font_cache', 'NOT SET') . "\n";
echo "temp_dir: " . config('dompdf.options.temp_dir', 'NOT SET') . "\n";
echo "chroot: " . config('dompdf.options.chroot', 'NOT SET') . "\n";
echo "pdf_backend: " . config('dompdf.options.pdf_backend', 'NOT SET') . "\n";
echo "enable_remote: " . (config('dompdf.options.enable_remote') ? 'true' : 'false') . "\n";
echo "\n";

// 5. Routes check
echo "--- 5. ROUTES ---\n";
try {
    $attendanceRoute = route('export.attendance.pdf', ['meeting_id' => 1, 'category' => 'matc']);
    echo "export.attendance.pdf (absolute): $attendanceRoute\n";
    $attendanceRouteRel = route('export.attendance.pdf', ['meeting_id' => 1, 'category' => 'matc'], false);
    echo "export.attendance.pdf (relative): $attendanceRouteRel\n";
} catch (\Throwable $e) {
    echo "[ERROR] export.attendance.pdf: " . $e->getMessage() . "\n";
}
try {
    $membersRoute = route('export.members.pdf', ['category' => 'matc']);
    echo "export.members.pdf (absolute): $membersRoute\n";
    $membersRouteRel = route('export.members.pdf', ['category' => 'matc'], false);
    echo "export.members.pdf (relative): $membersRouteRel\n";
} catch (\Throwable $e) {
    echo "[ERROR] export.members.pdf: " . $e->getMessage() . "\n";
}
echo "\n";

// 6. Ziggy config check
echo "--- 6. ZIGGY CONFIG ---\n";
try {
    $ziggy = new \Tighten\Ziggy\Ziggy();
    $routes = $ziggy->toArray()['routes'] ?? [];
    $exportRoutes = array_filter(array_keys($routes), fn($name) => str_starts_with($name, 'export.'));
    if (empty($exportRoutes)) {
        echo "[WARNING] No export routes found in Ziggy config!\n";
        echo "All route names: " . implode(', ', array_keys($routes)) . "\n";
    } else {
        foreach ($exportRoutes as $name) {
            echo "[OK] $name => " . ($routes[$name]['uri'] ?? 'unknown URI') . "\n";
        }
    }
} catch (\Throwable $e) {
    echo "[ERROR] Ziggy: " . $e->getMessage() . "\n";
}
echo "\n";

// 7. Route cache check
echo "--- 7. ROUTE CACHE ---\n";
$cachedRoutesFile = base_path('bootstrap/cache/routes-v7.php');
if (file_exists($cachedRoutesFile)) {
    echo "[INFO] Routes ARE cached ($cachedRoutesFile)\n";
    echo "[WARNING] If export routes were added after caching, run: php artisan route:cache\n";
} else {
    echo "[OK] Routes are NOT cached (using dynamic resolution)\n";
}
echo "\n";

// 8. Try generating a PDF
echo "--- 8. PDF GENERATION TEST ---\n";
try {
    // Ensure font dir exists
    if (!is_dir(storage_path('fonts'))) {
        mkdir(storage_path('fonts'), 0755, true);
        echo "[FIXED] Created storage/fonts/ directory\n";
    }

    $html = '<html><body><h1>PDF Test</h1><p>Generated at ' . date('Y-m-d H:i:s') . '</p></body></html>';
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'landscape');
    $output = $pdf->output();
    echo "[OK] PDF generated successfully (" . strlen($output) . " bytes)\n";
} catch (\Throwable $e) {
    echo "[ERROR] PDF generation failed!\n";
    echo "Exception: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
echo "\n";

// 9. Try loading the actual attendance view
echo "--- 9. ATTENDANCE PDF VIEW TEST ---\n";
try {
    $viewExists = view()->exists('pdf.attendance');
    echo ($viewExists ? '[OK]' : '[MISSING]') . " pdf.attendance view\n";

    $viewExists2 = view()->exists('pdf.members');
    echo ($viewExists2 ? '[OK]' : '[MISSING]') . " pdf.members view\n";

    if ($viewExists) {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.attendance', [
            'meetingTitle' => 'Test Meeting',
            'meetingDate' => '01-03-2026',
            'categoryLabel' => 'Cabang',
            'isMpkk' => false,
            'rows' => [
                [
                    'name' => 'Test User',
                    'ic_number' => '123456789012',
                    'phone_number' => '0123456789',
                    'address' => 'Test Address',
                    'position_type' => 'AJK',
                    'position_name' => null,
                    'status' => 'Hadir',
                    'absence_reason' => null,
                ],
            ],
        ])->setPaper('a4', 'landscape');
        $output = $pdf->output();
        echo "[OK] Attendance PDF rendered successfully (" . strlen($output) . " bytes)\n";
    }
} catch (\Throwable $e) {
    echo "[ERROR] Attendance PDF view failed!\n";
    echo "Exception: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace (first 5 lines):\n";
    $trace = explode("\n", $e->getTraceAsString());
    echo implode("\n", array_slice($trace, 0, 5)) . "\n";
}
echo "\n";

// 10. Check Laravel log for recent PDF errors
echo "--- 10. RECENT LARAVEL LOG ERRORS ---\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = file($logFile);
    $pdfErrors = [];
    foreach ($lines as $i => $line) {
        if (stripos($line, 'pdf') !== false || stripos($line, 'dompdf') !== false || stripos($line, 'export') !== false) {
            $pdfErrors[] = trim($line);
        }
    }
    if (empty($pdfErrors)) {
        echo "[INFO] No PDF-related errors in laravel.log\n";
    } else {
        echo "Found " . count($pdfErrors) . " PDF-related log entries (last 10):\n";
        foreach (array_slice($pdfErrors, -10) as $err) {
            echo "  " . substr($err, 0, 200) . "\n";
        }
    }
} else {
    echo "[WARNING] laravel.log not found at $logFile\n";
}
echo "\n";

echo "=== END DIAGNOSTIC ===\n";
echo "\n*** DELETE THIS FILE (diagnose-pdf.php) AFTER DEBUGGING ***\n";
