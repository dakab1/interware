<?php
$PageTitle = "Recipients list";
$PageBackground = "images/icons/Contactlist.png";

include_once("modules/DB.php");
include_once("modules/UI.php");
include_once("header.php");

extract ($_GET);

//--- Permission check
if ($Permissions[$_SERVER['PHP_SELF']] > $_SESSION['emailer']['permission_recipients']) {
    echo "<span>You do not have permission to access this page</span>";
    include_once ("footer.php");
    die;
} 

if ($eid) {
    ?>
    <p><input type="button" value="Upload new list" onclick="document.location.href='upload_csv.php?eid=<?php echo $eid ?>&cid=<?php echo (isset($cid) ? $cid : $_SESSION['emailer']['cid']); ?>&r=<?php echo urlencode("recipient_list_view.php?eid=$eid"); ?>'" /><input type="button" value="Download as CSV" onclick="document.location.href='print.php?a=email_list&eid=<?php echo $eid; ?>'" /></p>
    <?php
} elseif ($sms_id) {
    ?>
    <p><input type="button" value="Upload new list" onclick="document.location.href='sms_upload_csv.php?sms_id=<?php echo $sms_id ?>&cid=<?php echo (isset($cid) ? $cid : $_SESSION['emailer']['cid']); ?>&r=<?php echo urlencode("recipient_list_view.php?sms_id=$sms_id"); ?>'" /><input type="button" value="Download as CSV" onclick="document.location.href='print.php?a=sms_list&sms_id=<?php echo $sms_id; ?>'" /></p>
    <?php
}
?>
    
<div class="pagination">
    <?php
    if (!isset($page)) {
        $page = 1;// default to page 1
    } elseif($page<1) {
        $page=1;  	 
    }

    $limit = "LIMIT " . (($page-1) * UI_ITEMS_PER_PAGE) . "," . UI_ITEMS_PER_PAGE;
    
    if (isset($eid)) {
        
        $Total = RetrieveRecipientCount(($eid!=""?$eid:""));
        
    } else if (isset($sms_id)){
        
        $Total = RetrieveSMSRecipientCount($sms_id);
        
    } else {
        
        echo "Please pass Email ID or SMS ID.";
        include_once ("footer.php");
        die;
        
    }

    $Total = $Total[0]['recipients'];

    echo "<p>Total Number of Recipients:$Total</p>";

    //--- Show pagination
    echo "\nPage : <select onchange=\"document.location.href='" . $_SERVER['SCRIPT_NAME'] . "?" . ($eid!=""?"eid=$eid&":""). "page=' + this.value\">";

    for($i=1;$i<=(($Total/UI_ITEMS_PER_PAGE)+1);$i++) {

        echo "\n\t<option " .($i == $page ? "selected=selected" : "") . " >$i</option>";

    }

    echo "\n</select>";
    ?>
</div>
<!--
<div class="searchBox">
    <input type="text" name="q" />
    <input type="submit" target="" value="Search" />
</div>
-->

<?php


if ($eid) {
    
    $Recipients = RetrieveRecipientsByEmail ($eid, $limit);
    
} else {
    
    $Recipients = RetrieveRecipientsBySMS ($sms_id, $limit);
    
}

//--- Display queued campaigns
echo TableStart();

if (isset($eid)) {

    echo TableHeaderRow(array("Reciepient ID", "Email Address", "Queued Date", "Sent Date","Status",""));

} else {

    echo TableHeaderRow(array("Reciepient ID", "Number (MSISDN)", "Queued Date", "Sent Date","Status",""));

}

if (is_array($Recipients)) {
    

    foreach($Recipients as $Recipient) {

        switch($Recipient['status']) {
            case STATUS_CANCELLED:
                $Recipient['status'] = "Cancelled";
            break;

            case STATUS_NOT_SENT:
                $Recipient['status'] = "Not yet sent";
            break;

            case STATUS_QUEUED:
                $Recipient['status'] = "Queued";
            break;

            case STATUS_SENT:
                $Recipient['status'] = "Sent";
            break;
        }

        if ($eid) {

            echo TableRow(array($Recipient['id'], "<a href='email_view.php?eid=" . $Recipient['mail_id'] . "'>" . stripcslashes($Recipient['email']) . "</a>", $Recipient['date_queued'], $Recipient['date_sent'], $Recipient['status'], ""));

        } else {

            echo TableRow(array($Recipient['id'], "<a href='sms_view.php?sms_id=" . $Recipient['sms_id'] . "'>" . stripcslashes($Recipient['msisdn']) . "</a>", $Recipient['date_queued'], $Recipient['date_sent'], $Recipient['status'], ""));

        }

    }
} else {
    
    echo "\n<tr><td colspan='6'>No recipients loaded yet</td></tr>";
}

echo TableEnd();


include_once ("footer.php");
?>
