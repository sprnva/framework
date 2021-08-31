<?php

function scaffold($message = null, $exeption = null, $exceptionClass = null)
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
    $coat .= "color: #050d62;";
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
    $coat .= "<p class='text-muted' style='margin: 0px;font-size: 21px;''>{$exceptionClass}</p>";
    $coat .= "<p class='' style='font-size: 30px;font-weight: 500;'>{$message}</p>";
    $coat .= "<small class='text-muted'>".$_SERVER['REQUEST_URI'] ."</small>";
    $coat .= "</div>";
    $coat .= "</div>";
    $coat .= "</div>";

    $coat .= "<div class='col-md-12'>";
    $coat .= "<div class='card' style='margin-top: 2%;margin-bottom: 5%;background-color: #fff; border: 2px solid #e1dfdf; border-radius: 3px;'>";
    $coat .= "<div class='card-header' style='padding: 15px;background-color: #1e4d1a;color: #fff;'>";
    $coat .= "Sprnva Blast : Stack Trace";
    $coat .= "</div>";
    $coat .= "<div class='card-body d-flex flex-column' style='padding: 50px;'>";
    $coat .= "<p class='text-muted' style='font-size: 18px;font-weight: 300;'>{$exeption}</p>";
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

scaffold('SQLSTATE[42S02]: Base table or view not found', 'test', "Illuminate\Database\QueryException");