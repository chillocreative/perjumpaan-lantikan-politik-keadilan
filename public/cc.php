<?php
// Temporary deployment helper - will be removed after use
if (($_GET['t'] ?? '') !== 'Kd9xM2pL7qRnW4vZ') {
    http_response_code(403);
    exit;
}

header('Content-Type: text/plain');
$root = dirname(__DIR__);

// Bootstrap Laravel autoloader so we can use app classes
require $root . '/vendor/autoload.php';

use App\Enums\CategoryType;
use Illuminate\Validation\Rule;

$category = CategoryType::Mpkk;
$positions = $category->positions();

echo "CategoryType::Mpkk->positions():\n";
echo implode("\n", $positions) . "\n\n";

$testValues = ['Pengerusi', 'Timbalan Pengerusi', 'Setiausaha', 'Bendahari', 'AJK'];
foreach ($testValues as $val) {
    echo "in_array('$val'): " . (in_array($val, $positions, true) ? 'PASS' : 'FAIL') . "\n";
}
