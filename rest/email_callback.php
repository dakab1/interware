<?php
include_once ("../modules/config.php");
include_once ("../modules/DB.php");

extract($_GET);

//--- Check if mail had already been marked as read
$SQL = "SELECT date_read FROM mail_sent WHERE list_id='$list_id' AND mail_id='$mail_id'";
$Results = ExecuteQuery($SQL);

if ($Results[0]['date_read'] == "0000-00-00 00:00:00" || $Results[0]['date_read'] == ""){

    $SQL = "UPDATE mail_sent SET date_read = ' " . date ("Y-m-d H:i:s") ."' WHERE mail_id = '$mail_id' AND list_id = '$list_id'";
    ExecuteQuery($SQL);

}
?>
