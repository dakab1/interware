<?php
set_time_limit(0);

$CronPath = str_replace("crons/trigger_sms.php",'',$_SERVER["SCRIPT_FILENAME"]);

if ($CronPath!='') {
	include_once ($CronPath . "/modules/config.php");
	include_once ($CronPath . "/modules/DB.php");
} else {
	include_once ("../modules/config.php");
	include_once ("../modules/DB.php");
}

//--- Get all the mails due to be sent today
$SQL = "SELECT `sms_id` FROM `campaign_sms` WHERE `send_start_date` >= '" . date("Y-m-d 00:00:00") . "' AND `send_start_date` <= '" . date("Y-m-d H:i:s")  . "'";

$Response = ExecuteQuery($SQL);

$sms_ids = "";
foreach($Response as $id) {
    $sms_ids .= $id['sms_id'] . ",";
}
$sms_ids = rtrim($sms_ids,",");

//--- Queue all recipients that are mean't to receive mails during this time that havent been queued
if($sms_ids!=""){
    
    $SQL = "UPDATE `list` SET `status`='" . STATUS_QUEUED. "', `date_queued` = '" . date("Y-m-d H:i:s") . "' WHERE `status` = '" . STATUS_NOT_SENT . "' AND `sms_id` IN ($sms_ids)";
    if (!$Response = ExecuteQuery($SQL)) {
        echo "<p>No records updated</p>";
    } else {
        echo "<p>" . mysqli_affected_rows($connection). " records updated.</p>";
    }
    
}
?>
