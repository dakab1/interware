<?php
include_once ("config.php");
include_once ("utils.php");

function SaveSocialPost ($user_id, $text, $id = false) {
    
    if ($id) {
        
        $SQL = "UPDATE `social_post`
            SET `text` = '" . addslashes($text). "',
                `user_id` = '$user_id' 
            WHERE 
                `id`='$id'";
        
    } else {
        
        $SQL = "INSERT INTO `social_post`
            (`user_id`,`text`) 
            VALUES
            ('$user_id', '" .  addslashes($text). "')";
        
    }
    
    return ExecuteQuery($SQL);
    
}

function DeleteSocialNetwork ($id) {
    
    $SQL = "DELETE FROM `social_network_oauth` WHERE id = '$id'";
    return ExecuteQuery($SQL);
    
}

function RetrieveSocialPosts ($id = false, $limit=" LIMIT 10", $count= false) {
    
    $SQL = "SELECT " . ($count ? "count(*)" : "*") . " FROM `social_post`" . ($id ? " WHERE `id` = $id " : "") . $limit;
    
    return ExecuteQuery($SQL);
    
}

function RetrieveSocialPostsSchedule ($id= false, $limit = " LIMIT 10", $count= false) {
    
    $SQL = "SELECT " . ($count ? "count(*)" : "*") . " FROM `campaign_social`" . ($id ? " WHERE `id` = $id " : "") . $limit;
    
    return ExecuteQuery($SQL);
    
}

function RetrievePreviousSocialPosts ($Limit = null) {
    
    $SQL = "SELECT `user`.`name`, `social_network_oauth`.`description`, `social_post`.`id`,`social_network_oauth`.`network`,`social_post`.`text`, `campaign_social`.`send_date`,`campaign_social`.`sent`
                FROM `social_post`, `campaign_social`, `social_network_oauth`, `user`
                WHERE 
                `social_network_oauth`.`id` = `campaign_social`.`social_network_oauth_id` AND
                `campaign_social`.`social_post_id` = `social_post`.`id` AND
                `social_post`.`user_id` = `user`.`id` AND
                `campaign_social`.`send_date` <= NOW() AND `campaign_social`.`sent` = '1'
                ORDER BY send_date DESC " . $Limit;
    
    return ExecuteQuery($SQL);
    
}

function SaveSocialPostSchedule ($social_post_id, $social_network_id, $send_date, $id = false, $cid = false) {
    
    if ($id) {
        
        $SQL = "UPDATE `campaign_social`
            SET `social_post_id` = '$social_post_id',
            `social_network_oauth_id` = '$social_network_id',
            `send_date` = '$send_date'
            WHERE 
            `id` = '$id'";
        
    } else {
        
        $SQL = "INSERT INTO `campaign_social`
            (`social_post_id`,`social_network_oauth_id`,`send_date`" .($cid ? ",`campaign_id`" : ""). ")
            VALUES
            ('$social_post_id','$social_network_id','$send_date'".($cid ? ",'$cid'" : "").")";
    }
    
    return ExecuteQuery($SQL);
}

function SaveOauth ($uid, $description, $token, $secret, $network, $id=false, $extra = false) {
//--- Social network Oauth
    
    if (!$id) {
        $SQL = "INSERT INTO 
            `social_network_oauth` 
            (`user_id`,`description`,`access_token`,`access_token_secret`,`network`" . ($extra ? ",`extra`" : "") . ") 
            VALUES
                ('$uid',
                '" . addslashes($description) ."',
                '" . addslashes($token) ."',
                '" . addslashes($secret) . "',
                '" . addslashes($network) ."'
                " . ($extra ? ",'" . addslashes($extra) . "'" : "") . ")";
    } else {
        $SQL = "UPDATE `social_network_oauth`
            SET
                `user_id`='" . addslashes($uid). "',
                `description`='" . addslashes($description). "',
                `access_token`='" . addslashes($token). "',
                `access_token_secret`='" . addslashes($secret). "',
                `network`='" . addslashes($network). "'
                " . ($extra ? ",`extra`='" . addslashes($extra). "'" : "") . "
            WHERE
                `id` = '$id'";
    }
    
    return ExecuteQuery($SQL);
    
}

function RetrieveOauth ($id=false, $type=false, $limit=" LIMIT 25") {    
    
    $SQL = "SELECT * FROM `social_network_oauth` " . ($id ? " WHERE `id` = '$id'" : "") . ($type ? " WHERE `type`='$type'" : "");
    
    return ExecuteQuery($SQL);
    
}

function RetrieveUnsubscribeCount () {
    
    $SQL = "SELECT count(*) as `Unsubscibed` FROM `unsubscribe`";
    
    return ExecuteQuery($SQL);
    
}

function RetrieveUnsubscribe ($email, $limit="LIMIT 50") {
    
    $SQL = "SELECT * FROM `unsubscribe` " . ($email!="" ? "WHERE `email_address` = '$email'" : "") . $limit;

    return ExecuteQuery($SQL);
        
}

function SaveUnsubscribe ($email) {
    
    $SQL = "INSERT INTO `unsubscribe` (`email_address`) VALUES ('" . addslashes($email) . "')";
    
    return ExecuteQuery($SQL);
    
}

function DeleteUnsubscribe ($email) {
    
    $SQL = "DELETE FROM `unsubscribe` WHERE `email_address` = '" . addslashes($email) . "'";
    
    return ExecuteQuery($SQL);

    
}

function SaveCampaign($name, $start_date, $user_id, $status, $end_date="", $id=false) {
 
    if (!$id) {
        
        $SQL = "
            INSERT INTO `campaign`(`name`,`start_date`,`user_id`,`status`,`end_date`, `created_date`) 
                VALUES('" . addslashes($name) . "','$start_date','$user_id','$status','$end_date', '" . date('Y-m-d H:i:s') . "')";
    } else {
        
        $SQL = "
            UPDATE `campaign`
                SET `name` = '" . addslashes($name) . "', 
                `start_date` = '$start_date', 
                `user_id` = '$user_id',
                `status` = '$status',
                `end_date` = '$end_date'
            WHERE `id` = '$id'";
                
    }
    
    if (!$Response = ExecuteQuery($SQL)) {
        return false;
    } else {
        return $Response;
    }
    
}

function SaveEmail ($from_address, $bcc_address, $subject, $message, $id=false,$status=0) {

    if (!$id) {
        
        $SQL = "
            INSERT INTO `mail` (`from_address`,`bcc_address`,`subject`,`message`,`status`) VALUES('" . addslashes($from_address) . "','" . addslashes($bcc_address) . "','" . addslashes($subject) . "','" . addslashes($message) . "', '$status')";
    } else {
        
        $SQL = "
            UPDATE `mail` 
                SET `from_address` = '" . addslashes($from_address) . "',
                `bcc_address` = '" . addslashes($bcc_address) . "',
                `subject` = '" . addslashes($subject). "',
                `message` = '" . addslashes($message). "',
                `status`='$status'
            WHERE `id` = '$id'";
                
    }
    
    if (!$Response = ExecuteQuery($SQL)) {
        if ($Response === false) {
            
            return false;
            
        } else {
            
            return true;
            
        }
    } else {
        return $Response;
    }
    
}

function SaveList ($mail_id, $email, $field1="", $field2="", $field3="", $field4="", $status=0, $id=false) {
    //--- Save or update an existing list entry    
    if(!$id) {

        $SQL = "INSERT INTO `list` (`mail_id`,`email`,`field1`,`field2`,`field3`,`field4`,`status`) VALUES('$mail_id','" . addslashes($email) . "','" . addslashes($field1) . "','" . addslashes($field2) . "','" . addslashes($field3) . "','" . addslashes($field4) . "','" . addslashes($status) . "')";

    } else {

        $SQL = "
            UPDATE `list`
                SET `mail_id` = '$mail_id',
                `email` = '" . addslashes($email) . "',
                `field1` = '" . addslashes($field1) . "',
                `field2` = '" . addslashes($field2) . "',
                `field3` = '" . addslashes($field3) . "',
                `field4` = '" . addslashes($field4) . "',
                `status` = '$status',
            WHERE `id` = '$id'";
    
    }     
            
    if (!$Response = ExecuteQuery($SQL)) {
        return false;
    } else {
        return $Response;
    }
    
}

function SaveListBulk($mail_id, $DataARY) {
//--- Bulk insert list data into the DB

    if (!is_array($DataARY)) {
        return false;
    }
    
    //--- ValidateEmailAddresses first
    foreach ($DataARY as $Row) {
        
        if(!ValidateEmailAddress($Row[0])) {
            return false;
        }
        
    }
    
    //--- Delete previous list if any
    $SQL = "DELETE FROM `list` WHERE `mail_id` = '$mail_id'";
    ExecuteQuery($SQL);
    
    $columns = 0; //keep count of number of columns un the CSV 
    
    $SQL = "INSERT INTO `list` (`mail_id`,`email`,`field1`,`field2`,`field3`,`field4`,`status`) VALUES";
       
    foreach ($DataARY as $Row) {
        
        $SQL .= "('$mail_id', '" . addslashes($Row[0]) . "', '" . addslashes($Row[1]) . "', '" . addslashes($Row[2]) . "', '" . addslashes($Row[3]) . "', '" . addslashes($Row[4]) . "','0'),";
                    
    }
    
    $SQL = rtrim($SQL, ",");
    
    if(!$Response = ExecuteQuery($SQL)) {
    
        return false;
    
    } else {
    
        return $Response;
    
    }

}

function RetrieveCampaignEmailSchedule ($campaign_id, $mail_id) {
    
    $SQL = "SELECT * FROM `campaign_mail` WHERE `campaign_id` = '" . $campaign_id . "' AND `mail_id` = '" . $mail_id . "'";
    return ExecuteQuery($SQL);
    
    
}

function CheckCampaignMailScheduled ($campaign_id, $mail_id) {
//--- Check if an email belonging to the passed campaign is scheduled for send
    
    $SQL = "SELECT count(*) FROM `campaign_mail` WHERE `campaign_id` = '" . $campaign_id . "' AND `mail_id` = '" . $mail_id . "'";
    $Response1 = ExecuteQuery($SQL);
    
    if ($Response1[0]['count(*)']>0) {
        
        return true;
        
    } else {
        
        return false;
        
    }
    
    
}

function SaveCampignMail ($campaign_id, $mail_id, $send_start_date = null, $send_end_date = null) {
    
    if(!CheckCampaignMailScheduled($campaign_id, $mail_id)) { 

        $SQL = "INSERT INTO `campaign_mail` (`campaign_id`,`mail_id`,`send_start_date`,`send_end_date`) VALUES('$campaign_id','$mail_id','$send_start_date','$send_end_date')";

    } else {

        $SQL = "
            UPDATE `campaign_mail`
                SET `campaign_id` = '$campaign_id',
                `mail_id` = '$mail_id',
                `send_start_date` = '$send_start_date',
                `send_end_date` = '$send_end_date'
            WHERE `mail_id` = '$mail_id'
            AND `campaign_id` = '$campaign_id'";
    
    }     
            
    if (!$Response = ExecuteQuery($SQL)) {
        return false;
    } else {
        return $Response;
    }
    
}

function SaveMailSent ($mail_id, $list_id, $message, $date_sent) {
    
    $SQL = "
        INSERT INTO mail_sent(`mail_id`,`list_id`,`message`,`date_sent`) 
        VALUES('$mail_id','$list_id','" . addslashes($message) . "','$date_sent')";
    
    if (!$Response = ExecuteQuery($SQL)) {
        return false;
    } else {
        return $Response;
    }
    
}

function SaveSMSSent ($sms_id, $list_id, $message, $date_sent, $network_response) {
    
    $SQL = "
        INSERT INTO sms_sent(`sms_id`,`list_id`,`message`,`date_sent`,`network_response`) 
        VALUES('$sms_id','$list_id','" . addslashes($message) . "','$date_sent','" . addslashes($network_response) . "')";
    
    if (!$Response = ExecuteQuery($SQL)) {
        return false;
    } else {
        return $Response;
    }
    
}

function SaveUser ($username, $password, $status, $user_id, $emails=0, $socialmedia=0, $users=0, $reports=0, $campaigns=0, $sms, $recipients) {
    
    if ($user_id) {
        $SQL = "
            UPDATE `user` 
            SET `name`='" . addslashes($username) . "',
                `password`='" . addslashes($password). "',
                `status`='$status',
                `permission_emails`='$emails',
                `permission_social_medias`='$socialmedia',
                `permission_reports`='$reports',
                `permission_users`='$users',
                `permission_campaigns`='$campaigns',
                `permission_sms`='$sms',
                `permission_recipients`='$recipients'
            WHERE `id`='$user_id' 
                ";
    } else {
        
        $SQL = "INSERT INTO `user`
                (`name`,
                `password`,
                `status`,
                `permission_emails`,
                `permission_social_medias`,
                `permission_reports`,
                `permission_users`,
                `permission_campaigns`)
            VALUES('" . addslashes($username). "',
                '" . $password. "',
                '$status',
                '$emails',
                '$socialmedia',
                '$reports',
                '$users',
                '$campaigns')";
    }
    
    if (!$Response = ExecuteQuery($SQL)) {
        return false;
    } else {
        return $Response;
    }
        
}

function RetrieveCampaignsCount () {
    
    $SQL = "SELECT count(*) as `campaigns` FROM campaign";
    
    return ExecuteQuery($SQL);
    
}

function RetrieveCampaigns ($Limit = false) {
    
    $SQL = "SELECT * FROM campaign ORDER BY id DESC " . ($Limit ? $Limit : "");
    if ($Response = ExecuteQuery($SQL)) {
        
        return $Response;
    } else {
        
        return false;
        
    }
    
}

function RetrieveEmailsCount ($cid = false) {
    
    if (!$cid) {
        $SQL = "SELECT count(*) as emails from mail";
    }else {
        $SQL = "SELECT count(*) as emails from campaign_mail";
    }
    
    return ExecuteQuery($SQL);
    
}

function RetrieveEmails ($campaign_id = false, $Limit=false) {
    
    $SQL = "SELECT m.*, m.id as mail_id" . ($campaign_id ? ", cm.send_start_date as send_date " : "") ." FROM mail as m " . ($campaign_id ? ", campaign_mail as cm WHERE m.id=cm.mail_id AND cm.campaign_id=$campaign_id " : "") . " ORDER BY `id` DESC" . (isset ($Limit) ? $Limit : "");
    
    if ($Response = ExecuteQuery($SQL)) {
        
        return $Response;
    } else {
        
        return false;
    }
    
}

function RetrieveMail ($mail_id) {
    
    $SQL = "SELECT * FROM `mail` WHERE `id` = '$mail_id'";
    
    return ExecuteQuery($SQL);
}

function RetrieveCampaign ($campaign_id, $Limit = "") {
    
    $SQL = "SELECT * FROM `campaign` WHERE `id` = $campaign_id ORDER BY id desc" . ($Limit != "" ? $Limit : "");
    
    if ($Response = ExecuteQuery($SQL)) {
        
        return $Response;
    } else {
        
        return false;
    }
}

function RetrieveRecipientCount($mail_id=false) {
    
    $SQL = "SELECT count(*) as `recipients` FROM `list` " . ($mail_id ? " WHERE `mail_id` = $mail_id" : "");

    if ($Response = ExecuteQuery($SQL)) {
        
        return $Response;
    } else {
        
        return false;
    }
}

function RetrieveRecipientsAll ($count=false) {
    
    $SQL = "SELECT " . ($count ? "COUNT(DISTINCT(email)) as emails" : "DISTINCT(email) as email") . " FROM list";
    return ExecuteQuery($SQL);
    
}

function RetrieveRecipientsByEmail ($mail_id, $limit="LIMIT 0,50") {
    
    $SQL = "SELECT * FROM `list` WHERE `mail_id` = '$mail_id'";
    
    return ExecuteQuery($SQL);
    
}

function RetrieveRecipientsBySMS ($sms_id, $limit="LIMIT 0,50") {
    
    $SQL = "SELECT * FROM `list` WHERE `sms_id` = '$sms_id' ORDER by `sms_id` DESC";
    
    return ExecuteQuery($SQL);
    
}

function RetrieveReceipientsByMailSent ($mail_id=false, $limit="LIMIT 0,50") {
    
    if ($mail_id) {
        
        $SQL = "SELECT `list`.`id`, `list`.`email`, `list`.`status`, `list`.`date_queued`, `mail_sent`.`date_sent`
            FROM `list` ,`mail_sent` 
            WHERE `list`.`mail_id` = `mail_sent`.`mail_id` " . ($mail_id ? " AND `list`.`mail_id` = '$mail_id' " : " ") .
            ($limit ? $limit : "");
    } else {
        
        $SQL ="SELECT * FROM list $limit";
        
    }
    
    if ($Response = ExecuteQuery($SQL)) {
        
        return $Response;
    } else {
        
        return false;
    }
    
}

function UpdateQueuedEmailCampaignStatus ($mail_id, $status) {
    
    $SQL = "UPDATE list SET status = '$status' WHERE email_id = '$mail_id';";
    $SQL .= "UPDATE campaign_mail set status='$status';";
    
    return ExecuteQuery($SQL);
    
}

function UpdateQueuedSMSCampaignStatus ($mail_id, $status) {
    
    $SQL = "UPDATE list SET status = '$status' WHERE email_id = '$mail_id'";
    $SQL .= "UPDATE campaign_sms set status='$status';";
    
    return ExecuteQuery($SQL);
    
}

function RetrieveQueuedMailCampaigns ($Limit = false) {
    
    if($Limit) $Limit = " limit $Limit";
    
    $SQL = "
        SELECT count(`list`.`status`) as `status_count`,`list`.`status`, `campaign`.`name`, `mail`.`subject`,
            `campaign_mail`.`send_start_date`, `mail`.`id` as `mail_id` 
        FROM `campaign`, `campaign_mail`,`mail`,`list`
        WHERE 
            `campaign`.`id` = `campaign_mail`.`campaign_id`
        AND 
            `mail`.`id` = `campaign_mail`.`mail_id`
        AND 
            `mail`.`id` = `list`.`mail_id`
        GROUP BY `campaign`.`name`, `mail`.`subject`, `list`.`status` 
        ORDER BY `send_start_date` DESC " . $Limit;
    
    if ($Response = ExecuteQuery($SQL)) {
        
        return $Response;
    } else {
        
        return false;
    }
    
}

function RetrieveUsers ($Limit = "") {

    $SQL = "SELECT * FROM `user` $Limit";
    if ($Response = ExecuteQuery($SQL)) {
        
        return $Response;
        
    } else {
        
        return false;
        
    }
}

function RetrieveUserHistory($uid, $limit="", $count = false) {
    
    if (!$count) {
        
        $SQL = "SELECT * FROM `event_log` WHERE `user_id` = '$uid' ORDER BY `date` DESC " . $limit;
        
    } else {
        
        $SQL = "SELECT count(*) FROM `event_log` WHERE `user_id` = '$uid' " . $limit;
        
    }
    
    return ExecuteQuery($SQL);
    
}

function RetrieveUser($user_id) {
    
    $SQL = "SELECT * FROM `user` WHERE `id` = '$user_id'";
    
    if ($Response = ExecuteQuery($SQL)) {
        
        return $Response;
        
    } else {
        
        return false;
        
    }
}

function RetrieveUsersCount ($Limit = "") {
    
    $SQL = "SELECT count(*) as `users` FROM `user` $Limit";
    
    if ($Response = ExecuteQuery($SQL)) {
        
        return $Response;
        
    } else {
        
        return false;
    }
}

function SaveSession($encrypted_session_string, $session_string,$user_id, $expiry_date, $start_date, $last_ip){
    
    $encrypted_session_string = sha1($encrypted_session_string . $last_ip);
    
    $SQL = "INSERT INTO session(`encrypted_session_string`,`session_string`,`user_id`,`expiry_date`, `start_date`, `last_ip`)
        VALUES ('" . addslashes ($encrypted_session_string) . "', '" . addslashes($session_string). "', '$user_id', '$expiry_date', '$start_date', '$last_ip');";
    
    return ExecuteQuery($SQL);
    
}

function RetrieveSession ($session, $expiry = false) {
    
    $SQL = "SELECT count(*) FROM session WHERE encrypted_session_string = '$session' AND user_id='" . $_SESSION['emailer']['id'] . "' " .
            ($expiry ? "AND expiry_date >= '$expiry'" : "");

    $response = ExecuteQuery($SQL);
    
    if (is_array($response)) {
        
        if ($response[0]['count(*)'] > 0) {
            
            return true;
            
        } else {
            
            return false;
            
        }
        
    } return false;
    
}

function DeleteUserSession ($user_id) {
    
    $SQL = "DELETE FROM session WHERE user_id = '$user_id'";
    return ExecuteQuery($SQL);
    
}

function RetrieveActiveSessions () {
    
    $SQL = "SELECT count(*) FROM session";
    $response = ExecuteQuery($SQL);
    return $response[0]['count(*)'];
    
}

function Authenticate ($username, $password) {
    
    $SQL = "SELECT `id` FROM `user` WHERE `name`='$username' AND `password`='$password'";
    
    if ($Response = ExecuteQuery($SQL)) {
        
        return $Response;
        
    } else {
        
        return false;
    }
    
}

function SaveEvent ($user_id, $description, $date) {
    
    $SQL = "INSERT INTO `event_log` (`user_id`,`description`,`date`)
            VALUES ('$user_id','" . addslashes($description). "','" .$date. "')";
    
    return ExecuteQuery($SQL);
    
}

function RetrieveEvents($user_id = false, $start_date = false, $end_date = false, $Limit = false) {
    
    $SQL = "SELECT 
            user.`name` as `username`,
            user.`id` as `user_id`,
            event_log.`id` as `event_id`,
            `description`,
            `date`
        FROM 
            `user`
        LEFT JOIN `event_log`.`user_id` ON `user.id`
        ";
    
    $SQL .= ($user_id!="" || $start_date!="" || $end_date!="" ? "WHERE " : "");
    $SQL .= ($user_id!="" ? " `user_id`='$user_id' " : "");
    $SQL .= ($start_date!="" && $user_id!="" ? " AND " : "");
    $SQL .= ($start_date!="" ? " `date`>='$start_date'" : "");
    $SQL .= ($end_date!="" && $start_date!="" ? " AND " : "");
    $SQL .= ($end_date!="" ? " `date`<='$end_date'" : "");
    
    return ExecuteQuery($SQL);
    
}

function SaveAttachment ($filename, $size, $data, $mail_id, $mime_type ,$id = false) {
    
    if ($id) {
        
        $SQL = "UPDATE `mail_attachment`
            SET `filename` = '" . addslashes($filename) . "', 
                `size` = '$size',
                `data` = '" . base64_encode($data) . "',
                `mail_id` = '$mail_id',
                `mime_type` = '$mime_type'
            WHERE
                `id` = '$id'";
        
    } else {
        
        $SQL = "INSERT INTO `mail_attachment`
                (`filename`,`size`,`data`,`mail_id`, `mime_type`)
                VALUES
                ('" . addslashes ($filename) . "', '$size', '" . base64_encode($data) ."', '$mail_id', '$mime_type')";
    }
    
    return ExecuteQuery($SQL);
}

function DeleteAttachmentById ($id) {
    
    $SQL = "DELETE FROM `mail_attachment` WHERE `id` = '$id'";
    return ExecuteQuery($SQL);
    
}

function RetrieveMailAttachments ($mail_id) {
    
    $SQL = "SELECT * FROM `mail_attachment` WHERE `mail_id` = '$mail_id'";
    return ExecuteQuery($SQL);
    
}

function RetrieveMailAttachment ($id) {
    
    $SQL = "SELECT * FROM `mail_attachment` WHERE `id` = '$id'";
    return ExecuteQuery($SQL);
    
}

function MailSent ($id) {
    
    //$SQL = "SELECT COUNT(*) FROM `mail_sent`";
}

function SaveSMS ($text, $created_date, $id = false) {
    
    if ($id) {
        
        $SQL = "UPDATE `sms` SET `text` = '$text' WHERE `id` = '$id'";
        
    } else {
        
        $SQL = "INSERT INTO `sms` (`text`,`created_date`) VALUES ('" . addslashes($text) . "', '" . date ("Y-m-d H:i:s"). "')";
        
    }
    
    return ExecuteQuery($SQL);
    
}

function SMSCampaignExists($campaign_id, $sms_id) {
    
    $SQL = "SELECT COUNT(*) FROM `campaign_sms` WHERE `campaign_id`='$campaign_id' AND `sms_id` = '$sms_id'";
    $Response = ExecuteQuery($SQL);
    
    if ($Response[0]['COUNT(*)'] > 0) {
        
        return true;
        
    } else {
        
        return false;
        
    }
    
    
}

function SaveSMSCampaign ($campaign_id, $sms_id, $status = 0, $send_start_date = null){

    if (SMSCampaignExists($campaign_id, $sms_id)) {
        
        $SQL = "UPDATE campaign_sms SET `status` = '$status', `send_start_date` = '$send_start_date' WHERE `campaign_id` = '$campaign_id' AND `sms_id` = '$sms_id'";
    } else {
        
        $SQL = "INSERT INTO `campaign_sms` (`campaign_id`,`sms_id`,`status`,`send_start_date`)
            VALUES('$campaign_id', '$sms_id', '$status', '$send_start_date')";
    }
    
    return ExecuteQuery($SQL);
}


function SaveSMSListBulk($sms_id, $DataARY) {
//--- Bulk insert list data into the DB

    if (!is_array($DataARY)) {
        return false;
    }
    
    //--- Check if valid MSISDN numbers where supplied
    foreach ($DataARY as $Row) {
        
        if (strlen($Row[0])!= 11 || !is_numeric($Row[0])) { //11 digit numbers
            
            return false;
            
        }
    }
    
    //--- Delete previous list if any
    $SQL = "DELETE FROM `list` WHERE `sms_id` = '$sms_id'";
    ExecuteQuery($SQL);
    
    $columns = 0; //keep count of number of columns un the CSV 
    
    $SQL = "INSERT INTO `list` (`sms_id`,`msisdn`,`field1`,`field2`,`field3`,`field4`,`status`) VALUES";
       
    foreach ($DataARY as $Row) {
        
        $SQL .= "('$sms_id', '" . addslashes($Row[0]) . "', '" . addslashes($Row[1]) . "', '" . addslashes($Row[2]) . "', '" . addslashes($Row[3]) . "', '" . addslashes($Row[4]) . "','0'),";
                    
    }
    
    $SQL = rtrim($SQL, ",");
    
    if(!$Response = ExecuteQuery($SQL)) {
    
        return false;
    
    } else {
    
        return $Response;
    
    }

}

function RetrieveQueuedSMSByCampaign ($campaign_id = false, $limit = null) {
    
    $SQL = "SELECT count(`list`.`status`) as `status_count`,`list`.`status`, `campaign`.`name`, `campaign_sms`.`send_start_date`, `sms`.`text`, `sms`.`id`
                FROM `sms`,`campaign_sms`,`campaign`, `list`
                WHERE `sms`.`id` = `list`.`sms_id` AND `sms`.`id` = `campaign_sms`.`sms_id` AND `campaign_sms`.`campaign_id` = `campaign`.`id` " . ($campaign_id ? "AND `campaign`.`id` = '$campaign_id'" : "") . " 
                GROUP BY `campaign`.`name`, `sms`.`text`, `list`.`status` 
                ORDER BY id DESC " . $limit;
    
    return ExecuteQuery($SQL);
    
}

function RetrieveSMSRecipientCount ($sms_id) {
    
    $SQL = "SELECT count(*) as recipients FROM list where sms_id = '$sms_id'";
    
    return ExecuteQuery($SQL);
    
}

function RetrieveSMS ($sms_id) {    
    
    $SQL = "SELECT `sms`.*, `campaign_sms`.`campaign_id`
                FROM `sms`,`campaign_sms`
                WHERE `campaign_sms`.`sms_id` = `sms`.`id`
                AND `sms`.`id` =  '$sms_id'";
    
    return ExecuteQuery($SQL);
    
}

function RetrieveSMSByCampaign ($campaign_id) {    
    
    $SQL = "SELECT `sms`.*, `campaign_sms`.`campaign_id`
                FROM `sms`,`campaign_sms`
                WHERE `campaign_sms`.`sms_id` = `sms`.`id`
                AND `campaign_sms`.`campaign_id` =  '$campaign_id'";
    
    return ExecuteQuery($SQL);
    
}

function RetrieveSMSSchedule ($sms_id, $campaign_id = false) {
    
    $SQL = "SELECT * FROM campaign_sms WHERE sms_id='$sms_id'" .($campaign_id ? " AND campaign_id='$campaign_id'" : "");
    
    return ExecuteQuery($SQL);
    
}

function RetrieveSMSs ($limit="", $count=false) {
    
    $SQL = "SELECT " . ($count ? "count(*)" : "*") . " FROM sms ORDER BY id DESC " .  $limit;
    
    return ExecuteQuery($SQL);
    
}

function DeleteSocialPost ($id) {
    
    $SQL = "DELETE FROM campaign_social WHERE id = $id";
    return ExecuteQuery($SQL); 
}


function UpdateListStatusByMailID ($mail_id, $status) {
    
    $SQL = "UPDATE list SET status = '$status' WHERE mail_id = '$mail_id'";
    return ExecuteQuery($SQL);
    
}

function UpdateListStatusBySMSID ($sms_id, $status) {
    
    $SQL = "UPDATE list SET status = '$status' WHERE sms_id = '$sms_id'";
    return ExecuteQuery($SQL);
    
}

function UpdateListStatusByID ($list_id, $status) {
    
    $SQL = "UPDATE list SET status = '$status' WHERE id = '$list_id'";
    return ExecuteQuery($SQL);
    
}

function IsSMSSent ($sms_id) {    
    
    $SQL = "SELECT COUNT(*) FROM list WHERE sms_id = '$sms_id' AND status <> '0'";
    $Result = ExecuteQuery($SQL);
    
    if ($Result[0]['COUNT(*)'] > 0) {
        
        return true;
        
    } else {
        
        return false;
    }
    
}

function isEmailSent ($mail_id) {
    
    $SQL = "SELECT COUNT(*) FROM list WHERE mail_id = '$mail_id' AND status <> '0'";
    $Result = ExecuteQuery($SQL);
    
    if ($Result[0]['COUNT(*)'] > 0) {
        
        return true;
        
    } else {
        
        return false;
    }
}

function GetEmailCampaignIDByMailID ($mail_id) {
    
    //--- This is potentially buggy
    //TODO: Find another way to get the campaign_id for the mail 
    $SQL = "SELECT campaign_id FROM campaign_mail WHERE mail_id=$mail_id";
    $Result = ExecuteQuery($SQL);
    if ($Result[0]['campaign_id']!="") {
        return $Result[0]['campaign_id'];
    } else  {
        return 0;
    }
    
}

?>