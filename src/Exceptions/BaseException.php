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
        $err_file = $exeption->getFile();
        $err_line = $exeption->getLine();

        $lineOFfset = $err_line - 15;
        $lineLength = $err_line + 15;

        $lineTxt = file($err_file);
        $fileContent = "";
        for($x=$lineOFfset; $x<$lineLength; $x++) { 
            if(($err_line - 1) === $x){
                $fileContent .= "<span style='background-color: green; color: #fff;padding: 2px;'>".$x.$lineTxt[$x]."</span>";
            }else{
                $fileContent .= $x.$lineTxt[$x];
            }
        }
        // $fileContent = file_get_contents($err_file, FALSE, NULL, $lineOFfset, $lineLength);

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
        $coat .= "color: #1a4017;";
        $coat .= "}";
        $coat .= "</style>";
        $coat .= "<script src='" . public_url('/assets/sprnva/js/jquery-3.6.0.min.js') . "'></script>";
        $coat .= "<script src='" . public_url('/assets/sprnva/js/popper.min.js') . "'></script>";
        $coat .= "<script src='" . public_url('/assets/sprnva/js/bootstrap.min.js') . "'></script>";
        $coat .= "</head>";
        $coat .= "<div class='container'>";
        $coat .= "<div class='row justify-content-md-center'>";

        $coat .= "<div class='col-md-12'>";
        $coat .= "<div class='card' style='margin-top: 5%;background-color: #fff; border: 2px solid #e1dfdf; border-radius: 3px; padding: 10px;'>";
        $coat .= "<div class='card-body d-flex flex-column' style='padding: 50px;'>";
        $coat .= "<p class='text-muted' style='margin: 0px;font-size: 18px;''>{$exceptionClass}</p>";
        $coat .= "<p class='' style='font-size: 30px;font-weight: 500;'>{$message}</p>";
        $coat .= "<small class='text-muted' style='font-size: 14px;'>".$_SERVER['REQUEST_URI'] ."</small>";
        $coat .= "</div>";
        $coat .= "</div>";
        $coat .= "</div>";

        $coat .= "<div class='col-md-12'>";
        $coat .= "<div class='card' style='margin-top: 2%;margin-bottom: 5%;background-color: #fff; border: 2px solid #e1dfdf; border-radius: 3px;'>";
        $coat .= "<div class='card-header' style='padding: 15px;background-color: #1e4d1a;color: #fff;'>";
        $coat .= "Sprnva Blast : Stack Trace";
        $coat .= "</div>";
        $coat .= "<div class='card-body d-flex flex-column' style='padding: 50px;'>";
        $coat .= "<p class='text-muted' style='font-size: 18px;font-weight: 300;'><span style='font-weight: 600;'>thrown in</span> {$err_file} <span style='font-weight: 600;'>on line </span>{$err_line}</p>";
        $coat .= "<pre style='border: 1px solid #ddd;'><code style='color: #20371e;'>{$fileContent}</code></pre>";
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
