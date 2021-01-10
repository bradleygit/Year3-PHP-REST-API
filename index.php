<?php

 include 'Config/config.php';

 $recordSet = new JSONRecordSet(DB);
 $page = new Router($recordSet);
 new View($page)
?>

