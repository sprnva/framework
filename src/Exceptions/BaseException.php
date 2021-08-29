<?php

namespace App\Core\Exception;

class BaseException
{

    public function __construct($message = null, $exeption = null, $exceptionClass = null)
    {
        $this->message = $message;
        $this->exeption = $exeption;
        $this->exceptionClass = ($exceptionClass == null) ? get_class($this) : $exceptionClass;

        return $this->scaffold($this->message, $this->exeption, $this->exceptionClass);
    }

    public function scaffold($message = null, $exeption = null, $exceptionClass = null)
    {
        $coat = "";
        $coat .= "<html lang='en'>";
        $coat .= "<head>";
        $coat .= "<meta charset='UTF-8'>";
        $coat .= "<meta http-equiv='X-UA-Compatible' content='IE=edge'>";
        $coat .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
        $coat .= "<link rel='icon' href='" . public_url('/favicon.ico') . "' type='image/ico' />";
        $coat .= "<title>";
        $coat .= "ERROR";
        $coat .= "</title>";
        $coat .= "<link rel='stylesheet' href='" . public_url('/assets/sprnva/css/bootstrap.min.css') . "'>";
        $coat .= "<style>";
        $coat .= "body {";
        $coat .= "background-color: #eef1f4;";
        $coat .= "color: #00096f;";
        $coat .= "}";
        $coat .= "</style>";
        $coat .= "<script src='" . public_url('/assets/sprnva/js/jquery-3.6.0.min.js') . "'></script>";
        $coat .= "<script src='" . public_url('/assets/sprnva/js/popper.min.js') . "'></script>";
        $coat .= "<script src='" . public_url('/assets/sprnva/js/bootstrap.min.js') . "'></script>";
        $coat .= "</head>";
        $coat .= "<div class='container'>";
        $coat .= "<div class='row justify-content-md-center'>";
        $coat .= "<div class='col-md-8'>";
        $coat .= "<div class='card' style='margin-top: 10%;background-color: #fff; border: 0px; border-radius: 3px; box-shadow: 0 4px 5px 0 rgba(0,0,0,0.2);padding: 10px;'>";
        $coat .= "<div class='card-body d-flex flex-column'>";
        $coat .= "<p class='mt-2 mb-0' style='font-size: 18px;font-weight: 500;'>{$message}</p>";
        $exceptions = ($exceptionClass != null) ? "<b>{$exceptionClass}</b><br>" : "";
        $coat .= "<small class='text-muted'>{$exceptions}{$exeption}</small>";
        $coat .= "<small class='text-muted mt-4'>Sprnva blast</small>";
        $coat .= "</div>";
        $coat .= "</div>";
        $coat .= "</div>";
        $coat .= "</div>";
        $coat .= "</div>";
        $coat .= "</body>";
        $coat .= "</html>";

        echo $coat;
        die();
    }
}
