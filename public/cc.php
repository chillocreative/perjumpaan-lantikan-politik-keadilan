<?php
if (($_GET['t'] ?? '') !== 'Kd9xM2pL7qRnW4vZ') {
    http_response_code(403);
    exit;
}

header('Content-Type: text/plain');
$root = dirname(__DIR__);

require $root . '/vendor/autoload.php';
$app = require_once $root . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

// Simulate the exact same request the browser sends
$request = Request::create('/mpkk', 'POST', [
    'name' => 'TEST',
    'ic_number' => '780912345678',
    'phone_number' => '0190002222',
    'address' => 'TEST',
    'position_type' => 'Setiausaha',
    'mpkk_name' => 'MPKK KG SELAMAT UTARA',
    'status' => 'hadir',
    'absence_reason' => null,
    'website' => '',
    '_ft' => Crypt::encryptString((string) now()->timestamp),
]);
$request->headers->set('Accept', 'application/json');

// Call the controller directly (bypass middleware)
$controller = $app->make(\App\Http\Controllers\QrAttendanceController::class);

echo "=== Calling publicVerify('mpkk') directly ===\n\n";

try {
    // Use DB transaction to avoid creating actual records
    \Illuminate\Support\Facades\DB::beginTransaction();
    $response = $controller->publicVerify($request, 'mpkk');
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Body: " . $response->getContent() . "\n";
    \Illuminate\Support\Facades\DB::rollBack();
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "VALIDATION FAILED:\n";
    foreach ($e->errors() as $field => $messages) {
        echo "  $field: " . implode(', ', $messages) . "\n";
    }
    \Illuminate\Support\Facades\DB::rollBack();
} catch (\Throwable $e) {
    echo "EXCEPTION: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    \Illuminate\Support\Facades\DB::rollBack();
}
