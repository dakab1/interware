<?php
@include_once ("modules/config.php");

function PersonalizeMessage ($message, $list) {
    
    $message = $message;
    $message = str_replace("{FIELD1}", $list['field1'], $message);
    $message = str_replace("{FIELD2}", $list['field2'], $message);
    $message = str_replace("{FIELD3}", $list['field3'], $message);
    $message = str_replace("{FIELD4}", $list['field4'], $message);

    return $message;
    
}

function CorrectLinks ($content) {

    $content = str_replace("{THISURL}", THISURL , $content);
    $content = str_replace("{PATH}", PATH , $content);
    $content = stripslashes($content);
    return $content;
    
}

function UpdateLog($Trail, $Error, $Filename = "error.log") {
    
    if ($Handle = fopen(HOMEPWD.PATH. "/logs/" . $Filename, "a")) {
        
        if(!fwrite($Handle,"\n" .date("Y-m-d H:i:s") . " | \nCalls:" . print_r($Trail,1) ."\n $Error")) {
            
            if (DEBUG_MODE) {
                die ("\n<span style='color:red; font-weight:bolder'>failed to write to error log file<br/>Calls:$Trail<br/>$Error</span>");
            } else {
                echo "___\n<!-- debug: failed to write to error log file\n\tCalls:$Trail\n\t$Error\n-->";
            }
            
        }
        
    } else {
        
        if (DEBUG_MODE) {
            die ("\n<span style='color:red; font-weight:bolder'>failed to open error log file<br/>Calls:$Trail<br/>$Error</span>");
        } else {
            echo "___\n<-- debug: failed to open error log file\n\tCalls:$Trail\n\t$Error\n-->";
        }
    }
    
}

function RequestURL($URL, $Parameters = array(), $Method="get") {
    
    $ch = curl_init($URL);
    
    if ($Method != "get") {
        curl_setopt($ch, CURLOPT_POST, true);
    }
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $Parameters);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $json = curl_exec($ch);
    $jsonArray = json_decode($json);
    
    return $jsonArray;
    
}

function ProcessCSV () {
    
    set_time_limit(0);
    
    $NewFilename = HOMEPWD . PATH . "/uploads/" . date("YmdHis") . "_" . (session_id()!="" ? session_id() : "nosession") . "_" . $_FILES['list']['name'];
        
    UpdateLog(debug_backtrace(), print_r($_FILES,1), "uploads.txt");
    
    if (move_uploaded_file($_FILES['list']['tmp_name'] , $NewFilename)) {
        
        //--- Open the uploaded CSV
        if($handle = fopen($NewFilename, "r")) {
            
            while ($row = fgetcsv($handle)) {
                
                $data[] = $row;
                 
            }                   
            
            $response = $data;         
            
        }
        
    } else {

        UpdateLog(debug_backtrace(), "Failed to move file to $NewFilename", "uploads.txt");
        
        $response = false;
    
    } 
    
    if (DEBUG_MODE) {
        
        echo "<div style='border:1px solid red; color:red'>";
        debug_print_backtrace();
        print_r($_FILES);
        echo "<br/>New filename: $NewFilename";
        echo "<p>CSV contents: ". print_r($response,1) . "</p>";
        echo "</div>";     
           
    }   

    return $response;    
}

function SendSMS ($msisdn, $text) {

    $SendSMSURL = SMS_API_URL . 
            "user=" . SMS_USER . 
            "&password=" . SMS_PASS . 
            "&api_id=" . SMS_API_ID . 
            "&to=" . $msisdn .
            "&text=" . urlencode($text).
            "&callback=7"; //Returns both intermediate, final and error statuses of a message.;
    
    $ch = curl_init($SendSMSURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $Response = curl_exec($ch);
    
    if (DEBUG_MODE) {
        
        DebugBox("SMS send URL", $SendSMSURL);
        UpdateLog(debug_print_backtrace(), $SendSMSURL, "sms.txt");
    }
    
    return $Response;
    
}

function SendHTMLMail($from_address, $to_address, $bcc_address, $subject, $message, $attachments=null, $list_id = null, $mail_id = null) {
    
    if (OptedOut($to_address)) {
        
        if (DEBUG_MODE) {
            DebugBox("SendHTMLMail", "address:$to_address is opted out");
        }
        
        return false;
        
    } 
    
    //--- Remove slashes from email content just in case they where missed
    $message = stripslashes($message. "<img src=\"" . THISURL . PATH . "/rest/email_callback.php?list_id=$list_id&mail_id=$mail_id\" style=\"height:0px;width:0px\" />");
    
    if (!$attachments) {
        
        //--- To send HTML mail, the Content-type header must be set
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
        
        //--- Additional headers
        $headers .= "To: <$to_address>" . "\r\n";
        $headers .= "From: <$from_address>" . "\r\n";
        $headers .= "Bcc: $bcc_address" . "\r\n";
        
    } else {
        
        $random_hash = md5(date('r', time())); 
        
        //--- To send html mail with attachments
        $headers = "Content-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"\r\n\r\n";
        
        //--- Additional headers
        $headers .= "To: <$to_address>" . "\r\n";
        $headers .= "From: <$from_address>" . "\r\n";
        $headers .= "Bcc: $bcc_address" . "\r\n";
        $headers .= "Return-Path:" . BOUNCE_EMAIL . "\r\n";
        $headers .= "X-Confirm-Reading-To:" . BOUNCE_EMAIL . "\r\n";
        
        $message_content .= "--PHP-mixed-" . $random_hash . "\r\n";  
        $message_content .= "Content-Type: multipart/alternative; boundary=\"PHP-alt-" . $random_hash . "\"\r\n\r\n";
        
        $message_content .= "--PHP-alt-" . $random_hash . "\r\n";  
        $message_content .= "Content-Type: text/html; charset=\"iso-8859-1\"\r\n"; 
        $message_content .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        
        //--- Combine message with read recipt
        $message_content .= $message . "\r\n";
        
        $message_content .= "--PHP-alt-" . $random_hash . "--\r\n\r\n";  
        
        foreach ($attachments as $attachment) {
            
            $message_content .= "--PHP-mixed-" . $random_hash ."\r\n";  
            $message_content .= "Content-Type: " . $attachment['mime_type'] . "; name=\"" . $attachment['filename'] . "\"\r\n";  
            $message_content .= "Content-Transfer-Encoding: base64\r\n";  
            $message_content .= "Content-Disposition: attachment\r\n\r\n";  
            $message_content .= chunk_split($attachment['data']) . "\r\n";
            $message_content .= "--PHP-mixed-" . $random_hash . "--\r\n"; 

        }
        
        $message = $message_content;        
        
    }

    if (DEBUG_MODE) {
        
        DebugBox("Mail send debug", debug_backtrace());
        
    }
    
    //--- Mail it
    if(!mail($to_address, $subject, $message, $headers)) {
        
        UpdateLog(debug_backtrace(), "Failed to send scheduled email Dumping...:[to:$to_address][subject:$subject][message body:$message][headers:$headers]", "mail_errors.txt");
        return false;
    } else { 
        
        return true;
        
    }
    
}
/*
function GenerateCSV($filename, $DataARY) {

    //--- Notify the browser that this is a CSV document
    header("Cache-control: private");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Description: File Transfer");
    header("Content-disposition: attachment; filename=$filename.csv");

    //--- Show the header columns of the csv as the key from the data array
    $HeadersARY = array_keys($DataARY[0]);
    
    //--- Send the headers as the first line of the csv
    foreach ($HeadersARY as $Header) {
        
        $CSVHeader .= $Header .",";        
        
    }
        
    //--- trim the comma from the last column
    $CSVHeader = rtrim ($CSVHeader, ",");
    
    //--- Remove any function names in the header
    $CSVHeader = str_replace("_", " ", $CSVHeader);

    //--- Write the header to the file    
    echo trim($CSVHeader) ."\n";
    

    //--- Display the data for each header here
    foreach ($DataARY as $Data) {
        
        //--- Display the data as per header        
        foreach ($HeadersARY as $Header) {
            
            switch ($Header) {
            
                //--- Convert images to base64 string and send to Ensight
                case "Picture":
                    
                    //--- Open the image
                    if(@$FH = fopen($Data[$Header], "r")) {
                    
                        //--- Read the image into a string
                        @$PictureSTR = fread($FH, filesize($Data[$Header]));
                        
                        //--- Save the image as a string
                        $Row .= base64_encode($PictureSTR) . ",";
                        
                        //--- Close the file handle
                        @fclose($FH);
                    
                    } else {
                        
                        //--- Save as blank
                        $Row .= ",";
                    }
                    
                break;    
    
                default:     

                    //--- Just save it as is
                    $Row .= $Data[$Header] .",";

                break;

            }
        
        }
        //--- trim the comma from the last column
        $Row = rtrim ($Row, ",");
        
        //--- Write the row to a file
        echo $Row . "\n";
        
        $Row="";
    }
        
}
*/

function TimeEntryField ($FieldName, $SelectedValue = "", $Attributes = "") {
    
    //--- seperate passed times minutes and hours
    if ($SelectedValue!="") {
        
        $Time = explode (":", $SelectedValue);
        
    }
    
    //--- Hours
    echo "\n<select onchange='UpdateTimeField_$FieldName()' id='hours_field_$FieldName' $Attributes>";
    
    for ($i=0;$i<=23;$i++) {
        
        echo "\n\t<option " . ($Time[0] == $i ? "SELECTED='SELECTED' " : "") . " value='". (strlen($i)<2 ? "0" . $i : $i) ."'>". (strlen($i)<2 ? "0" . $i : $i) ."</option>";
        
    }
    
    echo "\n</select>";
    
    //--- Minutes
    echo "\n<select onchange='UpdateTimeField_$FieldName()' id='minutes_field_$FieldName' $Attributes>";

    for ($i=0;$i<=59;$i++) {
        
        echo "\n\t<option " . ($Time[1] == $i ? "SELECTED='SELECTED' " : "") . "value='". (strlen($i)<2 ? "0" . $i : $i) ."'>". (strlen($i)<2 ? "0" . $i : $i) ."</option>";
        
    }
    
    echo "\n</select>";
    
    echo "\n<input type='hidden' value='$SelectedValue' name='$FieldName' id='full_time_field_$FieldName' />";
    
    echo "\n
    <script>
    function UpdateTimeField_$FieldName() {
        document.getElementById('full_time_field_$FieldName').value = document.getElementById('hours_field_$FieldName').value + ':' + document.getElementById('minutes_field_$FieldName').value;
    } 
    </script>";
}

function LoginUser($user_id) {
    
    session_start();
    unset($_SESSION['emailer']);//kill all active sessions
    
    //--- Get the users details into an array
    $User = RetrieveUser($user_id);

    //--- Only one record returned so overwrite the variable
    $User = $User[0];
    
    $_SESSION['emailer'] = $User; 
    
    //--- Set a custom session name
    session_name(["DRM_Emailer"]);
    
    SaveEvent($_SESSION['emailer']['id'], "Logged in to admin tool", date("Y-m-d H:i:s"));
    
    //--- Return the unique session of the logged in user
    return session_id();
    
}

function LogoutUser () {
    
    session_start();
    SaveEvent($_SESSION['emailer']['id'], "Logged out of admin tool", date("Y-m-d H:i:s"));
    DeleteUserSession($_SESSION['emailer']['id']);
    unset($_SESSION['emailer']);//kill all active sessions
    
}

function OptedOut ($email_address) {
    
    $SQL = "SELECT count(*) as `found` FROM unsubscribe WHERE email_address = '$email_address'";
    $Response = ExecuteQuery($SQL);

    if ($Response[0]['found']>0) {
        
        return true;
        
    } else {
        
        return false;
    }
}

function UpdateBreadcrumb ($pagename, $url) {
    
    // session_start();
    
    //--- Protect againist updating the breadcrumb on page reload
    if ($_SESSION['emailer']["breadcrumb"][count($_SESSION['emailer']["breadcrumb"])-1]['url'] != $url) {
    
        $_SESSION['emailer']["breadcrumb"][]['pagename'] = $pagename;
        $_SESSION['emailer']["breadcrumb"][count($_SESSION['emailer']["breadcrumb"])-1]['url'] = $url;
        
    }
}

function BreadcrumbShow () {
    
    echo "<p style='font:bold'>";
    
    //--- Determine where to start of you want to get the last 3 records
    $s = count($_SESSION['emailer']['breadcrumb']) - 6;
    
    echo "<a class='breadcrumb' href='home.php'>Home</a> ";
    for ($i=$s; $i<count($_SESSION['emailer']['breadcrumb']); $i++) {
        
        if ($_SESSION['emailer']["breadcrumb"][$i]['pagename']!="") {
            echo " / <a class='breadcrumb' href='" . $_SESSION['emailer']["breadcrumb"][$i]["url"] . "'>" . $_SESSION['emailer']["breadcrumb"][$i]['pagename'] . "</a>";
        }
    }
    echo "</p>";

}

function GetFolderContents ($folder) {
//--- Gets list of files from the template directory

    $Directory = HOMEPWD . PATH . "/" . $folder;
    $Dir = opendir($Directory);

    if (DEBUG_MODE) {

        DebugBox("debug: Contents directory", $Directory);

    }

    if (is_resource($Dir)) {

        while ($File = readdir($Dir)) {
            
            if (DEBUG_MODE) {
                
                DebugBox("debug: Filenames", $File);
                
            }
            
            if ($File == "." || $File == ".." || $File == "images") continue;
            $Data[] = $File;// $File;
            
        }
        
        return $Data;
        
    } else {
        
        UpdateLog(debug_backtrace(), "unable to open dir");
        $response = false;
        
    }
    
    if (is_array($Data)) {
        
        $response = true;
        
    } else {
        
        UpdateLog(debug_backtrace(), "no files found in directory.");
        $response = false;
        
    }
    
    if (DEBUG_MODE) {
        
        echo "<div style='border:1px solid red; color:red'>";
        debug_print_backtrace();
        echo "</div>";     
        
    } 

    return $response;
}

function GetFileContents ($file) {
    
    if ($handle = fopen(HOMEPWD . PATH . "/" . $file, "r")) {
        
        if ($Contents = fread($handle, filesize(HOMEPWD . PATH . "/" . $file))) {
            
            //--- Return the contents of the file
            $response = $Contents;
            
        } else {
            
            //--- Failed to read the file
            UpdateLog(debug_backtrace(), "unable to read file");
            $response = false;
        }
        
    } else {
        
        //--- Failed to open file
        UpdateLog(debug_backtrace(), "unable to open file");
        $response = false;
        
    }
    
    if (DEBUG_MODE) {
        
        echo "<div style='border:1px solid red; color:red'>";
        debug_print_backtrace();
        echo "</div>";     
        
    }
    
    return $response;
    
}

function DebugBox ($title, $content) {
    
    echo "<div style='color:red; border: 1px solid red'>";
    echo "<b>---" . $title . "---</b>";
    echo "<p>$content</p>";
    echo "</div>";
    
}

function ValidateEmailAddress($email) {
    
    // First, we check that there's one @ symbol, 
    // and that the lengths are right.
    if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
        // Email invalid because wrong number of characters 
        // in one section or wrong number of @ symbols.
        return false;
    }
    
    // Split it into sections to make life easier
    $email_array = explode("@", $email);
    
    $local_array = explode(".", $email_array[0]);
    
    for ($i = 0; $i < sizeof($local_array); $i++) {
        if(!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&↪'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$",$local_array[$i])) {
            return false;
        }
    }
     
    // Check if domain is IP. If not, 
    // it should be valid domain name
    if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
        
        $domain_array = explode(".", $email_array[1]);
        
        if (sizeof($domain_array) < 2) {
            
            return false; // Not enough parts to domain
            
        }
        
        for ($i = 0; $i < sizeof($domain_array); $i++) {
            if(!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|↪([A-Za-z0-9]+))$",$domain_array[$i])) {
                return false;
            }
        }
    }
    
    return true;
}

function DisplayRSS ($url, $limit=5) {

    $xmlDoc = new DOMDocument();
    
    if(@$xmlDoc->load($url)) {

        //get elements from "<channel>"
        $channel=$xmlDoc->getElementsByTagName('channel')->item(0);
        $channel_title = $channel->getElementsByTagName('title')
        ->item(0)->childNodes->item(0)->nodeValue;
        $channel_link = $channel->getElementsByTagName('link')
        ->item(0)->childNodes->item(0)->nodeValue;
        $channel_desc = $channel->getElementsByTagName('description')
        ->item(0)->childNodes->item(0)->nodeValue;

        //output elements from "<channel>"
        /*
        echo("<p><a href='" . $channel_link
          . "'>" . $channel_title . "</a>");
        echo("<br />");
        echo($channel_desc . "</p>");
        */
        
        //get and output "<item>" elements
        $x=$xmlDoc->getElementsByTagName('item');
        
        for ($i=0; $i<=$limit; $i++){
            
            $item_title=$x->item($i)->getElementsByTagName('title')->item(0)->childNodes->item(0)->nodeValue;
            $item_link =$x->item($i)->getElementsByTagName('link')->item(0)->childNodes->item(0)->nodeValue;
            $item_desc=$x->item($i)->getElementsByTagName('description')->item(0)->childNodes->item(0)->nodeValue;

            $c++;
            echo ("<p><a style='text-weight:bolder; text-decoration: underline;' target='_blank' href='" . $item_link . "'>" . $item_title . "</a>");
            echo ("<br />");
            echo ($item_desc . "</p>");
            
        }
    }
    
}

function GenerateCSV($filename, $DataARY) {

    //--- Notify the browser that this is a CSV document
    header("Cache-control: private");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Description: File Transfer");
    header("Content-disposition: attachment; filename=$filename.csv");

    //--- Show the header columns of the csv as the key from the data array
    $HeadersARY = array_keys($DataARY[0]);
    
    //--- Send the headers as the first line of the csv
    foreach ($HeadersARY as $Header) {
        
        $CSVHeader .= $Header .",";        
        
    }
        
    //--- trim the comma from the last column
    $CSVHeader = rtrim ($CSVHeader, ",");
    
    //--- Remove any function names in the header
    $CSVHeader = str_replace("_", " ", $CSVHeader);

    //--- Write the header to the file    
    echo trim($CSVHeader) ."\n";
    

    //--- Display the data for each header here
    foreach ($DataARY as $Data) {

        //--- Display the data as per header        
        foreach ($HeadersARY as $Header) {

            //--- Just save it as is
            $Row .= $Data[$Header] .",";
        
        }
        //--- trim the comma from the last column
        $Row = rtrim ($Row, ",");
        
        //--- Write the row to a file
        echo $Row . "\n";
        
        $Row="";
    }
        
}
?>
