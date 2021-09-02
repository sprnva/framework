<?php

namespace App\Core\Exception;

class BaseException
{

    public function __construct($message = null, $exception = null, $exceptionClass = null, $getLastError = null)
    {
        $this->message = $message;
        $this->exception = $exception;
        $this->getLastError = $getLastError;
        $this->exceptionClass = ($exceptionClass == null) ? get_class($this) : $exceptionClass;

        if($this->exception != null && $getLastError == null){
            return $this->scaffoldException($this->message, $this->exception, $this->exceptionClass);
        }else{
            return $this->scaffoldError($this->message, $this->getLastError, $this->exceptionClass);
        }
    }

    public function getLineContent($err_line, $err_file, $tabId, $class, $funct)
    {
        $lineOFfset = $err_line - 15;
        $lineLength = $err_line + 15;
        $lineTxt = (file_exists($err_file))?file($err_file):null;
        $active = ($tabId == 1) ? 'active' : '';
        $displayClass = (!empty($class))?$class."::":'';
        $traceFile = $this->selectiveStr($err_file);

        $fileContent = "";
        $fileContent .= "<div class='tab-pane fade show " . $active . "' id='" . $tabId . "' role='tabpanel' aria-labelledby='" . $tabId . "-tab'>";
        $fileContent .= "<div style='padding: 0px 28px;'>";
        $fileContent .= "<p class='text-muted' style='font-size: 14px;margin: 0px;'>". $displayClass . $funct ."</p>";
        $fileContent .= "<p style='font-size: 14px;font-weight: 300;'><span style='word-break: break-all;font-size: 16px'>{$traceFile}</span><span style='font-weight: 600;'>:{$err_line}</span></p>";
        $fileContent .= "</div>";
        $fileContent .= "<table style='border-top: 1px solid #dedddd;width: 100%;'>";
        $fileContent .= "<tr>";
        $fileContent .= "<td class='line-number'>&nbsp;</td>";
        $fileContent .= "<td class='line-content'><pre><code>&nbsp;</code></pre></td>";
        $fileContent .= "</tr>";
        for ($x = $lineOFfset; $x < $lineLength; $x++) {
            if (!empty($lineTxt[$x]) || $x == ($err_line - 1)) {
                if (($err_line - 1) === $x) {
                    $fileContent .= "<tr class='line-err'>";
                    $fileContent .= "<td class='line-number' style='background-color: #73b973 !important;'>" . ($x + 1) . "</td>";
                    $fileContent .= "<td class='line-content'><pre><code>" . sanitizeString($lineTxt[$x], false) . "</code></pre></td>";
                    $fileContent .= "</tr>";
                } else {
                    $fileContent .= "<tr>";
                    $fileContent .= "<td class='line-number'>" . ($x + 1) . "</td>";
                    $fileContent .= "<td class='line-content'><pre><code>" . sanitizeString($lineTxt[$x], false) . "</code></pre></td>";
                    $fileContent .= "</tr>";
                }
            }
        }
        $fileContent .= "<tr>";
        $fileContent .= "<td class='line-number'>&nbsp;</td>";
        $fileContent .= "<td class='line-content'><pre><code>&nbsp;</code></pre></td>";
        $fileContent .= "</tr>";
        $fileContent .= "</table>";
        $fileContent .= "</div>";

        return $fileContent;
    }

    public function selectiveStr($mainString)
    {
        $prefix = "/sprnva";
        $index = strpos($mainString, $prefix) + strlen($prefix);
        $result = substr($mainString, $index);
        return $result;
    }

    public function scaffoldException($message = null, $exeption = null, $exceptionClass = null)
    {
        $traceContent = '';
        $fileContent = '';
        $counter = 1;
        $err_trace = $exeption->getTrace();
        foreach ($err_trace as $trace) {

            $active = ($counter == 1) ? 'active' : '';

            $showClass = (!empty($trace['class']))?"<small class='text-muted mt-2'>".$trace['class']."</small>":"";

            $result = $this->selectiveStr($trace['file']);

            if(!empty($trace['line'])){

                $traceContent .= "<a class='nav-link " . $active . "' id='" . $counter . "-tab' data-toggle='pill' href='#" . $counter . "' role='tab' aria-controls='" . $counter . "' aria-selected='true'><div style='display: flex;flex-direction: row;justify-content: space-between;align-items: center;'><div style=''>" . $result . ":". $trace['line'] . "</div></div>".$showClass."</a>";
            }

            $fileContent .= $this->getLineContent($trace['line'], $trace['file'], $counter, $trace['class'], $trace['function']);

            $counter++;
        }

        $this->generateView($exceptionClass, $message, $traceContent, $fileContent);
    }

    public function scaffoldError($message = null, $exeption  = null, $exceptionClass = null)
    {
        $traceContent = '';
        $fileContent = '';
        $counter = 1;

        $active = ($counter == 1) ? 'active' : '';
        $result = $this->selectiveStr($exeption['file']);

        if(!empty($exeption['line'])){

            $traceContent .= "<a class='nav-link " . $active . "' id='" . $counter . "-tab' data-toggle='pill' href='#" . $counter . "' role='tab' aria-controls='" . $counter . "' aria-selected='true'><div style='display: flex;flex-direction: row;justify-content: space-between;align-items: center;'><div style=''>" . $result . ":". $exeption['line'] . "</div></div></a>";
        }

        $fileContent .= $this->getLineContent($exeption['line'], $exeption['file'], $counter, $exeption['class'], $exeption['function']);
        
        $this->generateView($exceptionClass, $message, $traceContent, $fileContent);
    }

    public function generateView($exceptionClass, $message, $traceContent, $fileContent)
    {
        $title = $message;
        $viewStub = file_get_contents(__DIR__ . "/view/index.php");
        $icon = public_url(' /favicon.ico');
        $css = public_url('/assets/sprnva/css/bootstrap.min.css');
        $jquery = public_url('/assets/sprnva/js/jquery-3.6.0.min.js');
        $popper = public_url('/assets/sprnva/js/popper.min.js');
        $bstrap = public_url('/assets/sprnva/js/bootstrap.min.js');
        $r_uri = $_SERVER['REQUEST_URI'];
        $cur_dir = __DIR__;

        $coat = str_replace(
            [
                '{{$title}}',
                '{{$exceptionClass}}',
                '{{$message}}',
                '{{$traceContent}}',
                '{{$fileContent}}',
                '{{icon}}',
                '{{css}}',
                '{{jquery}}',
                '{{popper}}',
                '{{bstrap}}',
                '{{$r_uri}}',
                '{{$cur_dir}}'
            ],
            [
                $title,
                $exceptionClass,
                $message,
                $traceContent,
                $fileContent,
                $icon,
                $css,
                $jquery,
                $popper,
                $bstrap,
                $r_uri,
                $cur_dir
            ],
            $viewStub
        );

        echo $coat;
        die();
    }
}
