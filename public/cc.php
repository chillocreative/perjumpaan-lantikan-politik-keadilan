<?php
if (($_GET['t'] ?? '') !== 'Kd9xM2pL7qRnW4vZ') {
    http_response_code(403);
    exit;
}

header('Content-Type: text/plain');
$root = dirname(__DIR__);

// Bootstrap full Laravel
require $root . '/vendor/autoload.php';
$app = require_once $root . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\CategoryType;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

$categoryEnum = CategoryType::Mpkk;
$positions = $categoryEnum->positions();
$allPositions = implode(',', CategoryType::allPositions());

echo "=== Positions ===\n";
echo implode(', ', $positions) . "\n\n";

echo "=== allPositions implode string ===\n";
echo $allPositions . "\n\n";

$testValue = 'Setiausaha';

// Test OLD approach (string-based in:)
$oldValidator = Validator::make(
    ['position_type' => $testValue],
    ['position_type' => ['required', 'string', "in:{$allPositions}"]]
);
echo "OLD in: rule for '$testValue': " . ($oldValidator->fails() ? 'FAILS - ' . $oldValidator->errors()->first() : 'PASSES') . "\n";

// Test NEW approach (Rule::in)
$newValidator = Validator::make(
    ['position_type' => $testValue],
    ['position_type' => ['required', 'string', Rule::in($positions)]]
);
echo "NEW Rule::in for '$testValue': " . ($newValidator->fails() ? 'FAILS - ' . $newValidator->errors()->first() : 'PASSES') . "\n";

// Test all MPKK positions with OLD rule
echo "\n=== All MPKK positions with OLD in: rule ===\n";
foreach (['Pengerusi', 'Timbalan Pengerusi', 'Setiausaha', 'Bendahari', 'AJK'] as $pos) {
    $v = Validator::make(['position_type' => $pos], ['position_type' => ['required', 'string', "in:{$allPositions}"]]);
    echo "'$pos': " . ($v->fails() ? 'FAILS' : 'PASSES') . "\n";
}

// Show which controller class is actually loaded
$ref = new ReflectionMethod(\App\Http\Controllers\QrAttendanceController::class, 'processVerify');
$source = file_get_contents($ref->getFileName());
$startLine = $ref->getStartLine();
$endLine = $ref->getEndLine();
$lines = array_slice(explode("\n", $source), $startLine - 1, $endLine - $startLine + 1);
$methodSrc = implode("\n", $lines);
echo "\n=== processVerify loaded from: {$ref->getFileName()} (lines {$startLine}-{$endLine}) ===\n";
echo "Has Rule::in: " . (str_contains($methodSrc, 'Rule::in') ? 'YES' : 'NO - STILL OLD CODE') . "\n";
echo "Has in:\$allPositions: " . (str_contains($methodSrc, 'in:{$allPositions}') ? 'YES - OLD CODE RUNNING' : 'NO') . "\n";
