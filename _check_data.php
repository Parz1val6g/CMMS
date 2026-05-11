<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Locations\Models\Location;
use App\Shared\Models\User;

echo "Users: " . User::count() . "\n";
echo "Locations: " . Location::count() . "\n";
echo "Service Orders: " . ServiceOrder::count() . "\n\n";

$admin = User::where('email', 'admin@cm.pt')->first();
if ($admin) {
    echo "Admin user: {$admin->first_name} {$admin->last_name} ({$admin->email})\n";
    echo "Admin ID: {$admin->id}\n";
}

echo "\n--- Trying DevelopmentTestSeeder ---\n";
$seeder = new Database\Seeders\DevelopmentTestSeeder();
$seeder->run();
echo "\nAfter DevelopmentTestSeeder:\n";
echo "Service Orders: " . ServiceOrder::count() . "\n";
