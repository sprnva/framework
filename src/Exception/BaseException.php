<?php

namespace App\Core\Exception;

use App\Core\App;
use App\Core\Blast;
use App\Core\Filesystem\Filesystem;

class BaseException
{

    public function __construct($message = null, $exception = null, $exceptionClass = null, $getLastError = null)
    {
        $this->message = $message;
        $this->exception = $exception;
        $this->getLastError = $getLastError;
        $this->exceptionClass = ($exceptionClass == null) ? get_class($this) : $exceptionClass;

        $_SERVER['EXCEPTION'] = 1;

        if ($getLastError['type'] == E_PARSE) {
            $_exceptionClass = "ParseError";
        } else {
            $_exceptionClass = $this->exceptionClass;
        }

        if ($this->exception != null && $getLastError == null) {
            return Blast::scaffoldException($this->message, $this->exception, $_exceptionClass);
        } else {
            return Blast::scaffoldError($this->message, $this->getLastError, $_exceptionClass);
        }
    }
}
