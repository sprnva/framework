<?php

namespace App\Core;

class Dumper
{
    public static function dump()
    {
        $data = '';
        $data .= '<style> .accordion {background-color: #eee;color: #444;cursor: pointer;padding: 18px;width: 100%;border: none;text-align: left;font-size: 15px;transition: 0.4s;} .drpDwn {cursor: pointer;color: #aaa;} .panel-data {padding: 0 18px;display: none;background-color: transparent;overflow: hidden;border: 0px;} .panel-data1 {padding: 0 18px;display: none;background-color: transparent;overflow: hidden;border: 0px;} .collections {font-size: 12px !important;font-family: monospace;font-weight: 300;background:#18171B;color: #FF8400; padding: 5px;word-break: break-word;margin-top: 10px;} .array_color {color: #4e9cde;} .data-color{color: #56DB3A;} </style>';

        foreach (func_get_args() as $x) {
            if (is_array($x)) {
                $countArray = count($x);
                $data .= "<div class='collections'>";
                if ($countArray < 1) {
                    $data .= "<span class='array_color'>array:{$countArray}</span> []";
                } else {
                    $data .= "<span class='array_color'>array:{$countArray}</span> [<span class='drpDwn'></span>";
                    $data .= static::dumpChild($x);
                }
                $data .= "</div>";
            } else {
                $data .= "<div class='collections'>";
                $data .= "\"<span class='data-color'>{$x}</span>\"<br>";
                $data .= "</div>";
            }
        }

        $data .= ' <script>var acc = document.getElementsByClassName("drpDwn");var i; for(i = 0; i < acc.length; i++){acc[i].innerHTML = "&#9658;";acc[i].addEventListener("click", function() {this.classList.toggle("active");var panel = this.nextElementSibling; if(panel.style.display === "block"){panel.style.display = "none";this.innerHTML = "&#9658;";}else{panel.style.display = "block";this.innerHTML = "&#9660;";}});}</script>';

        return $data;
    }


    public static function dumpChild($x)
    {
        $data = '';
        $counterTest = 1;
        $data .= '<div class="panel-data1">';
        foreach ($x as $key => $value) {
            if (is_array($value)) {
                $numberOfArray = count($value);
                if ($numberOfArray < 1) {
                    $data .= "\"<span class='data-color'>{$key}</span>\" => []<br>";
                } else {
                    $data .= "\"<span class='data-color'>{$key}</span>\" => <span class='array_color'>array:{$numberOfArray}</span> [<span class='drpDwn'" . $counterTest . ">&#9658;</span>";
                    $data .= static::dumpChild($value);
                }
            } else {
                if (is_callable($value)) {
                    $data .= "\"<span class='data-color'>{$key}</span>\" => \"<span class='data-color'>callable()</span>\"<br>";
                } else {
                    $data .= "\"<span class='data-color'>{$key}</span>\" => \"<span class='data-color'>{$value}</span>\"<br>";
                }
            }

            $counterTest++;
        }

        $data .= '</div>]<br>';

        return $data;
    }
}
