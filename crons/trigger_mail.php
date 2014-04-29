<?php
set_time_limit(0);

//Calculate dynamic path
$CronPath = str_replace("crons/trigger_mail.php",'',$_SERVER["SCRIPT_FILENAME"]);

if ($CronPath!='') {
	include_once ($CronPath . "/modules/config.php");
	include_once ($CronPath . "/modules/DB.php");
} else {
	include_once ("../modules/config.php");
	include_once ("../modules/DB.php");
}

//--- Get all the mails due to be sent today
$SQL = "SELECT `mail_id` FROM `campaign_mail` WHERE `send_start_date` >= '" . date("Y-m-d 00:00:00") . "' AND `send_start_date` <= '" . date("Y-m-d H:i:s")  . "'";

$Response = ExecuteQuery($SQL);

foreach($Response as $id) {
    $mail_ids .= $id['mail_id'] . ",";
}
$mail_ids = rtrim($mail_ids,",");

//--- Queue all recipients that are mean't to receive mails during this time that havent been queued
if($mail_ids!=""){
    
    $SQL = "UPDATE `list` SET `status`='" . STATUS_QUEUED. "', `date_queued` = '" . date("Y-m-d H:i:s") . "' WHERE `status` = '" . STATUS_NOT_SENT . "' AND `mail_id` IN ($mail_ids)";
    if (!$Response = ExecuteQuery($SQL)) {
        echo "<p>No records updated</p>";
    } else {
        echo "<p>" . mysql_affected_rows(). " records updated.</p>";
    }
    
}
?>
