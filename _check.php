<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Parishes: " . \App\Shared\Models\Parish::count() . "\n";
echo "Locations: " . \App\Features\Locations\Models\Location::count() . "\n";
echo "ServiceOrders: " . \App\Features\ServiceOrders\Models\ServiceOrder::count() . "\n";
echo "Managers: ";
$mgr = \App\Shared\Models\User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin','manager']))->first();
echo $mgr ? $mgr->email . "\n" : "none\n";
echo "Clients: " . \App\Features\Clients\Models\Client::count() . "\n";
echo "Sectors: " . \App\Features\Sectors\Models\Sector::count() . "\n";
echo "ServiceTypes: " . \App\Features\ServiceTypes\Models\ServiceType::count() . "\n";
echo "Equipments: " . \App\Features\Equipments\Models\Equipment::count() . "\n";
