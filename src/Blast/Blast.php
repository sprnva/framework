<?php

namespace App\Core;

use App\Core\App;
use App\Core\Kernel;
use App\Core\Filesystem\Filesystem;

class Blast
{
    public static function listen()
    {
        Kernel::make();
    }

    public static function getLineContent($err_line, $err_file, $tabId, $class, $funct)
    {
        $self = new static;
        $lineOFfset = $err_line - 15;
        $lineLength = $err_line + 15;
        $lineTxt = (file_exists($err_file)) ? file($err_file) : null;
        $active = ($tabId == 1) ? 'active' : '';
        $displayClass = (!empty($class)) ? $class . "::" : '';
        $traceFile = $self::selectiveStr($err_file);

        $fileContent = "";
        $fileContent .= "<div class='tab-pane fade show " . $active . "' id='" . $tabId . "' role='tabpanel' aria-labelledby='" . $tabId . "-tab'>";
        $fileContent .= "<div style='padding: 0px 28px;'>";
        $fileContent .= "<p class='text-muted' style='font-size: 13px;margin: 0px;font-weight: 500;'>" . $displayClass . $funct . "</p>";
        $fileContent .= "<p style='font-size: 14px;font-weight: 300;'><span style='word-break: break-all;font-size: 15px;color: #949FAF;'>{$traceFile}</span><span style='font-weight: 600;color: #949FAF;'>:{$err_line}</span></p>";
        $fileContent .= "</div>";
        $fileContent .= "<div class='content-scrolls' style='overflow: hidden;overflow-x: auto;overflow-y: auto;padding: 0px;'><table style='border-top: 0px solid #dedddd;width: 100%;'>";

        for ($x = $lineOFfset; $x < $lineLength; $x++) {
            if (!empty($lineTxt[$x]) || $x == ($err_line - 1)) {
                if (($err_line - 1) === $x) {
                    $fileContent .= "<tr class='line-err'>";
                    $fileContent .= "<td class='line-number' style='/*background-color: #73b973 !important;*/background-color: #78464a !important;'>" . ($x + 1) . "</td>";
                    $fileContent .= "<td class='line-content'><pre><code class='hljs language-php'>" . sanitizeString($lineTxt[$x], false, false) . "</code></pre></td>";
                    $fileContent .= "</tr>";
                } else {
                    $fileContent .= "<tr class='_line'>";
                    $fileContent .= "<td class='line-number'>" . ($x + 1) . "</td>";
                    $fileContent .= "<td class='line-content'><pre><code class='hljs language-php'>" . sanitizeString($lineTxt[$x], false, false) . "</code></pre></td>";
                    $fileContent .= "</tr>";
                }
            }
        }

        $fileContent .= "</table></div>";
        $fileContent .= "</div>";

        return $fileContent;
    }

    public static function selectiveStr($mainString)
    {
        $prefix = App::get('base_url');
        return strstr($mainString, substr($prefix, 1));
    }

    public static function scaffoldException($message = null, $exeption = null, $exceptionClass = null)
    {
        $self = new static;

        $traceContent = '';
        $fileContent = '';
        $counter = 1;
        $err_trace = $exeption->getTrace();
        foreach ($err_trace as $trace) {

            $active = ($counter == 1) ? 'active' : '';

            $showClass = (!empty($trace['function'])) ? "<small class='text-muted mt-2' style='font-size: 96%; font-weight: 400;'>" . $trace['function'] . "</small>" : "";

            $result = $self::selectiveStr($trace['file']);

            if (!empty($trace['line'])) {

                $traceContent .= "<a class='nav-link " . $active . "' id='" . $counter . "-tab' data-toggle='pill' href='#" . $counter . "' role='tab' aria-controls='" . $counter . "' aria-selected='true'><div style='display: flex;flex-direction: row;justify-content: space-between;align-items: center;color: #b7b7b7;font-weight: 500;'><div style=''>" . $result . ":" . $trace['line'] . "</div></div>" . $showClass . "</a>";
            }

            $fileContent .= $self::getLineContent($trace['line'], $trace['file'], $counter, $trace['class'], $trace['function']);

            $counter++;
        }

        $self::generateView($exceptionClass, $message, $traceContent, $fileContent);
    }

    public static function scaffoldError($message = null, $exeption  = null, $exceptionClass = null)
    {
        $self = new static;

        $traceContent = '';
        $fileContent = '';
        $counter = 1;

        $file = $exeption['file'];
        $line = $exeption['line'];
        $_class = $exeption['class'];
        $_function = $exeption['function'];

        $active = ($counter == 1) ? 'active' : '';
        $result = $self::selectiveStr($file);

        if (!empty($file)) {

            $traceContent .= "<a class='nav-link " . $active . "' id='" . $counter . "-tab' data-toggle='pill' href='#" . $counter . "' role='tab' aria-controls='" . $counter . "' aria-selected='true'><div style='display: flex;flex-direction: row;justify-content: space-between;align-items: center;color: #b7b7b7;font-weight: 500;'><div style=''>" . $result . ":" . $line . "</div></div></a>";
        }

        $fileContent .= $self::getLineContent($line, $file, $counter, $_class, $_function);

        $self::generateView($exceptionClass, $message, $traceContent, $fileContent);
    }

    public static function generateView($exceptionClass, $message, $traceContent, $fileContent)
    {
        $title = $message;
        $requestedURI = $_SERVER['REQUEST_URI'];
        $requestedfullUri = $_SERVER['SCRIPT_URI'];
        $fullURI = str_replace($requestedURI, '', $requestedfullUri);
        $base_url = $fullURI; //'/' . App::get('config')['app']['base_url'];
        $viewStub = Filesystem::get(__DIR__ . "/view/index.php");
        $icon = $base_url . '/vendor/sprnva/framework/src/Blast/assets/favicon.ico';
        $css = $base_url . '/vendor/sprnva/framework/src/Blast/assets/css/bootstrap.min.css';
        $hjscss = $base_url . '/vendor/sprnva/framework/src/Blast/assets/css/default.min.css';
        $hjsscript = $base_url . '/vendor/sprnva/framework/src/Blast/assets/js/highlight.min.js';
        $jquery = $base_url . '/vendor/sprnva/framework/src/Blast/assets/js/jquery-3.6.0.min.js';
        $popper = $base_url . '/vendor/sprnva/framework/src/Blast/assets/js/popper.min.js';
        $bstrap = $base_url . '/vendor/sprnva/framework/src/Blast/assets/js/bootstrap.min.js';
        $r_uri = $_SERVER['REQUEST_URI'];
        $cur_dir = __DIR__;

        $app_version = "Sprnva v" . appversion() . " (PHP v" . phpversion() . ")";

        $coat = str_replace(
            [
                '{{$title}}',
                '{{$exceptionClass}}',
                '{{$message}}',
                '{{$traceContent}}',
                '{{$fileContent}}',
                '{{icon}}',
                '{{css}}',
                '{{hjscss}}',
                '{{hjsscript}}',
                '{{jquery}}',
                '{{popper}}',
                '{{bstrap}}',
                '{{$r_uri}}',
                '{{$cur_dir}}',
                '{{$app_version}}'
            ],
            [
                $title,
                $exceptionClass,
                $message,
                $traceContent,
                $fileContent,
                $icon,
                $css,
                $hjscss,
                $hjsscript,
                $jquery,
                $popper,
                $bstrap,
                $r_uri,
                $cur_dir,
                $app_version
            ],
            $viewStub
        );

        echo $coat;
        die();
    }
}
