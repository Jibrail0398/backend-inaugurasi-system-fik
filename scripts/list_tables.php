<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Default connection: " . config('database.default') . PHP_EOL;
echo "Database name: " . config('database.connections.' . config('database.default') . '.database') . PHP_EOL;

/** @var \Illuminate\Database\Connection $db */
$db = $app['db']->connection();

try {
    $rows = $db->select('SHOW TABLES');
} catch (\Exception $e) {
    echo 'Error running SHOW TABLES: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}

if (empty($rows)) {
    echo "No tables found" . PHP_EOL;
    exit(0);
}

foreach ($rows as $row) {
    $arr = (array) $row;
    echo array_shift($arr) . PHP_EOL;
}
