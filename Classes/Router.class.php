<?php

/**
 * This router will return a main, documentation or about page
 *
 * @author Bradley Slater
 *
 */
class Router
{
    private $page;
    private $type = "HTML";

    /**
     * @param $pageType - can be "main", "documentation" or "about"
     */
    public function __construct($recordSet)
    {
        $url = $_SERVER["REQUEST_URI"];
        $path = parse_url($url)['path'];

        $path = str_replace(BASEPATH, "", $path);
        $pathArr = explode('/', $path);
        $path = (empty($pathArr[0])) ? "main" : $pathArr[0];

        ($path == "api")
            ? $this->api_route($pathArr,$recordSet)
            : $this->html_route($path);

    }

    public function api_route($pathArr,$recordSet)
    {
        $this->type = "JSON";
        $this->page = new JSONPage($pathArr,$recordSet);
    }

    public function html_route($path)
    {
        $ini['routes'] = parse_ini_file("Config/routes.ini", true);
        $pageInfo = isset($path, $ini['routes'][$path])
            ? $ini['routes'][$path]
            : $ini['routes']['error'];
        $this->page = new WebPageWithNav($pageInfo['title'], $pageInfo['heading1'], $pageInfo['footer']);
        if (strpos(strtolower($pageInfo['title']), "documentation")) {
            $this->page->addToBody($this->getDocumentation());
        } else {
            $this->page->addToBody($pageInfo['text']);
        }
    }

    public function getDocumentation()
    {
        $text = "<p>Welcome to the Documentation page!</p><br>
                <h2>API Endpoints</h2><br><ul>
                
                <div class = 'endpoint'>
               <h3>/api/</h3>
               <div class = 'endpointAdditional'>
               <p>Returns all endpoints available</p>
               </div>
                </div>
                
                <div class = 'endpoint'>
                <h3>/authors</h3>
                 <div class = 'endpointAdditional'>
               <p>?search=<div class='endpointExplain'> Search author</div> </p></div>
               <div class = 'endpointAdditional'>
               <p>?id=<div class='endpointExplain'> Search author ID</div> </p> </div>
               
            </div>
            
            
             <div class = 'endpoint'>
              <h3>/schedule</h3> 
              <div class = 'endpointAdditional'>
               ?day= 
               <div class='endpointExplain'>Search by day</div>  </div>
                </div>
                
                
                <div class = 'endpoint'>
                <h3>/login/</h3>
                 <div class = 'endpointAdditional'>
                 <p class = 'required'> Requires email and password to be posted</p>
                 </div>
                 </div>
                 
                  <div class = 'endpoint'>
                <h3>/update/</h3>
                 <div class = 'endpointAdditional'>
                 <p class = 'required'> Requires a valid JSON web token which is issued by logging in</p>
                 </div>
                </div>
                
                 <div class = 'endpoint'>
                <h3>/chairs</h3>
                 <div class = 'endpointAdditional'>
                  ?author=
               <div class='endpointExplain'>Search by author</div>  </div>
                 </div>
                </iv>
                </div>   
                </ul>";


        return $text;
    }


    public function pageDetermine()
    {

    }

    public function get_type()
    {
        return $this->type;
    }

    public function get_page()
    {
        return $this->page->get_page();
    }
}

?>