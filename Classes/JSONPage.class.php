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
    private $welcome = array("Message" => "Welcome", "Author" => "Bradley Slater", "End Points" => array("authors", "awards", "login", "update", "rooms", "schedule", "chairs", "sessions","slots"));
    private $error = array("status" => "404", "message" => "Error, endpoint not found");

    /**
     * @param $pathArr - an array containing the route information
     * @param $recordSet - passed in jsonrecordset for DB access
     */
    public function __construct($pathArr, $recordSet)
    {
        $this->recordset = $recordSet;
        $path = (empty($pathArr[1])) ? "api" : $pathArr[1];
        switch ($path) {
            case 'api':
                $this->page = $this->jsonEncode($this->welcome);
                break;
            case 'login':
                $this->page = $this->handleLogin();
                break;
            case 'update':
                $this->page = $this->json_update();
                break;
            case 'authors':
                $this->page = $this->build_endPoint(array('search' => "name",'id'=>'authorId','getsessions'=>""), JSONSQLStatements::$getAuthors);
                break;
            case 'awards':
                $this->page = $this->build_endPoint(array('search' => "award"), JSONSQLStatements::$getNonEmptyAwards);
                break;
            case 'rooms':
                $this->page = $this->build_endPoint(array('roomId' => "roomId"), JSONSQLStatements::$getRooms);;
                break;
            case 'schedule':
                $this->page = $this->build_endPoint(array('day' => 'dayString','author'=>'authorName','room'=>'roomName'), JSONSQLStatements::$getSchedule);
                break;
            case 'sessions':
                $this->page = $this->json_NoAdditionalParams(JSONSQLStatements::$getSessions);
                break;
            case 'chairs':
                $this->page = $this->build_endPoint(array('author' => "a.name"), JSONSQLStatements::$getChairs);;
                break;
            case 'slots':
                $this->page = $this->build_endPoint(array('type' => "type"), JSONSQLStatements::$getSlots);;
                break;

            default:
                $this->page = $this->jsonEncode($this->error);
                break;

        }
    }

    /**
     * Takes a query and executes it with no parameters
     */
    public function json_NoAdditionalParams($query)
    {
        return ($this->recordset->getJSONRecordSet($query, []));
    }


    /**
     * this function runs through a map of endpoints and completes any needed sql task
     *
     * @param $endPoints - map that is made up of KEY -> desired endpoint eg. /search and VALUE -> the desired field for to look for in database e.g award
     * @param $query - basic query pulled from JSONSQLStatements class
     * @return mixed - executed query with data
     */
    //
    //
    private function build_endPoint($endPoints, $query)
    {
        $params = [];
        foreach ($endPoints as $key => $value) {
            if (isset($_REQUEST[$key])) {
                switch ($key) {
                    case 'search': //simple replace
                    case 'type':
                    case 'day':
                    case 'author':
                    case 'room':
                        $query = $this->determineClause($endPoints[$key] . " LIKE :term", $query);
                        $params["term"] = $this->sanitiseString("%" . $_REQUEST[$key] . "%");
                        break;
                    case 'id':
                    case 'roomId':
                        $query = $this->determineClause($endPoints[$key] . " = :term", $query);
                        $params["term"] = $this->sanitiseNum($_REQUEST[$key]);
                        break;
                    case 'getsessions':
                        $query = JSONSQLStatements::$getAuthorsWithSessions;
                        break;
                }

                return $this->recordset->getJSONRecordSet($query, $params);
            }
        }
        return $this->recordset->getJSONRecordSet($query, []);
    }


    private function determineClause($conditionToAdd, $query)
    {
        if (strpos($query, 'WHERE')) {
            $query .= " AND " . $conditionToAdd;
        } else {
            $query .= " WHERE " . $conditionToAdd;
        }
        return $query;
    }



    /**
     * json_login
     *
     * handles the login section
     */
    private function handleLogin()
    {
        $input = json_decode(file_get_contents("php://input"));
        $token = array();
        if (isset($input->email) && isset($input->password)) {
            $query = JSONSQLStatements::$getLogin;
            $email = $this->sanitiseString($input->email);
            $password = $this->sanitiseString($input->password);
            $params = ["email" => $email];
            $res = json_decode($this->recordset->getJSONRecordSet($query, $params), true);
            $passwordFound = ($res['count']) ? $res['data'][0]['password'] : null;
            if (password_verify($password, $passwordFound)) {
                $adminStatus = $res['data'][0]['admin'];
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
            return json_encode(array("status" => $status, "message" => $msg));
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
     * allows updating of sessions with a valid token and sessionId
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

        return $this->recordset->getJSONRecordSet(JSONSQLStatements::$updateSessions, ["name" => $this->sanitiseString($input->name), "sessionId" => $this->sanitiseString($input->sessionId)]);
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

    private function jsonEncode($jsonArray)
    {
        return json_encode($jsonArray);
    }

    public function get_page()
    {
        return $this->page;
    }
}

?>
