<?php

if (file_exists(__DIR__ . '/Database/Migration/routes/migration.php')) {
    require __DIR__ . '/Database/Migration/routes/migration.php';
}

if (file_exists(__DIR__ . '/../../../../config/routes/')) {
    $routesDirectory = array_diff(scandir(__DIR__ . '/../../../../config/routes/'), array('.', '..'));
    foreach ($routesDirectory as $route) {
        require __DIR__ . '/../../../../config/routes/' . $route;
    }
}
