<?php
include_once("modules/config.php");
include_once("modules/utils.php");
include_once("modules/DB.php");

extract($_GET);

switch ($a) {
    
    case "sms_list"://Print SMS Recipient List
        $filename = "sms_recipient_list_" . $sms_id . "_" . date("Y-m-d_H_i_s") . ".csv";
        $data = RetrieveRecipientsBySMS($sms_id);
    break;

    case "email_list"://Print SMS Recipient List
        $filename = "email_recipient_list_" . $sms_id . "_" . date("Y-m-d_H_i_s") . ".csv";
        $data = RetrieveRecipientsByEmail ($eid);
    break;
}

if (DEBUG_MODE) {
    
    DebugBox("Data array", print_r($data,1));
    die;
} 
GenerateCSV($filename, $data);

?>
