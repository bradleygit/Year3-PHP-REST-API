<?php
function autoloadClasses($className) {
    $filename = "Classes\\" .$className . ".class.php";
    $filename = str_replace('\\', DIRECTORY_SEPARATOR, $filename);
    if (is_readable($filename)) {
        include_once $filename;
    } else {
        exit("File not found: " . $className . " (" . $filename . ")");
    }

}
spl_autoload_register("autoloadClasses");

$ini = parse_ini_file("config.ini",true);
define('BASEPATH', $ini['paths']['basepath']);
define('CSSPATH', $ini['paths']['css']);
define('DB',$ini['database']['dbname']);
define('JWTKEY',$ini['secret']['JWT']);

set_exception_handler('exceptionHandler');



function exceptionHandler($e) {
    $msg = array("status" => "500", "message" => $e->getMessage(), "file" => $e->getFile(), "line" => $e->getLine());
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST");
    echo json_encode($msg);
}


?>