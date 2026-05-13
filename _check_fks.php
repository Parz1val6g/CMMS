<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = DB::select("SELECT CONSTRAINT_NAME, CONSTRAINT_TYPE FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'attachments'");
foreach ($rows as $r) {
    echo $r->CONSTRAINT_NAME . ' (' . $r->CONSTRAINT_TYPE . ")\n";
}

echo "\n--- Columns ---\n";
$cols = DB::select("SHOW COLUMNS FROM attachments");
foreach ($cols as $c) {
    echo $c->Field . ': ' . $c->Type . ' (' . $c->Null . ')' . "\n";
}
