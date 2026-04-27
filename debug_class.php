<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $reflection = new ReflectionClass(\App\Providers\Filament\ControlPanelPanelProvider::class);
    echo "Class loaded successfully.\n";
    echo "Parent class: " . $reflection->getParentClass()->getName() . "\n";

    $method = $reflection->getMethod('panel');
    echo "Method 'panel' found.\n";

    $parameters = $method->getParameters();
    foreach ($parameters as $parameter) {
        echo "Parameter: " . $parameter->getName() . ", Type: " . $parameter->getType() . "\n";
    }

    echo "Return type: " . $method->getReturnType() . "\n";

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
