<?php
$PageTitle = "SMS list view";
$PageBackground = "images/icons/SendSMS.png";

include_once ("modules/DB.php");
include_once ("header.php");
include_once ("modules/UI.php");

extract($_GET);

//--- Permission check
if ($Permissions[$_SERVER['PHP_SELF']] > $_SESSION['emailer']['permission_sms']) {
    echo "<span>You do not have permission to access this page</span>";
    include_once ("footer.php");
    die;
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

    $Total = RetrieveSMSs(null, true);

    $Total = $Total[0]['count(*)'];

    echo "<p>Total Number of SMS:$Total</p>";

    //--- Show pagination
    echo "\nPage : <select onchange=\"document.location.href='" . $_SERVER['SCRIPT_NAME'] . "?" . "page=' + this.value\">";

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

//--- Display the list of scheduled emails under this campaign
echo TableStart();
echo TableHeaderRow(array("SMS ID", "Text",  ""));

//--- Get list of sms under this campaign
if (!$cid) {
    $SMSs = RetrieveSMSs($limit); //RetrieveQueuedSMSByCampaign($CampaignID, " LIMIT 0,3");
} else {
    $SMSs = RetrieveSMSByCampaign($cid); //RetrieveQueuedSMSByCampaign($CampaignID, " LIMIT 0,3");
}

if (is_array($SMSs)) {

    foreach ($SMSs as $SMS) {

        //--- Get the number of recipients 
        $Count = RetrieveSMSRecipientCount($SMS['id']);
        $Count = $Count[0]['recipients'];
        
        if (!IsSMSSent($SMS['id'])) {
            $sms_edit_link = "sms_editor.php?sms_id=" . $SMS['id'] . "&cid=" . $cid . "&r=" . urlencode("campaign_editor.php?cid=" . $cid) . "&";
            $sms_schedule_link = "sms_schedule_editor.php?cid=" . $cid . "&sms_id=" . $SMS['id'];
        } else {          
            $sms_edit_link = "javascript:alert(\"Sorry, cannot edit an sms that has already been queued or sent.\")";
            $sms_schedule_link = "javascript:alert(\"Sorry, cannot edit an sms that has already been queued or sent.\")";
        }
        
        $sms_recipients_link = "recipient_list_view.php?sms_id=" . $SMS['id'];
        $sms_preview_link = "sms_preview.php?sms_id=" . $SMS['id'];
        echo TableRow(array(
            "<a href='" . $sms_edit_link ."'>" . $SMS['id'] . "</a>", 
            "<a href='" . $sms_edit_link ."'>" . $SMS['text']. "</a>", 
            "<span><a href='" . $sms_edit_link ."'>Edit</a></span>&nbsp;&nbsp;
             <span><a href='" . $sms_preview_link ."'>Preview</a></span>&nbsp;&nbsp;
             <span><a href='" . $sms_recipients_link . "'>View Recipients ($Count)</a></span>&nbsp;&nbsp;
             <span><a href='" . $sms_schedule_link . "'>View schedule</a></span>&nbsp;&nbsp;"
            ));

    }
} else {

    echo "\n\t<tr><td colspan='3'>No SMSs as yet</td></tr>";
}

echo TableEnd();

?>
