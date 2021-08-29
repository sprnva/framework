<?php

namespace App\Core\Database\Connection\Exception;

use PDOException;
use App\Core\Exception\BaseException;

class ConnectionException extends PDOException
{
    /**
     * Main constructor class which overrides the parent constructor and set the message
     * and the code properties which is optional
     * 
     * @param string $message
     * @param int $code
     * @return void
     */
    public function __construct($message = null, $code = null)
    {
        $this->message = $message;
        $this->code = $code;

        new BaseException($this->message, $this->code, get_class($this));
    }
}
