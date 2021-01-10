<?php

spl_autoload_register("autoloadClasses");
$ini['main'] = parse_ini_file("config.ini",true);
$ini['routes'] = parse_ini_file("routes.ini",true);

define('BASEPATH', $ini['main']['paths']['basepath']);
define('CSSPATH', $ini['main']['paths']['css']);
define('DB',$ini['main']['database']['dbname']);
define('JWTKEY',$ini['main']['secret']['JWT']);



set_exception_handler('exceptionHandler');
set_error_handler('errorHandler');


function errorHandler($errno, $errstr, $errfile, $errline) {
    if ($errno != 2 && $errno != 8) {
        throw new Exception("Fatal Error Detected: [$errno] $errstr line: $errline", 1);
    }
}

function autoloadClasses($className) {
    $filename = "classes\\" . strtolower($className) . ".class.php";
    $filename = str_replace('\\', DIRECTORY_SEPARATOR, $filename);
    if (is_readable($filename)) {
        include_once $filename;
    } else {
        throw new Exception("File not found: " . $className . " (" . $filename . ")");
    }

}

function exceptionHandler($e) {
    $msg = array("status" => "500", "message" => $e->getMessage(), "file" => $e->getFile(), "line" => $e->getLine());
    $user_msg = array("status" => "500", "message" => "Sorry there was an internal server error!");
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST");
    echo json_encode($user_msg);
}

?>