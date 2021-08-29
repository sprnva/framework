<?php

namespace App\Core\Database\QueryBuilder\Exception;

use Exception;
use App\Core\Exception\BaseException;

class QueryBuilderException extends Exception
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
