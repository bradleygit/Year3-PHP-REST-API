<?php
/**
 * Return an Associative Array recordset
 * @author Bradley Slater
 */
class HTMLRecordSet extends RecordSet {
    private $output = "";

    private function makeTable($recordSet) {
        $this->output = "<table>";
        foreach($recordSet as $key=>$row) {
            $this->output.= "<tr>";
            foreach($row as $key2=>$row2){
                $this->output.= "<td>" . $row2 . "</td>";
            }
            $this->output.= "</tr>";
        }
        $this->output.= "</table>";
    }

    /**
     * function to return a record set as an associative array
     * @param $query   string with sql to execute to retrieve the record set
     * @param $params  associative array of params for preparted statement
     * @return string  a multidimensional associative array
     */
    function getHTMLRecordSet($query, $params = null) {
        $stmt = $this->getRecordSet($query, $params);
        $recordSet = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($recordSet)) {
            $this->makeTable($recordSet);
        }

        return $this->output;

    }
}
?>