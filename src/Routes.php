<?php

if (file_exists(__DIR__ . '/../../fortify/src/routes/auth.php')) {
    require __DIR__ . '/../../fortify/src/routes/auth.php';
} else {
    if (file_exists(__DIR__ . '/../../../../config/routes/auth.php')) {
        require __DIR__ . '/../../../../config/routes/auth.php';
    }
}

if (file_exists(__DIR__ . '/database/migrations/routes/migration.php')) {
    require __DIR__ . '/database/migrations/routes/migration.php';
}

if (file_exists(__DIR__ . '/../../../../config/routes/web.php')) {
    require __DIR__ . '/../../../../config/routes/web.php';
}
