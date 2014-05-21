<?php
set_time_limit(0);

$CronPath = str_replace("crons/send_mail.php",'',$_SERVER["SCRIPT_FILENAME"]);

if ($CronPath!='') {
	include_once ($CronPath . "/modules/config.php");
	include_once ($CronPath . "/modules/DB.php");
	include_once ($CronPath . "/modules/utils.php");
} else {
	include_once ("../modules/config.php");
	include_once ("../modules/DB.php");
	include_once ("../modules/utils.php");
}

$SentCount = 0;

//--- Check if there is a current email process running
$lock_filename = "locks/email.lock";
$sleep_lock_filename = "locks/email_sleep.lock";        
    
if (file_exists($lock_filename)) {
    
    if (DEBUG_MODE_CRONS) {
        $handle = fopen($lock_filename, "r");
        $content = fread($handle, filesize($lock_filename));
        fclose ($handle);
        DebugBox("Cron already Running", $content);
        die();
    } else {
        
        die;
        
    }
    
}

if (file_exists($sleep_lock_filename)) {
    
    $handle = fopen($sleep_lock_filename, "r");
    $time_stamp = fread($handle, filesize($sleep_lock_filename));
    fclose ($handle);
    
    if (DEBUG_MODE_CRONS) {
        
        DebugBox("Sleep lock enabled", "unlock will occur at " . date("Y-m-d H:i:s",$time_stamp));
        UpdateLog("send_map.php", "Sleep lock enabled, unlock will occur at " . date("Y-m-d H:i:s",$time_stamp));
        
    } 
    
    if ($time_stamp <= time()) {

        //--- Remove lock;
        if (DEBUG_MODE_CRONS) {
            DebugBox("Mail sleep finished", "Finished sleeping at " . date ("Y-m-d H:i:s"));
            UpdateLog("send_mail.php", "Finished sleeping at " . date ("Y-m-d H:i:s"));
        }

        if (!unlink($sleep_lock_filename)) {

            if (DEBUG_MODE_CRONS) {
                DebugBox("Mail sleep lock", "Unable to wake email send cron. Please delete file $sleep_lock_filename manually");
                UpdateLog("send_mail.php", "Unable to wake email send cron. Please delete file $sleep_lock_filename manually");
            } 

            //--- Email admin
            SendHTMLMail("my@email.addr", WEBMASTER, "", "Cron error", "Unable to wake email process. Please delete $sleep_lock_filename manually");

        }

    } else {
        
        if (DEBUG_MODE_CRONS) {
            
            UpdateLog("send_mail.php", "Sleep at " . date("Y-m-d H:i:s"));
            
        }
        
        die();
    }
    
}
        
//--- Get individuals from the list who have a queued status
$SQL = "SELECT list.id as list_id, list.mail_id, list.email, list.field1, list.field2, list.field3, list.field4, list.status,
            list.date_queued, mail.* 
        FROM mail, list 
        WHERE list.mail_id=mail.id 
        AND list.status=" . STATUS_QUEUED;
$Response = ExecuteQuery($SQL);

//--- Create a log file so that no duplicate processes spawn and send duplicate emails
if (is_array($Response)) {
    
    $handle = fopen("locks/email.lock", "w");
    fwrite($handle, date("Y-m-d H:i:s") . "\nStart sending");
    fclose($handle);
    
}

foreach (@$Response as $Mail) {
    
    //--- Check if the hourly email limit hasnt been reached
    $resource = file_get_contents("http://www.example.com/interware_resources/check_email_limit.php");
    
    //--- Set a lock so that cron doesnt run until an hour later
    /*
    if($resource!="1"){

        if (DEBUG_MODE_CRONS) {
            echo "<p>Sleep for " . ($NextHour - time()) . " seconds...</p>";
            UpdateLog("send_mail.log", "<p>Current time " . date("Y-m-d H:i:s") . " . Next send in an hour</p>" , "resources.log");
        }

        $handle = fopen($sleep_lock_filename, "w");
        fwrite($handle, time() + 300); //an hour later
        fclose($handle);
        
        break; //stop looping

    } 
    */
    
    //--- Check to see if the email has attachments
    $Attachments = RetrieveMailAttachments($Mail['id']);
    
    if (DEBUG_MODE_CRONS) {
        UpdateLog("before email send", "---", "email_benchmark_" . $Mail['mail_id']. ".txt");
    }
    
    $processed_message = PersonalizeMessage ($Mail['message'],$Mail);
    
    if(!SendHTMLMail(stripslashes($Mail['from_address']), stripslashes($Mail['email']), stripslashes($Mail['bcc_address']), stripslashes($Mail['subject']), $processed_message,(is_array($Attachments) ? $Attachments : false), $Mail['list_id'], $Mail['mail_id'])) {
        
        //--- Update mail status and mark us error
        $SQL = "UPDATE list SET status = 3 WHERE id=" . $Mail['list_id'];
        ExecuteQuery($SQL);
                
        if (DEBUG_MODE_CRONS) {
            DebugBox("Mail send error", "<p>Failed to send email, posible opted out or mail server is down or no sendmail installed on localhost</p>");
            UpdateLog(print_r($Mail,1), "Failed to send email, posible opted out or mail server is down or no sendmail installed on localhost");
        }
        
    } else {
        
        //--- Update the email sent counter on the server resources
        //file_get_contents("http://www.example.com/interware_resources/set_email_limit.php?sent=1");
            
        $SentCount++;
        
        if (DEBUG_MODE_CRONS) {
            UpdateLog("after email send", "---", "email_benchmark_" . $Mail['mail_id']. ".txt");
        }
        
        //--- Update the DB and flag the email as sent
        $SQL = "UPDATE `list` SET `status` = '2' WHERE list.id = '" . $Mail['list_id']. "';";
        //$SQL .= "UPDATE campaign_mail SET status = '" . STATUS_QUEUED . "';";
        
        if (!$Response = ExecuteQuery($SQL)) {
            
            if (DEBUG_MODE_CRONS) {
                DebugBox("Update send status", "Failed to update campaign send status");
            }
            
            //--- Send email and log error to file
            SendHTMLMail("my@email.addr", WEBMASTER, $bcc_address, "Error sending scheduled mail", "An error has occured sending scheuled mail. Please contact RDM support");
            UpdateLog(debug_print_backtrace(), mysql_error());
            
            die(); //dont continue to execute in the even of this error because it might result in duplicate emails being sent. Email admin
            
        } else {
            
            if(!SaveMailSent ($Mail['mail_id'], $Mail['list_id'], $Mail['message'], date("Y-m-d H:i:s"))) {
                
               //echo some error 
            }
            
        }
        
        if (DEBUG_MODE_CRONS) {
            
            DebugBox("Mail send status","Email sent successfully");
            
        }
        
    }
    
    if (EMAIL_SEND_RATE > 0) {
        
        sleep(EMAIL_SEND_RATE);
        
    }
}

//--- Remove lock;
if (file_exists($lock_filename)) {
    
    if (DEBUG_MODE_CRONS) {
        DebugBox("Cron process finished", "Finished executing at " . date ("Y-m-d H:i:s"));
    }
    
    if (!unlink($lock_filename)) {
        
        if (DEBUG_MODE_CRONS) {
            DebugBox("Cron process lock", "Unable to unlock email send cron");
        } 
        
        //--- Email admin
        SendHTMLMail("my@email.addr", WEBMASTER, "", "Cron error", "Unable to unlock email process");
        
    }
    
}

//--- Update campaign, mark as completed
/*
if (count($Response) == $SentCount && $SentCount != 0) {
    
    //--- Update mail status
    $SQL = "UPDATE campaign_mail SET status='" . STATUS_SENT. "'";
    if (!ExecuteQuery($SQL)) {
        
        if (DEBUG_MODE_CRONS) {
            
            DebugBox("Campaign Complete Error", "$SentCount / " . count($Response) . " sent");
            UpdateLog(print_r($Response,1), "$SentCount / " . count($Response) . " sent");
            
        }
    }
    
}
 * *
 */
?>
