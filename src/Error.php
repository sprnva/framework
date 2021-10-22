<?php

namespace App\Core;

class Error
{
    public static function any()
    {
        if (empty($_SESSION['RESPONSE_MSG'])) {
            return [];
        }

        return $_SESSION['RESPONSE_MSG'];
    }

    public static function clear()
    {
        unset($_SESSION['RESPONSE_MSG']);
    }
}
