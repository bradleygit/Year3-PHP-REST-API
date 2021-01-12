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
     * @param $recordSet
     */
    public function __construct($recordSet)
    {
        $url = $_SERVER["REQUEST_URI"];
        $path = parse_url($url)['path'];

        $path = str_replace(BASEPATH, "", $path);
        $pathArr = explode('/', $path);
        $path = (empty($pathArr[0])) ? "main" : $pathArr[0];

        ($path == "api")
            ? $this->api_route($pathArr, $recordSet)
            : $this->html_route($path);

    }

    public function api_route($pathArr, $recordSet)
    {
        $this->type = "JSON";
        $this->page = new JSONPage($pathArr, $recordSet);
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
        return "
<p>Welcome to the Documentation page!</p><br>
<h2>API Endpoints</h2><br>

<div class='endpoint'>
    <h3>/api/</h3>
    <div class='endpointAdditional'>
        <p>Returns all endpoints available</p>
          <a href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api'>Example</a></div>
    </div>
</div>
<div class='endpoint'>
    <h3>/authors</h3>
        <div class='endpointAdditional'>
        <p>Returns all authors</p><a
            href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/authors'>Example</a></div>
    <div class='endpointAdditional'>
        <p>?search=
        <div class='endpointExplain'> Search author</div>
        <a href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/authors?search=jennifer'>Example</a>
    </div>
    <div class='endpointAdditional'>
        <p>?id=
        <div class='endpointExplain'> Search author ID</div>
        <a href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/authors?id=8192'>Example</a></div>
</div>
<div class='endpoint'>
    <h3>/awards</h3>
       <div class='endpointAdditional'>
        <p>Returns all awards</p><a
            href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/awards'>Example</a></div>
    <div class='endpointAdditional'>
        ?search=
        <div class='endpointExplain'>Search for award</div>
        <a href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/awards?search=honorable'>Example</a></div>
</div>
<div class='endpoint'>
    <h3>/rooms</h3>
       <div class='endpointAdditional'>
        <p>Returns all rooms</p><a
            href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/rooms'>Example</a></div>
    <div class='endpointAdditional'>
        ?roomId=
        <div class='endpointExplain'>Find room with a given Id</div>
        <a href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/rooms?roomId=10048'>Example</a></div>
</div>
<div class='endpoint'>
    <h3>/sessions</h3>
    <div class='endpointAdditional'>
        <p>Returns all sessions</p><a href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/sessions'>Example</a></div>
    <div class='endpointAdditional'>
        ?day=
        <div class='endpointExplain'>Search by day</div>
        <a href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/sessions?day=monday'>Example</a></div>

    <div class='endpointAdditional'>
        ?author=
        <div class='endpointExplain'>Search by author name</div>
        <a href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/sessions?author=michael'>Example</a></div>
    <div class='endpointAdditional'>
        ?room=
        <div class='endpointExplain'>Search by room name</div>
        <a href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/sessions?room=AB'>Example</a></div>
</div>
<div class='endpoint'>
    <h3>/slots</h3>
       <div class='endpointAdditional'>
        <p>Returns all slots</p><a
            href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/slots'>Example</a></div>
    <div class='endpointAdditional'>
        ?type=
        <div class='endpointExplain'>Find slots with a given type</div>
        <a href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/slots?type=session'>Example</a></div>
</div>
<div class='endpoint'>
    <h3>/schedule</h3>
        <div class='endpointAdditional'>
        <p>Returns the whole schedule</p><a href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/schedule'>Example</a></div>
    <div class='endpointAdditional'>
        ?day=
        <div class='endpointExplain'>Search by day</div>
        <a href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/schedule?day=monday'>Example</a></div>

</div>
<div class='endpoint'>
    <h3>/login/</h3>
    <div class='endpointAdditional'>
        Returns a JSON web token upon a successful login with admin privileges
        <p class='required'> Requires email and password to be posted</p>
    </div>
</div>

<div class='endpoint'>
    <h3>/update/</h3>
    <div class='endpointAdditional'>
        <div class='endpointExplain'>Allows for a session name to be changed with an Id</div>
        <p class='required'> Requires a valid JSON web token which is issued by logging in</p>

    </div>
</div>
<div class='endpoint'>
    <h3>/content</h3>
       <div class='endpointAdditional'>
        <p>Returns all content</p><a
            href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/content'>Example</a></div>
       <div class='endpointAdditional'>
        ?author=
        <div class='endpointExplain'>Search by authorId</div>
        <a href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/content?author=18395'>Example</a></div>
      <div class='endpointAdditional'>
        ?sessionId=
        <div class='endpointExplain'>Search by authorId</div>
        <a href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/content?sessionId=1565'>Example</a></div>
   
    </div>
</div>

<div class='endpoint'>
    <h3>/chairs</h3>
       <div class='endpointAdditional'>
        <p>Returns all authors chairs</p><a
            href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/chairs'>Example</a></div>
    <div class='endpointAdditional'>
        ?author=
        <div class='endpointExplain'>Search by author</div>
        <a href='http://unn-w17004559.newnumyspace.co.uk/KF6012/part1/api/chairs?author=michael'>Example</a></div>
</div>
  ";
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