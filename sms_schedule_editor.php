<?php
include_once 'modules/DB.php';

$PageTitle = "Schedule sms send";
$PageBackground = "images/icons/Calendar.png";

include_once ("header.php"); //starts session and displays all top contents

extract ($_GET);
extract ($_POST);

//--- Permission check
if ($Permissions[$_SERVER['PHP_SELF']] > $_SESSION['emailer']['permission_sms']) {
    echo "<span>You do not have permission to access this page</span>";
    include_once ("footer.php");
    die;
}

if (!isset($cid) || $cid=="") $cid = 1; //set to default campaign

$Schedule = RetrieveSMSSchedule($sms_id, $cid);
$Schedule = $Schedule[0];

?>
<script type="text/javascript">

function Validate (formObj) {
    /*
    var DateObj = new Date();
    var CurrentTime = DateObj.getFullYear() + "-" + (DateObj.getMonth() + 1) + DateObj.getDate() + " ";
    
    if (formObj.elements['send_start_time'].value <= CurrentTime){
        
        if (confirm('The send time falls in the past, clicking Ok will send the message now. Are you sure you want to proceed.')) {
            
            return true;
            
        } else {
            
            alert ('Please enter a send time that is in the future');
            return false;
            
        }
    }
    */
   
   return true;
    
}   

</script>
<form action="actions.php" method="get" id="campaignScheduleForm" onsubmit="return Validate(this)">
<input type="hidden" name="cid" value="<?php echo $cid ?>" />
<input type="hidden" name="sms_id" value="<?php echo $sms_id ?>" />
<br/>Send Date</br/>
<input id="datepicker1" class="required" type="text" name="send_start_date" value="<?php echo (is_array($Schedule) && $Schedule['send_start_date'] != "0000-00-00 00:00:00"? substr($Schedule['send_start_date'],0,10) : date("Y-m-d")) ?>" />
<br/>Start Time</br/>
<?php

//--- Convert saved date time to time
if ($Schedule['send_start_date'] != "0000-00-00 00:00:00" && is_array($Schedule)) {
    $Time = date("H:i", strtotime($Schedule['send_start_date']));
}else {
    $Time = date ("H:i");
}

echo TimeEntryField("send_start_time", $Time, " class='required' ");
?>
<input type="hidden" name="a" value="13" />
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

