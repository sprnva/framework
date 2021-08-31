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

    public function getLineContent($err_line, $err_file, $tabId)
    {
        $lineOFfset = $err_line - 15;
        $lineLength = $err_line + 15;
        $lineTxt = file($err_file);
        $active = ($tabId == 1) ? 'active' : '';

        $fileContent = "";
        $fileContent .= "<div class='tab-pane fade show " . $active . "' id='" . $tabId . "' role='tabpanel' aria-labelledby='" . $tabId . "-tab'>";
        $fileContent .= "<div style='padding: 0px 28px;'>";
        $fileContent .= "<p class='text-muted' style='font-size: 18px;font-weight: 300;'><span style='font-weight: 600;'>thrown in</span> <span style='text-decoration: underline;word-break: break-all;'>{$err_file}</span> <span style='font-weight: 600;'>on line </span>{$err_line}</p>";
        $fileContent .= "</div>";
        $fileContent .= "<table style='border-top: 1px solid #dedddd;width: 100%;'>";
        $fileContent .= "<tr>";
        $fileContent .= "<td class='line-number'>&nbsp;</td>";
        $fileContent .= "<td class='line-content'><pre><code>&nbsp;</code></pre></td>";
        $fileContent .= "</tr>";
        for ($x = $lineOFfset; $x < $lineLength; $x++) {
            if (!empty($lineTxt[$x])) {
                if (($err_line - 1) === $x) {
                    $fileContent .= "<tr class='line-err'>";
                    $fileContent .= "<td class='line-number' style='background-color: #73b973 !important;'>" . ($x + 1) . "</td>";
                    $fileContent .= "<td class='line-content'><pre><code>" . $lineTxt[$x] . "</code></pre></td>";
                    $fileContent .= "</tr>";
                } else {
                    $fileContent .= "<tr>";
                    $fileContent .= "<td class='line-number'>" . ($x + 1) . "</td>";
                    $fileContent .= "<td class='line-content'><pre><code>" . $lineTxt[$x] . "</code></pre></td>";
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

    public function scaffold($message = null, $exeption = null, $exceptionClass = null)
    {
        $traceContent = '';
        $fileContent = '';
        $counter = 1;
        $err_trace = $exeption->getTrace();
        foreach ($err_trace as $key => $trace) {

            $active = ($counter == 1) ? 'active' : '';

            $traceContent .= "<a class='nav-link " . $active . "' id='" . $counter . "-tab' data-toggle='pill' href='#" . $counter . "' role='tab' aria-controls='" . $counter . "' aria-selected='true'>" . $trace['file'] . "</a>";

            $fileContent .= $this->getLineContent($trace['line'], $trace['file'], $counter);

            $counter++;
        }

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
