<?php
include_once 'modules/DB.php';

$PageTitle = "Schedule email send";
$PageBackground = "images/icons/Calendar.png";

include_once ("header.php"); //starts session and displays all top contents

extract ($_GET);
extract ($_POST);

//--- Permission check
if ($Permissions[$_SERVER['PHP_SELF']] > $_SESSION['emailer']['permission_emails']) {
    echo "<span>You do not have permission to access this page</span>";
    include_once ("footer.php");
    die;
} 

//--- Get the list of campaigns
$Schedule = RetrieveCampaignEmailSchedule ($cid, $eid);
$Schedule = $Schedule[0];

?>
<form action="actions.php" method="get" id="campaignScheduleForm">
<input type="hidden" name="campaign_id" value="<?php echo $cid ?>" />
<input type="hidden" name="mail_id" value="<?php echo $eid ?>" />
<br/>Send Date</br/>
<input id="datepicker1" class="required" type="text" name="send_start_date" value="<?php echo ($Schedule['send_start_date']!='0000-00-00 00:00:00' ? substr($Schedule['send_start_date'],0,10) : date("Y-m-d")) ?>" />
<br/>Start Time</br/>
<?php

//--- Convert saved date time to time
if ($Schedule['send_start_date']!="0000-00-00 00:00:00" && $Schedule['send_start_date'] != "") {
    $Time = date("H:i", strtotime($Schedule['send_start_date']));
} else {
    $Time = date("H:i");
}

echo TimeEntryField("send_start_time", ($Time!="" ? $Time : date("H:i:s")), " class='required' ");
?>
<input type="hidden" name="a" value="4" />
<input type="hidden" name="r" value="campaign_editor.php?cid=<?php echo $cid ?>" />
<p><input type="button" onclick="document.location.href='<?php echo $_SERVER['HTTP_REFERER']; ?>'" value="<< Back"/><input type="submit" value="Save" /></p>
</form>

<script>
$(function() {
    $( "#datepicker1" ).datepicker({ dateFormat: 'yy-mm-dd' });
});

$(document).ready(function(){
    $("#campaignScheduleForm").validate();
});

</script>

<?php
include_once ("footer.php");
?>

