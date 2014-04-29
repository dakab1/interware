<?php
global $PageTitle, $PageBackground;
$PageTitle = "Campaigns editor " . ($_GET['cid'] ? " - Campaign #" . $_GET['cid'] : "");
$PageBackground = "images/icons/TextEdit.png";
include_once ("modules/DB.php");
include_once ("header.php"); //starts session and displays all top contents
include_once ("modules/UI.php");

extract ($_GET);

//--- Permission check
if ($Permissions[$_SERVER['PHP_SELF']] > $_SESSION['emailer']['permission_campaigns']) {
    echo "<span>You do not have permission to access this page</span>";
    include_once ("footer.php");
    die;
} 

//--- Check if there is a campaign that needs to be loaded
if (isset($cid)) { 
    $CampaignID = $cid;
    $Campaign = RetrieveCampaign($CampaignID);
    $_SESSION['emailer']['cid'] = $cid;
}


?>
<div id="message"></div>

<form id="campaignForm" action="actions.php" target="process" method="get">
Name <br/><input value="<?php echo $Campaign[0]['name']; ?>" class="required" type="text" name="name" />
<br/>Start Date <br/><input class="required" id="datepicker1" type="text" name="start_date" value="<?php echo substr($Campaign[0]['start_date'],0,10); ?>"/>
<br/>End Date  <br/><input id="datepicker2" type="text" name="end_date" value="<?php echo ($Campaign[0]['end_date'] == "0000-00-00 00:00:00" ? "" : substr($Campaign[0]['end_date'],0,10)); ?>"/>
<input type="hidden" name="user_id" value="1" />
<input type="hidden" name="status" value="0"/>
<input id="CampaignID" type="hidden" name="id" value="<?php echo (isset ($CampaignID) ? $CampaignID : '0'); ?>" />
<input type="hidden" name="s" value="<?php echo $s; ?>" />
<input type="hidden" name="a" value="1" />
<br/><input type="hidden" name="r" value="email_editor.php" />
<input type="submit" value="Save" />
</form>

<h3>Scheduled Emails</h3>
<div id="emails_display">
    <form target="process">
    <?php
    //--- Display the list of scheduled emails under this campaign
    echo TableStart();
    echo TableHeaderRow(array("Email ID", "Subject", "Send date", ""));
    
    //--- Get list of emails under this campaign
    if (isset($CampaignID) && $CampaignID) {
        $Emails = RetrieveEmails($CampaignID, " LIMIT 0,3");
    
        if (is_array($Emails)) {

            foreach ($Emails as $Email) {

                //--- Get the number of recipients 
                $Count = RetrieveRecipientCount($Email['mail_id']);
                $Count = $Count[0]['recipients'];
                $email_preview_link = "email_preview.php?eid=" . $Email['mail_id'];
                
                if (!isEmailSent($Email['mail_id'])) {
                    $email_edit_link = "email_editor.php?eid=" . $Email['mail_id'] . "&cid=" . $cid . "&r=" . urlencode("campaign_editor.php?cid=" . $cid) . "&";
                    $email_schedule_link = "email_schedule_editor.php?cid=" . $cid . "&eid=" . $Email['mail_id'];
                } else {
                    $email_edit_link = "javascript:alert(\"Sorry, cannot edit an email that has already been queued or sent.\")";
                    $email_schedule_link = "javascript:alert(\"Sorry, cannot edit an email that has already been queued or sent.\")";
                }
                
                echo TableRow(array(
                    "<a href='" . $email_edit_link ."'>" . $Email['mail_id'] . "</a>", 
                    "<a href='" . $email_edit_link ."'>" . $Email['subject']. "</a>", 
                    "<a href='" . $email_schedule_link . "'>" . $Email['send_date'] ."</a>",
                    "   <a href='" . $email_edit_link ."'>Edit Email</a>&nbsp;&nbsp;
                        <a href='" . $email_preview_link ."'>Preview Email</a>&nbsp;&nbsp;
                        <a href='recipient_list_view.php?eid=" . $Email['mail_id'] ."'>View Recipients ($Count)</a>&nbsp;&nbsp;
                        <a href='" . $email_schedule_link. "'>View schedule</a>&nbsp;&nbsp;
                        <a href='#'>Cancel</a>"
                    ));

            }
        } else {

            echo "\n\t<tr><td colspan='3'>No emails as yet</td></tr>";
        }
        
    } else {
        
            echo "\n\t<tr><td colspan='3'>No emails as yet</td></tr>";
        
    }
    echo TableEnd();
    ?>
    </form>
    <input type="button" value="Create new" onclick="
        if(CampaignSaved()) {
            $(location).attr('href','email_editor.php?cid=' + document.getElementById('CampaignID').value);
        } else {
            
            $('#alert-modal').dialog('destroy');

            $('#alert-modal').dialog({
                                    height: 140,
                                    modal: true
		});
            $('#alert-modal').dialog('open');
        }
   "/>
    <input type="button" onclick="document.location.href='email_list_view.php?cid=' + document.getElementById('CampaignID').value" value="View All" />
</div>

<h3>Scheduled Social Posts</h3>
<div id="social_post_display">
    <?php
    $ScheduledPosts = RetrieveSocialPostsSchedule(false, " WHERE `campaign_id`='$cid' ORDER BY `send_date` DESC LIMIT 3");

    if (is_array($ScheduledPosts)) {

        echo TableStart();
        echo TableHeaderRow(array("Posted by", "To account","Content", "Send date"));

        foreach($ScheduledPosts as $Schedule) {

            $Post = RetrieveSocialPosts($Schedule['social_post_id']);
            $User = RetrieveUser($Post[0]['user_id']);
            $Network = RetrieveOauth($Schedule['social_network_oauth_id']);
            echo TableRow(array($User[0]['name'], $Network[0]['description'] . " (" . $Network[0]['network'] . ")", $Post[0]['text'], $Schedule['send_date']));

        }

        echo TableEnd();
    } else {

        echo "<p>No posts for this campaign as yet.</p>";
    }
    ?>

    <input type="button" value="Create new" onclick="
           
        if(CampaignSaved()) {
            $(location).attr('href','social_posts.php?cid=' + document.getElementById('CampaignID').value);
        } else {
            
            $('#alert-modal').dialog('destroy');

            $('#alert-modal').dialog({
                                    height: 140,
                                    modal: true
		});
            $('#alert-modal').dialog('open');
        }
    "/>
    
    <input type="button" onclick="" value="View All" />
    
</div>

<h3>Scheduled SMS</h3>
<div id="sms">
<?php
    //--- Display the list of scheduled emails under this campaign
    echo TableStart();
    echo TableHeaderRow(array("SMS ID", "Text", "Send date", ""));

    //--- Get list of emails under this campaign
    if (isset($CampaignID) && $CampaignID) {
        $SMSs = RetrieveQueuedSMSByCampaign($CampaignID, " LIMIT 0,3");

        if (is_array($SMSs)) {

            foreach ($SMSs as $SMS) {

                //--- Get the number of recipients 
                $Count = RetrieveSMSRecipientCount($SMS['id']);
                $Count = $Count[0]['recipients'];

                if (!IsSMSSent($SMS['id'])) {
                    $sms_edit_link = "sms_editor.php?sms_id=" . $SMS['id'] . "&cid=" . $cid . "&r=" . urlencode("campaign_editor.php") . "&";
                    $sms_schedule_link = "sms_schedule_editor.php?cid=" . $cid . "&sms_id=" . $SMS['id'];
                } else {
                    $sms_edit_link = "javascript:alert(\"Sorry, cannot edit an sms that has already been queued or sent.\")";
                    $sms_schedule_link = "javascript:alert(\"Sorry, cannot edit an sms that has already been queued or sent.\")";
                }
                
                $sms_recipients_link = "recipient_list_view.php?sms_id=" . $SMS['id'];
                
                echo TableRow(array(
                    "<a href='" . $sms_edit_link ."'>" . $SMS['id'] . "</a>", 
                    "<a href='" . $sms_edit_link ."'>" . $SMS['text']. "</a>", 
                    "<a href='" . $sms_schedule_link . "'>" . $SMS['send_start_date'] ."</a>",
                    "   <a href='" . $sms_edit_link ."'>Edit SMS</a>&nbsp;&nbsp;
                        <!--<a href='" . $email_preview_link ."'>Preview SMS</a>&nbsp;&nbsp;-->
                        <a href='" . $sms_recipients_link . "'>View Recipients ($Count)</a>&nbsp;&nbsp;
                        <a href='" . $sms_schedule_link . "'>View schedule</a>&nbsp;&nbsp;
                        <a href='#'>Cancel</a>"
                    ));

            }
        } else {

            echo "\n\t<tr><td colspan='3'>No SMSs as yet</td></tr>";
        }

    } else {

            echo "\n\t<tr><td colspan='3'>No SMSs as yet</td></tr>";

    }
    echo TableEnd();
    ?>
    <input type="button" value="Create new" onclick="
        if(CampaignSaved()) {
            $(location).attr('href','sms_editor.php?cid=' + document.getElementById('CampaignID').value);
        } else {
            
            $('#alert-modal').dialog('destroy');

            $('#alert-modal').dialog({
                                    height: 140,
                                    modal: true
		});
            $('#alert-modal').dialog('open');
        }
           
    "/>
    <input type="button" onclick="document.location.href='sms_list_view.php?cid=' + document.getElementById('CampaignID').value" value="View All" />
</div>    

<div id="alert-modal" title="Basic modal dialog" style="display:none">
	<p>Please save the Campaign first.</p>
</div>


<iframe src="blank" style="height:0px; width: 0px; border:0px" name="process"></iframe>

<script>
$(function() {
    $( "#datepicker1" ).datepicker({ dateFormat: 'yy-mm-dd' });
    $( "#datepicker2" ).datepicker({ dateFormat: 'yy-mm-dd' });
});

$(document).ready(function(){
    $("#campaignForm").validate();
});

function CampaignSaved () {
    
    CampaignID = document.getElementById("CampaignID").value;
    
    if(CampaignID == "" || CampaignID == null || CampaignID == 0) {
        
        return false;
        
    } else {
        
        return true;
        
    }
    
}
</script>

<?php
include_once ("footer.php");
?>