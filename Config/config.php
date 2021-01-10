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

$ini['main'] = parse_ini_file("config.ini",true);
$ini['routes'] = parse_ini_file("routes.ini",true);

define('BASEPATH', $ini['main']['paths']['basepath']);
define('CSSPATH', $ini['main']['paths']['css']);
define('DB',$ini['main']['database']['dbname']);
define('JWTKEY',$ini['main']['secret']['JWT']);

set_exception_handler('exceptionHandler');



function exceptionHandler($e) {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST");
    echo json_encode(array("status" => "500", "message" => $e->getMessage(), "file" => $e->getFile(), "line" => $e->getLine()));
}


?>