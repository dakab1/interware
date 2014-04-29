<?php
include_once ("../modules/DB.php");
include_once ("../modules/utils.php");

//--- Clickatel network status messages
$status = array (
    "001" => "Message unknown",
    "002" => "Message queued",
    "003" => "Delivered to gateway",
    "004" => "Received by recipient",
    "005" => "Error with message",
    "006" => "User cancelled message",
    "007" => "Error delivering message",
    "008" => "OK Message",
    "009" => "Routing error",
    "010" => "Message expired",
    "011" => "Message queued",
    "012" => "Out of credit",
    "014" => "Maximum MT limit exceeded");

if (DEBUG_MODE_CRONS) {
    //--- Log the callback to file for now
    UpdateLog(print_r($_GET,1), "SMS provider response", "sms_delivery_notifications.log");
}

$SQL = "UPDATE sms_sent 
        SET network_status = '" . ($status[$_REQUEST['status']]!="" ? $status[$_REQUEST['status']] : $_REQUEST['status']) . "'
        WHERE network_response = '" . $_REQUEST['apiMsgId'] . "'";

if (!ExecuteQuery($SQL)) {
    
    UpdateLog("sms_callback.php", mysql_error());
    
}
?>
