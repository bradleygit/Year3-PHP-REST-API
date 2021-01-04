<?php
/**
 * This router will return a main, documentation or about page
 *
 * @author Bradley Slater
 *
 */
class Router {
    private $page;
    private $type = "HTML";

    /**
     * @param $pageType - can be "main", "documentation" or "about"
     */
    public function __construct() {
        $url = $_SERVER["REQUEST_URI"];
        $path = parse_url($url)['path'];

        $path = str_replace(BASEPATH,"",$path);
        $pathArr = explode('/',$path);
        $path = (empty($pathArr[0])) ? "main" : $pathArr[0];

        ($path == "api")
            ? $this->api_route($pathArr)
            : $this->html_route($path);

    }

    public function api_route($pathArr) {
        $this->type = "JSON";
        $this->page = new JSONPage($pathArr);
    }

    public function html_route($path) {
        $ini['routes'] = parse_ini_file("Config/routes.ini",true);
        $pageInfo = isset($path, $ini['routes'][$path])
            ? $ini['routes'][$path]
            : $ini['routes']['error'];

        $this->page = new WebPageWithNav($pageInfo['title'], $pageInfo['heading1'], $pageInfo['footer']);
        if($pageInfo['heading1'] == "documentation"){

        }
        $this->page->addToBody($pageInfo['text']);
    }



    public function get_type() {
        return $this->type ;
    }

    public function get_page() {
        return $this->page->get_page();
    }
}
?>