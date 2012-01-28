<?php 
// check if file is included
($legal_require_php == 1234) or die();

$con = mysql_connect("<SERVER_ADDRESS>","<USERNAME>","<PASSWORD>");
mysql_set_charset('utf8',$con); // set encoding
$db = mysql_select_db("<DATABASE_NAME>"); // db name

?>