<?php

session_start();

use App\Core\App;
use App\Core\BcryptHasher;
use App\Core\Dumper;
use App\Core\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

Request::csrf_token();

/**
 * Require a view.
 *
 * @param  string $name
 * @param  array  $data
 */
function view($name, $data = [])
{
    extract($data);

    if (!file_exists("app/views/{$name}.view.php")) {
        throwException("View [{$name}] not found", new Exception());
    }

    return require "app/views/{$name}.view.php";
}

/**
 * Require a package view.
 *
 * @param  string $path
 * @param  array  $data
 */
function packageView($path, $data = [])
{
    extract($data);

    if (!file_exists("system/{$path}.view.php")) {
        throwException("A package view [{$path}] not found", new Exception());
    }

    return require "system/{$path}.view.php";
}

/**
 * Redirect to a new page.
 *
 * @param  string $path
 */
function redirect($path, $message = [])
{
    $path = App::get('base_url') . $path;
    if (!empty($message)) {
        with_msg($message);
    }

    header("Location: {$path}");
    exit();
}

/**
 * writes a response message.
 *
 * @param  array $message
 */
function with_msg($message = [])
{
    if (!empty($message)) {
        $_SESSION["RESPONSE_MSG"] = $message;
    }
}

/**
 * set the public location
 * 
 * @param string $uri
 */
function public_url($uri = "")
{
    return App::get('base_url') . "/public" . $uri;
}

/**
 * set a new route.
 *
 * @param  string $route
 * @param mixed $data
 */
function route($route, $data = "")
{
    if (!empty($data)) {
        $data = "/{$data}";
    }

    return App::get('base_url') . "{$route}" . $data;
}

/**
 * sanitize strings
 * 
 * @param string $data
 */
function sanitizeString($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);

    return $data;
}

/**
 * This will send an email to a specified email-address
 * 
 * @param mixed $subject
 * @param mixed $body
 * @param array $recipients
 * @param string $redirect_route
 */
function sendMail($subject, $body, $recipients)
{
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = App::get('config')['app']['smtp_host'];
        $mail->SMTPAuth = App::get('config')['app']['smtp_auth'];
        $mail->SMTPAutoTLS = App::get('config')['app']['smtp_auto_tls'];
        $mail->Username = App::get('config')['app']['smtp_username'];
        $mail->Password = App::get('config')['app']['smtp_password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->Port = App::get('config')['app']['smtp_port'];

        //Recipients
        $mail->setFrom(App::get('config')['app']['smtp_username'], 'Sprnva');
        $mail->addAddress($recipients);

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->send();

        $result_msg = [
            "message" => "Message has been sent",
            "status" => "success"
        ];
    } catch (Exception $e) {
        $result_msg = [
            "message" => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}",
            "status" => "danger"
        ];
    }

    return $result_msg;
}

/**
 * display alert message then 
 * clear it instantly on refresh
 * 
 * @param string $type
 */
function alert_msg()
{
    $msg = "";

    if (!empty($_SESSION['RESPONSE_MSG'])) {

        $msg = "<div class='alert alert-" . $_SESSION['RESPONSE_MSG']['status'] . " alert-dismissible fade show' role='alert' style='border-left-width: 4px;'>" . $_SESSION['RESPONSE_MSG']['message'] . "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";

        unset($_SESSION['RESPONSE_MSG']);
    }

    return $msg;
}

/**
 * generate random strings
 * 
 * @param int $length
 */
function randChar($length = 6)
{
    $str = "";
    $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
    $max = count($characters) - 1;
    for ($i = 0; $i < $length; $i++) {
        $rand = mt_rand(0, $max);
        $str .= $characters[$rand];
    }
    return $str;
}

/**
 * This will throw a exeption
 */
function throwException($message, $exeption = '')
{
    packageView('Exceptions/exception', compact('message', 'exeption'));
    exit();
}

/**
 * get the OS where the sprnva runs
 * 
 */
function getOS()
{
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $os_platform  = "Unknown OS Platform";

    $os_array     = array(
        '/windows nt 10/i'      =>  'windows',
        '/windows nt 6.3/i'     =>  'windows',
        '/windows nt 6.2/i'     =>  'windows',
        '/windows nt 6.1/i'     =>  'windows',
        '/windows nt 6.0/i'     =>  'windows',
        '/windows nt 5.2/i'     =>  'windows',
        '/windows nt 5.1/i'     =>  'windows',
        '/windows xp/i'         =>  'windows',
        '/windows nt 5.0/i'     =>  'windows',
        '/windows me/i'         =>  'windows',
        '/win98/i'              =>  'windows',
        '/win95/i'              =>  'windows',
        '/win16/i'              =>  'windows',
        '/macintosh|mac os x/i' =>  'macOS',
        '/mac_powerpc/i'        =>  'macOS',
        '/linux/i'              =>  'linux',
        '/ubuntu/i'             =>  'linux'
    );

    foreach ($os_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $os_platform = $value;
        }
    }

    return $os_platform;
}

/**
 * get the browser where sprnva runs
 * 
 */
function getBrowser()
{
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    global $user_agent;

    $browser        = "Unknown Browser";

    $browser_array = array(
        '/msie/i'      => 'Internet Explorer',
        '/firefox/i'   => 'Firefox',
        '/safari/i'    => 'Safari',
        '/chrome/i'    => 'Chrome',
        '/edge/i'      => 'Edge',
        '/opera/i'     => 'Opera',
        '/netscape/i'  => 'Netscape',
        '/maxthon/i'   => 'Maxthon',
        '/konqueror/i' => 'Konqueror',
        '/mobile/i'    => 'Handheld Browser'
    );

    foreach ($browser_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $browser = $value;
        }
    }

    return $browser;
}

/**
 * this will add a hidden input with csrf token
 * 
 */
function csrf()
{
    return "<input type='hidden' name='csrf_token' value='" . Request::csrf() . "'>";
}

/**
 * get old data of the input
 * 
 */
function old($field)
{
    return Request::old($field);
}

if (!function_exists('dd')) {
    /**
     * die and dump
     * 
     */
    function dd()
    {
        echo call_user_func_array(['App\\Core\\Dumper', 'dump'], func_get_args());
        die();
    }
}

if (!function_exists('db')) {
    /**
     * database connection instance
     * 
     */
    function DB()
    {
        return App::get('database');
    }
}

if (!function_exists('abort')) {
    /**
     * abort and display error message
     * 
     */
    function abort($code, $message = '')
    {
        $data = [
            'message' => ($message == "") ? error_page($code) : $message
        ];
        extract($data);
        return require "system/Error.php";
        die();
    }
}

/**
 * error codes
 * 
 */
function error_page($code)
{
    $codes = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => "I'm a teapot",
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        499 => 'Client Closed Request',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error',
    ];

    if (array_key_exists($code, $codes)) {
        return $codes[$code];
    }
}

/**
 * Hash the given value
 * 
 * @param  string  $value
 * @return string
 */
function bcrypt($value)
{
    $bcryptHaser = new BcryptHasher();
    return $bcryptHaser->make($value);
}

/**
 * Check the given plain value against a hash.
 * 
 * @param  string  $value
 * @param  string  $hashedValue
 * @return bool
 */
function checkHash($value, $hashedValue)
{
    $bcryptHaser = new BcryptHasher();
    return $bcryptHaser->check($value, $hashedValue);
}

// add additional helper functions from the users
require __DIR__ . '/../config/function.helpers.php';
