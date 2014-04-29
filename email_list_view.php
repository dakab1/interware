<?php
$PageTitle = "Emails List";
$PageBackground = "images/icons/Messages.png";

include_once("modules/DB.php");
include_once("modules/UI.php");
include_once("header.php");

extract($_GET);

//--- Permission check
if ($Permissions[$_SERVER['PHP_SELF']] > $_SESSION['emailer']['permission_emails']) {
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

    $Total = RetrieveEmailsCount(($cid ? $cid : false));

    $Total = $Total[0]['emails'];

    echo "<p>Total Number of Emails:$Total</p>";

    //--- Show pagination
    echo "\nPage : <select onchange=\"document.location.href='" . $_SERVER['SCRIPT_NAME'] . "?s=$s&" . ($cid!=""?"cid=$cid&":""). "page=' + this.value\">";

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
$Emails = RetrieveEmails(($cid != "" ? $cid : ""));

//--- Display queued campaigns
echo TableStart();
echo TableHeaderRow(array("Email ID", "Subject", "",""));

if (is_array($Emails)){
    
    foreach($Emails as $Email) {

        //--- Get the number of recipients 
        $Count = RetrieveRecipientCount($Email['mail_id']);
        $Count = $Count[0]['recipients'];
        
        if (!isEmailSent($Email['mail_id'])) {
            $edit_link = "email_editor.php?eid=" . $Email['mail_id']. "";
            $email_schedule_link = "email_schedule_editor.php?cid=" . ($cid ? $cid : GetEmailCampaignIDByMailID($Email['mail_id'])) . "&eid=" . $Email['mail_id'];
        } else {
            $edit_link = "javascript:alert(\"Sorry, cannot edit an email that has already been queued or sent.\")";
            $email_schedule_link = "javascript:alert(\"Sorry, cannot edit an email that has already been queued or sent.\")";
        }
 
        echo TableRow(
                array(
                    "<a href='$edit_link'>" . $Email['mail_id'] . "</a>", 
                    "<a href='$edit_link'>" . $Email['subject'] . "</a>", 
                    "<a href='$email_schedule_link'>View schedule</a>", 
                    "<a style='float:left' href='recipient_list_view.php?eid=" . $Email['mail_id'] ."'>Recipients ($Count)</a>"
                ));

    }

} else {
    
    echo "<tr><td colspan='4'>The are no emails to view as yet.</td></tr>";
    
}

echo TableEnd();

include_once ("footer.php");
?>
