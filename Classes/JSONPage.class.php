<?php

/**
 * Creates a JSON page based on the parameters
 *
 * @author YOUR NAME
 *
 */
class JSONPage
{
    private $page;
    private $recordset;

    /**
     * @param $pathArr - an array containing the route information
     */
    public function __construct($pathArr)
    {
        $this->recordset = new JSONRecordSet(DB);;
        $path = (empty($pathArr[1])) ? "api" : $pathArr[1];
        switch ($path) {
            case 'api':
                $this->page = $this->json_welcome();
                break;
            case 'authors':
                $this->page = $this->json_receive("SELECT name FROM authors");
                break;
            case 'content':
                $this->page = $this->json_receive("SELECT title, abstract,award FROM content");
                break;
            case 'rooms':
                $this->page = $this->json_receive("SELECT roomId,name FROM rooms");
                break;
            case 'session_types':
                $this->page = $this->json_receive("SELECT typeId,name FROM session_types");
                break;
            case 'sessions_content':
                $this->page = $this->json_receive("SELECT sessionId,contentId FROM sessions_content");
                break;
            case 'sessions':
                $this->page = $this->json_receive("SELECT sessionId,name,typeId,roomId,chairId,slotId FROM sessions");
                break;
            case 'slots':
                $this->page = $this->json_receive("SELECT slotsId,type,dayInt,dayString,startHour,startMinute,endHour,endMinute FROM slots");
                break;
            default:
                $this->page = $this->json_error();
                break;
        }
    }

    private function json_welcome()
    {
        $msg = array("Message" => "Welcome", "Author" => "Bradley Slater","EndPoints"=>array("authors","content","rooms","session_types","sessions_content","sessions","slots"));
        return json_encode($msg);
    }

    private function json_error()
    {
        $msg = array("Message" => "Error, no such endpoint");
        return json_encode($msg);
    }


    private function json_receive($query){
        $params = [];
        return ($this->recordset->getJSONRecordSet($query, $params));
    }

    private function json_authors()
    {
        $query = "SELECT name FROM authors";
        $params = [];
        return ($this->recordset->getJSONRecordSet($query, $params));
    }

    private function json_content()
    {
        $query = "SELECT title, abstract,award FROM content";
        $params = [];
        return ($this->recordset->getJSONRecordSet($query, $params));
    }

    public function get_page()
    {
        return $this->page;
    }
}
?>
