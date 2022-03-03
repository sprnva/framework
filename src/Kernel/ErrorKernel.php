<?php

namespace App\Core;

use App\Core\Exception\BaseException;

class Kernel
{
    public static function make()
    {
        register_shutdown_function(function () {

            // always check if we got an error
            $lastError    = error_get_last();

            $fatal_errors = [E_ERROR, E_WARNING, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE, E_STRICT, E_RECOVERABLE_ERROR, E_DEPRECATED, E_USER_DEPRECATED, E_NOTICE];

            // check if the error is in the list above
            if ($lastError && in_array($lastError['type'], $fatal_errors, true)) {

                // check if the error is triggered by the exception
                if ($_SERVER['EXCEPTION'] == 0) {

                    // Let's clean the output buffer
                    ob_clean();

                    // throw the error we received
                    throw new BaseException($lastError['message'], null, null, $lastError);
                }
            }
        });
    }
}
