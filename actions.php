<?php
session_start();
include_once("modules/config.php");
include_once("modules/DB.php");
include_once("modules/utils.php");
include_once("verify_access.php");

if(!DEBUG_MODE_ACTIONS) {
    ob_start();
} else {
    
    if (isset($_FILES)) {
        
        echo "<div style='color:red; border 1px solid red'><h3>Files</h3>" . print_r($_FILES). "</div>";
    }
}


extract ($_GET);
extract ($_POST);


switch ($a) {
case "1": //Save a campaign
    if(!$response = SaveCampaign($name, $start_date, $user_id, $status, $end_date, $id)) {
        UpdateLog(debug_backtrace(), "SaveCampaign returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)) , "errors.txt");
        $_SESSION['cid'] = "";

        //--- Redirect the parent frame 
        ?>
        <script type="text/javascript">
            parent.document.getElementById('message').innerHTML = "<span class='notice_negative'>Failed to save campaign.</span>";
        </script>
        <?php
        die();
        
    } else {
        
        $parameters = "?cid=" . $response;
        $_SESSION['emailer']['cid'] = $response;
        SaveEvent($_SESSION['emailer']['id'], "Saved campaign with ID " . ($id!=""?$id:$response) , date("Y-m-d H:i:s"));
        
        //--- Redirect the parent frame 
        ?>
        <script type="text/javascript">
            parent.document.getElementById('message').innerHTML = "<span class='notice_positive'>Campaign successfully saved.</span>";
            parent.document.getElementById('CampaignID').value = "<?php echo $response ?>";
        </script>
        <?php
        die();
    }    
break; 

case "2": //save an email
    if(!$response = SaveEmail ($from_address, $bcc_address, $subject, $message, $eid)) {
        UpdateLog(debug_backtrace(), "SaveEmail returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
        $_SESSION['eid'] = "";
        
    } else {     
        SaveCampignMail(($cid!="" ? $cid : $_SESSION['emailer']['cid']), ($eid!="" ? $eid : $response));
        SaveEvent($_SESSION['emailer']['id'], "Saved email with ID " . ($eid!=""?$eid:$response) , date("Y-m-d H:i:s"));
        
        //--- Check if the email has any attachments to be saved
        if (is_array($_FILES)) {
            
            for ($i=0; $i<count($_FILES['attachment']['tmp_name']); $i++) {
                //--- Get file contents
                if ($handle = fopen($_FILES['attachment']['tmp_name'][$i], "r")) {
                    
                    $contents = fread($handle, $_FILES['attachment']['size'][$i]); //get the raw string contents
                    
                    //--- Save the attachments to the database
                    SaveAttachment($_FILES['attachment']['name'][$i], $_FILES['attachment']['size'][$i], $contents, (isset($eid) && $eid!="" ? $eid : $response),$_FILES['attachment']['type'][$i]);
                    
                } else {
                    
                    UpdateLog(debug_backtrace(), "Save mail attachments: Unable to open file " . $_FILES['attachment']['tmp_name'][$i], "uploads.txt");
                }
                
            }
            
        }
        
        $parameters = "?eid=" . $response . "&cid=" . $cid;
        $_SESSION['eid'] = $response;
    }
break; 

case "3": //upload csv file
    
    //--- Get the data out of the uploaded csv
    if ($Data = ProcessCSV()) {
        
        //--- Save the data from the CSV into the database
        if(!$response = SaveListBulk($_POST['eid'], $Data)) {
            UpdateLog(debug_backtrace(), "SaveListBulk returned false in actions.php, possible invalid email address | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)) , "errors.txt");
            $r = $_SERVER['HTTP_REFERER'] . "&error=Invalid+email+address+detected. Please check the contents of your csv and try again&";
        } else {
            SaveEvent($_SESSION['emailer']['id'], "Uploaded CSV for mail ID " . $_POST['mail_id'], date("Y-m-d H:i:s"));
        }
        
        $parameters = "?eid=" . $_POST['eid'] . "&cid=" . $_POST['cid'];
        $_SESSION['eid'] = $_POST['mail_id'];
            
    } else {

        UpdateLog(debug_backtrace(), "ProcessCSV returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
        
    }
    
break; 

case "4": //link campaign and email
    
    if(!$response = SaveCampignMail ($campaign_id, $mail_id, $send_start_date ." " . $send_start_time, $send_end_date)) {
        UpdateLog(debug_backtrace(), "SaveCampaign returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
    } else {
        SaveEvent($_SESSION['emailer']['id'], "Added an email ID " . $mail_id . " to campaign ID " . $campaign_id, date("Y-m-d H:i:s"));
    }
break; 

case "5": //save a new user or update an existing
    if(!$response = SaveUser($username, $password, $status, $user_id, $emails, $socialmedia, $users, $reports, $campaigns, $sms, $recipients)) {
        UpdateLog(debug_backtrace(), "SaveUser returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
    } else {
        
        SaveEvent($_SESSION['emailer']['id'], "Save user with ID " . ($user_id!="" ? $user_id : $response), date("Y-m-d H:i:s"));
    }
break;

case "6": //Blacklist an email
    
    $r = "recipient_unsubscribed_view.php";
    if (!$Response = SaveUnsubscribe($_GET["e"])) {
        UpdateLog(debug_backtrace(), "SaveUnsubscribe returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
    } else {
        SaveEvent($_SESSION['emailer']['id'], $description, date("Y-m-d H:i:s"));
    }
    
break;

case "7": //Unblacklist an email

    $r = "recipient_list_view.php";
    
    if (!$Response = DeleteUnsubscribe($e)) {
        UpdateLog(debug_backtrace(), "DeleteUnsubscribe returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
    } else {
        SaveEvent($_SESSION['emailer']['id'], "Added to white list the email $e", date("Y-m-d H:i:s"));
    }
    
break;

case "8": //Save post to DB
    
    if(!isset($r)) $r = "social_posts.php";
    
    if (!$Response1 = SaveSocialPost($_SESSION['emailer']['id'], $status, $id)) {
        
        UpdateLog(debug_backtrace(), "SaveSocialPost returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
        
    } else {
        
        for($i=0;$i<count($network);$i++) {

            if (!$Response2 = SaveSocialPostSchedule($Response1, $network[$i], $date . " " . $time, false, $cid)) {
                UpdateLog(debug_backtrace(), "SaveSocialPostSchedule returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
            } else {
                SaveEvent($_SESSION['emailer']['id'], "Saved social updated with ID $Response1", date("Y-m-d H:i:s"));
            } 
            
        }
        
    }
    
break;

case "9": //Get email contents
    $Email = RetrieveMail($eid);
    echo $Email[0]['message']; 
break;

case "10": //Get template file contents
    $content =  GetFileContents("/" . urldecode($file));
    $content = CorrectLinks($content);
    echo $content;
break;

case "11": //Save sms content
    
    if (!$Response = SaveSMS($text, date("Y-m-d H:I:s"), $sms_id)) {
        UpdateLog(debug_backtrace(), "SaveSMS returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
    } else {
        
        SaveEvent($_SESSION['emailer']['id'], "Saved SMS with ID $Response", date("Y-m-d H:i:s"));
        $parameters = "?cid=$cid&sms_id=".($sms_id ? $sms_id : $Response);
        
        if (isset($cid) && !isset($sms_id)) {
            
            if (SaveSMSCampaign($cid, ($sms_id ? $sms_id : $Response), $status, $send_start_date. " " . $send_start_time) !== false){
                
                SaveEvent($_SESSION['emailer']['id'], "Saved SMS with ID " . ($sms_id ? $sms_id : $Response) ." to campaign with id $cid .", date("Y-m-d H:i:s"));
                
            } else {
                
                UpdateLog(debug_backtrace(), "SaveSMSCampaign returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
                
            }
            
        }
    }
break; 

case "12": //Upload sms recipient list
    
    //--- Get the data out of the uploaded csv
    if ($Data = ProcessCSV()) {
        
        //--- Save the data from the CSV into the database
        if(!$response = SaveSMSListBulk($_POST['sms_id'], $Data)) {
            UpdateLog(debug_backtrace(), "SaveSMSListBulk returned false in actions.php, possibly because of invalid msisdn number. | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)) , "errors.txt");
            $r = $_SERVER[HTTP_REFERER] . "&error=Invalid cellphone number detected. Please check your csv file and try again.&";
        } else {
            SaveEvent($_SESSION['emailer']['id'], "Uploaded CSV for SMS ID " . $_POST['sms_id'], date("Y-m-d H:i:s"));
        }
        
        $parameters = "?sms_id=" . $_POST['sms_id'] . "&cid=" . $_POST['cid'];
        $_SESSION['sms_id'] = $_POST['sms_id'];
            
    } else {

        UpdateLog(debug_backtrace(), "ProcessCSV returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
        
    }
    
break;   

case "13": //Save or update sms send date
    
    if(SaveSMSCampaign($cid, $sms_id, $status, $send_start_date . " " . $send_start_time)!==false) {
        
        SaveEvent($_SESSION['emailer']['id'], "Uploaded CSV for SMS ID " . $sms_id, date("Y-m-d H:i:s"));
        
    } else {
        
        UpdateLog(debug_backtrace(), "SaveSMSCampaign returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
    }
    
break;

case "14": //delete a social network account
    
    if ($Response = DeleteSocialNetwork($sn_id)) {
        
        SaveEvent($_SESSION['emailer']['id'], "Deleted social network with $sn_id.", date("Y-m-d H:i:s"));
        
    } else {
        
        UpdateLog(debug_backtrace(), "DeleteSocialNetwork returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
        
    }
    
    
break;

case "15": //save oauth details
    
    //--- Split the page ID and the access_token
    $data = explode("|", $extra);
    $token = $data[1];
    $page_id = $data[0];
    
    if ($Response = SaveOauth($_SESSION['emailer']['id'], $description, $token, $secret, $network, $sn_id, $page_id)) {
        
        SaveEvent($_SESSION['emailer']['id'], "Save social network with" . ($sn_id ? $sn_id : $Response), date("Y-m-d H:i:s"));
        
    } else {
        
        UpdateLog(debug_backtrace(), "SaveOauth returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
        
    }
    
break;

case "16": //delete post
    
    if (DeleteSocialPost($p_id)) {
        
        SaveEvent($_SESSION['emailer']['id'], "Deleted social post with id " . $p_id, date("Y-m-d H:i:s"));
        
    } else {
        
        UpdateLog(debug_backtrace(), "DeleteSocialPost returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
        
    }
    
break;

case "17"://Updated queued
    if ($id!="") {
        
        //--- Update individual entry
        if (!UpdateListStatusByID($list_id, $status)) {
            
            UpdateLog(debug_backtrace(), "UpdateListStatusByID returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
            
        } else {
            
            SaveEvent($_SESSION['emailer']['id'], "Updated status of list item #$list_id to $status", date("Y-m-d H:i:s"));
            
        } 
        
    } elseif ($mail_id!="") {
        
        //--- Update multiple entries
        if (!UpdateListStatusByMailID($mail_id, $status)) {
            
            UpdateLog(debug_backtrace(), "UpdateListStatusByMailID returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
            
        } else {
            
            SaveEvent($_SESSION['emailer']['id'], "Updated status of list items from mail #$mail_id to $status", date("Y-m-d H:i:s"));
            
        }

    } elseif ($sms_id!="") {
        
        //--- Update multiple sms
        if (!UpdateListStatusBySMSID($sms_id, $status)) {
            
            UpdateLog(debug_backtrace(), "UpdateListStatusBySMSID returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
            
        } else {
            
            SaveEvent($_SESSION['emailer']['id'], "Updated status of list items from sms #$sms_id to $status", date("Y-m-d H:i:s"));
            
        }
        
    }
break;

case "18":
    
        $Email = RetrieveMail($eid);
        $Email = $Email[0];
        
        //--- Make these readable
        $from = stripslashes($Email['from_address']);
        $bcc = stripslashes($Email['bcc_address']);
        $subject = stripslashes($Email['subject']);
        $message = PersonalizeMessage(stripslashes($Email['message']), $custom);
        $attachment = stripslashes($Email['message']);
        
        if (SendHTMLMail($from, $email_address, $bcc, $subject, $message, $attachment)) {
            
            SaveEvent($_SESSION['emailer']['id'], "Send sample of mail #$eid to $email_address", date("Y-m-d H:i:s"));
            $error = "";
        } else {
            
            UpdateLog(debug_backtrace(), "SendHTMLMail returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
            $error = "&error=Failed to send sample email to $email_address&";
        }
        
        $r = $_SERVER['HTTP_REFERER'] . $error;
break;      
case "19":
    
        $SMS = RetrieveSMS($sms_id);
        $SMS = $SMS[0];
        
        //--- Make these readable
        $message = PersonalizeMessage(stripslashes($SMS['text']), $custom);
        
        if (SendSMS($msisdn, $message)) {
            
            SaveEvent($_SESSION['emailer']['id'], "Send sample of sms #$sms_id to $msisdn", date("Y-m-d H:i:s"));
            $error = "";
            
        } else {
            
            UpdateLog(debug_backtrace(), "SendSMS returned false in actions.php | " . (count($_GET) ? print_r($_GET,1) : print_r($_POST,1)), "errors.txt");
            $error = "&error=Failed to send sample sms to $sms_id&";
        }
        
        $r = $_SERVER['HTTP_REFERER'] . $error;
break;      

}


if(!DEBUG_MODE_ACTIONS) {
    
    if (isset($r)) {
        header("location:" . urldecode($r . $parameters));
    } 
    
    ob_flush();

}

?>