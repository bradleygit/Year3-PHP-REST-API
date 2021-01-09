<?php

use Firebase\JWT\JWT;

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
            case 'awards':
                $this->page = $this->json_awards();
                break;
            case 'login':
                $this->page = $this->handleLogin();
                break;
            case 'update':
                $this->page = $this->json_update();
                break;
            case 'rooms':
                $this->page = $this->json_rooms();
                break;
            case 'schedule':
                $this->page = $this->json_schedule();
                break;
            case 'sessions':
                $this->page = $this->json_sessions();
                break;
            case 'chairs':
                $this->page = $this->json_chairs();
                break;
            default:
                $this->page = $this->json_error();
                break;
        }
    }

    private function json_welcome()
    {
        $msg = array("Message" => "Welcome", "Author" => "Bradley Slater", "EndPoints" => array("authors", "awards", "login", "update", "rooms", "schedule", "chairs"));
        return json_encode($msg);
    }

    private function json_error()
    {

        $msg = array("status" => "404", "message" => "Error, endpoint not found");
        return json_encode($msg);
    }

    public function json_sessions(){
        $query = "SELECT s.name as sessionName,a.name FROM sessions s inner join authors a on s.chairId = a.authorId ";
        $params = [];
        return ($this->recordset->getJSONRecordSet($query, $params));
    }
    public function json_chairs()
    {
        $query = "SELECT s.name as SessionName, a.name as AuthorName FROM sessions s INNER JOIN  authors a  ON s.chairId = a.authorId";
        $params = [];
        if (isset($_REQUEST['author'])) {
            $query .= " WHERE a.name like :term";
            $term = $this->sanitiseString("%" . $_REQUEST['author'] . "%");
            $params = ["term" => $term];
        }
        return ($this->recordset->getJSONRecordSet($query, $params));
    }


    public function json_rooms()
    {
        $query = "SELECT name FROM rooms";
        $params = [];
        if (isset($_REQUEST['roomid'])) {
            $query .= " WHERE roomId like :term";
            $term = $this->sanitiseString("%" . $_REQUEST['roomid'] . "%");
            $params = ["term" => $term];
        }
        return ($this->recordset->getJSONRecordSet($query, $params));
    }

    private function json_schedule()
    {
        $query = "SELECT slots.type,slots.dayString as day,slots.startHour,slots.startMinute,slots.endHour,slots.endMinute,s.name,a.name as authorName,r.name as roomName
                        FROM slots  inner JOIN sessions s on slots.slotId = s.slotId 
                            inner join authors a on s.chairId = a.authorId
                            inner join rooms r on s.roomId = r.roomId"; //lots of joins going on here !
        $params = [];
        if (isset($_REQUEST['day'])) {
            $query .= " WHERE dayString LIKE :term";
            $term = $this->sanitiseString("%" . $_REQUEST['day'] . "%");
            $params = ["term" => $term];
        } elseif (isset($_REQUEST['type'])) {
            $query .= " WHERE type = :term";
            $term = $this->sanitiseString($_REQUEST['type']);
            $params = ["term" => $term];

        } elseif (isset($_REQUEST['timestart'])) {
            $query .= " WHERE startHour = :termA and  startMinute = :termB";
            $numberA = $_REQUEST['timestart'];
            $numberB = $_REQUEST['timestart'];
            if (strlen($_REQUEST['timestart']) >= 4) {
                $numberA = $this->trimNumbers($numberA, true);
                $numberB = $this->trimNumbers($numberB, false);
            }
            $numberATerm = $this->sanitiseString($numberA);
            $numberBTerm = $this->sanitiseString($numberB);
            $params = ["termA" => $numberATerm, "termB" => $numberBTerm];

        } elseif (isset($_REQUEST['timeend'])) {
            $query .= " WHERE endHour = :termA and  endMinute = :termB";
            $numberA = $_REQUEST['timeend'];
            $numberB = $_REQUEST['timeend'];
            if (strlen($_REQUEST['timeend']) >= 4) {
                $numberA = $this->trimNumbers($numberA, true);
                $numberB = $this->trimNumbers($numberB, false);
            }

            $numberATerm = $this->sanitiseString($numberA);
            $numberBTerm = $this->sanitiseString($numberB);
            $params = ["termA" => $numberATerm, "termB" => $numberBTerm];
        }

        return ($this->recordset->getJSONRecordSet($query, $params));
    }

    private function trimNumbers($numberString, $isHour)
    {
        if ($isHour) {
            $numStringFormat = $numberString[0] . $numberString[1];
            if ($numStringFormat < 10 && strlen($numStringFormat) >= 2) {
                return $numStringFormat[1]; //take 0 off start e.g. 01
            } else {
                return $numStringFormat;
            }
        } else {
            $numStringFormat = $numberString[2] . $numberString[3];
            if ($numStringFormat < 10 && strlen($numStringFormat) >= 2) {
                return $numStringFormat[1];//take 0 off end eg 01
            } else {
                return $numStringFormat;
            }
        }
    }


    private function json_awards()
    {
        $query = "SELECT title,abstract,award FROM content WHERE award != ''";
        $params = [];
        if (isset($_REQUEST['search'])) {
            $query .= "and award LIKE :term";
            $term = $this->sanitiseString("%" . $_REQUEST['search'] . "%");
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
            $term = $this->sanitiseString("%" . $_REQUEST['search'] . "%");
            $params = ["term" => $term];
        } elseif (isset($_REQUEST['id'])) {
            $query .= " WHERE authorId = :term";
            $term = $this->sanitiseNum($_REQUEST['id']);
            $params = ["term" => $term];
        }
        elseif (isset($_REQUEST['getsessions'])) {
                $query = "SELECT a.name as authorName,s.name as sessionName ,sl.dayString as day, sl.startHour,sl.startMinute,sl.endHour,sl.endMinute, rooms.name as roomName
                            FROM authors a left join sessions s on a.authorId = s.chairId left join rooms on s.roomId = rooms.roomId left join slots sl on s.slotId = sl.slotId";
        }

        return ($this->recordset->getJSONRecordSet($query, $params));
    }

    /**
     * json_login
     *
     * @todo this method can be improved
     */
    private function handleLogin()
    {


        $input = json_decode(file_get_contents("php://input"));
        $token = array();

        if (isset($input->email) && isset($input->password)) {
            $query = "SELECT username,password,admin FROM users WHERE email = :email";
            $email = $this->sanitiseString($input->email);
            $password = $this->sanitiseString($input->password);
            $params = ["email" => $email];
            $res = json_decode($this->recordset->getJSONRecordSet($query, $params), true);
            $passwordFound = ($res['count']) ? $res['data'][0]['password'] : null;
            $adminStatus = $res['data'][0]['admin'];
            if (password_verify($password, $passwordFound)) {
                $msg = "User authorised. Welcome " . $email;
                $status = 200;
                $token = $this->getToken($email, $res['data'][0]['username']);
                $jwtKey = JWTKEY;
                $token = JWT::encode($token, $jwtKey);
                return json_encode(array("status" => $status, "message" => $msg, "token" => $token, "adminStatus" => $adminStatus));
            } else {
                $msg = "username or password are invalid";
                $status = 401;
                return json_encode(array("status" => $status, "message" => $msg, "token" => $token));
            }
        } else {
            $msg = "Invalid request. Username and password required";
            $status = 400;
            return json_encode(array("status" => $status, "message" => $msg, "token" => $token));
        }

    }

    function getToken($username, $email)
    {
        $token['email'] = $email;
        $token['username'] = $username;
        $token['iat'] = time();
        $token['exp'] = time() + 60 * 60;;
        return $token;
    }

    /**
     * json_update
     *
     * @todo this method can be improved
     */
    private function json_update()
    {
        $input = json_decode(file_get_contents("php://input"));


        if (!$input) {
            return json_encode(array("status" => 400, "message" => "Invalid request"));
        }
        if (is_null($input->token)) {
            return json_encode(array("status" => 401, "message" => "Not authorised"));
        }
        if (is_null($input->name) || is_null($input->sessionId)) {
            return json_encode(array("status" => 400, "message" => "Invalid request"));
        }

        try {
            $jwtKey = JWTKEY;
            JWT::decode($input->token, $jwtKey, array('HS256'));
        } catch (UnexpectedValueException $e) {
            return json_encode(array("status" => 401, "message" => $e->getMessage()));
        }

        $query = "UPDATE sessions SET name = :name WHERE sessionId = :sessionId";
        $name = $this->sanitiseString($input->name);
        $Id = $this->sanitiseString($input->sessionId);

        $params = ["name" => $name, "sessionId" => $Id];
        return $this->recordset->getJSONRecordSet($query, $params);
    }

//an arbitrary max length of 20 is set
    private function sanitiseString($x)
    {
        return substr(trim(filter_var($x, FILTER_SANITIZE_STRING)), 0, 20);
    }

//an arbitrary max range of 100000 is set
    private function sanitiseNum($x)
    {
        return filter_var($x, FILTER_VALIDATE_INT, array("options" => array("min_range" => 0, "max_range" => 100000)));
    }

    public function get_page()
    {
        return $this->page;
    }
}

?>
