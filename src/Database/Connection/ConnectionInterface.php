<?php

namespace App\Core\Database\Connection;

use PDO;

interface ConnectionInterface
{
    /**
     * Create a new database connection
     * 
     * @return PDO
     */
    public static function make($config);
}
