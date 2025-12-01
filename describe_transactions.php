<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::select('DESCRIBE transactions');
$required = [];
foreach ($columns as $col) {
    if ($col->Null === 'NO' && $col->Default === null && $col->Extra !== 'auto_increment') {
        $required[] = $col->Field;
    }
}
echo "Required fields: " . implode(', ', $required) . "\n";
