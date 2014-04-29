<?php
set_time_limit(0);

$CronPath = str_replace("crons/send_sms.php",'',$_SERVER["SCRIPT_FILENAME"]);

if ($CronPath!='') {
	include_once ($CronPath . "/modules/config.php");
	include_once ($CronPath . "/modules/DB.php");
} else {
	include_once ("../modules/config.php");
	include_once ("../modules/DB.php");
}

//--- Check if there is a current sms process running
$lock_filename = "locks/sms.lock";
if (file_exists($lock_filename)) {
    
    if (DEBUG_MODE_CRONS) {
        $handle = fopen($lock_filename, "r");
        $content = fread($handle, filesize($lock_filename));
        DebugBox("Cron Running", $content);
        die();
    } else {
        
        die;
        
    }
    
}
        
//--- Get individuals from the list who have a queued status
$SQL = "
        SELECT list.id as list_id, list.sms_id, list.msisdn, list.field1, list.field2, list.field3, list.field4, list.status,
            list.date_queued, sms.* 
        FROM sms, list 
        WHERE list.sms_id=sms.id 
        AND list.status=" . STATUS_QUEUED;
$Response = ExecuteQuery($SQL);

$Message = RetrieveSMS($Response[0]['sms_id']);

foreach ($Response as $SMS) {
    
    if (DEBUG_MODE_CRONS) {
        //debug code
        UpdateLog(debug_print_backtrace(), "Before personalization:" . $Message[0]['text'], "sms.txt");    
    }
    
    //--- Customize the message if any custom fields have been passed
    $Text = PersonalizeMessage($Message[0]['text'], $SMS);
    
    if (DEBUG_MODE_CRONS) {
        //debug code
        UpdateLog(debug_print_backtrace(), "After personalization:" . $Text, "sms.txt");    
    }
    
    //--- Check if the client has enough credits to send sms
    $can_send = file_get_contents ("http://www.example.com/interware_resources/check_sms_credits.php?client=" . urlencode(THISURL));
    
    if ($can_send>0) {
        
        //--- Do a curl get to send the sms
        $network_response = SendSMS ($SMS['msisdn'], $Text);
        
        //--- Decrease sending credits
        file_get_contents ("http://www.example.com/interware_resources/set_sms_credits.php?sent=1&client=" . urlencode(THISURL));
        
    } else {
        
        break; //break out of loop and stop sending
        
    }
    
    DebugBox("Clickatel Response", $SendSMSURL . "<br/>" . $Response);
    
    if (curl_error($ch)=="") {
        
        UpdateLog("after sms send", "---", "sms_benchmark_" . $SMS['sms_id']. ".txt");
        //--- Update the DB and flag the sms as sent
        $SQL = "UPDATE `list` SET `status` = '2' WHERE list.id = '" . $SMS['list_id']. "'";
        if (!$Response = ExecuteQuery($SQL)) {

            //mail("tinashe2001@gmail.com", "error with send_sms.php", "unable to change status \n $SQL"); //enable when live
            die("error updating");

        } else {

            SaveSMSSent ($SMS['sms_id'], $SMS['list_id'], $Text, date("Y-m-d H:i:s"), ltrim($network_response,"ID: "));

        }

        echo "<p>sms sent successfully</p>";
    } else {
        echo curl_error($ch);
        UpdateLog($SendSMSURL, curl_error($ch), "sms_errors.log");
    }
    
    curl_close($ch);
    
}

//--- Remove lock;
if (file_exists($lock_filename)) {
    
    if (DEBUG_MODE_CRONS) {
        DebugBox("Cron process finished", "Finished executing at " . date ("Y-m-d H:i:s"));
    }
    
    if (!unlink($lock_filename)) {
        
        if (DEBUG_MODE_CRONS) {
            DebugBox("Cron process lock", "Unable to unlock sms send cron");
        }  
        
        //--- Email admin
        SendHTMLMail("me@email.addrr", WEBMASTER, "", "Cron error", "Unable to unlock sms process");
        
    }
    
}


?>
