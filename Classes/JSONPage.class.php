<?php

/**
 * Creates a JSON page based on the parameters
 *
 * @author Bradley Slater
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
                $this->page = $this->json_authors();
                break;
            case 'content':
                $this->page = $this->json_content();
                break;
            case 'rooms':
                $this->page = $this->json_receive("SELECT roomId,name FROM rooms");
                break;
            case 'schedule':
                $this->page = $this->json_schedule("SELECT typeId,name FROM session_types");
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

    private function json_schedule()    {
        $query ="SELECT type,dayInt,dayString,startHour,startMinute,endHour,endMinute from slots";
        $params = [];
        if (isset($_REQUEST['day'])) {
            $query .= " WHERE dayString LIKE :term";
            $term = $this->sanitiseString("%".$_REQUEST['day']."%");
            $params = ["term" => $term];
        }
        elseif (isset($_REQUEST['type'])) {
                $query .= " WHERE type = :term";
                $term = $this->sanitiseString($_REQUEST['type']);
                $params = ["term" => $term];

        }
        elseif (isset($_REQUEST['timestart'])) {
            $query .= " WHERE startHour like :termA and  startMinute like :termB";
            $numberA = substr($_REQUEST['timestart'], 0, 1);
            $numberB = substr($_REQUEST['timestart'], 1, 3);
            $numberATerm = $this->sanitiseNum($numberA);
            $numberBTerm = $this->sanitiseNum($numberB);
            $params = ["termA" => $numberATerm,"termB" =>$numberBTerm];

        }

        return ($this->recordset->getJSONRecordSet($query, $params));
    }


    private function json_content()
    {
        $query ="SELECT title,abstract,award FROM content";
        $params = [];
        if (isset($_REQUEST['search'])) {
            $query .= " WHERE name LIKE :term";
            $term = $this->sanitiseString("%".$_REQUEST['search']."%");
            $params = ["term" => $term];
        }
        elseif (isset($_REQUEST['id'])) {
            $query .= " WHERE authorId = :term";
            $term = $this->sanitiseNum($_REQUEST['id']);
            $params = ["term" => $term];
        }

        return ($this->recordset->getJSONRecordSet($query, $params));
    }
    private function json_authors()
    {
        $query = "SELECT name FROM authors";
        $params = [];
        if (isset($_REQUEST['search'])) {
            $query .= " WHERE name LIKE :term";
            $term = $this->sanitiseString("%".$_REQUEST['search']."%");
            $params = ["term" => $term];
        } else {
            if (isset($_REQUEST['id'])) {
                $query .= " WHERE authorId = :term";
                $term = $this->sanitiseNum($_REQUEST['id']);
                $params = ["term" => $term];
            }
        }
        return ($this->recordset->getJSONRecordSet($query, $params));
    }

//an arbitrary max length of 20 is set
    private function sanitiseString($x) {
        return substr(trim(filter_var($x, FILTER_SANITIZE_STRING)), 0, 20);
    }

//an arbitrary max range of 100000 is set
    private function sanitiseNum($x) {
        return filter_var($x, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>0, "max_range"=>100000)));
    }

    public function get_page()
    {
        return $this->page;
    }
}
?>
