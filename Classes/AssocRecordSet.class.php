<?php
/**
 * Return an Associative Array recordset
 * @author Bradley Slater
 */
class AssocRecordSet extends RecordSet {
    /**
     * function to return a record set as an associative array
     * @param $query   string with sql to execute to retrieve the record set
     * @param $params  associative array of params for preparted statement
     * @return string  a multidimensional associative array
     */
    function getAssocRecordSet($query, $params = null) {
        $stmt = $this->getRecordSet($query, $params);
        $recordSet = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $nRecords = count($recordSet);
        return array("count"=>$nRecords, "data"=>$recordSet);
    }
}
?>