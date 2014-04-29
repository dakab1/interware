<?php
$PageTitle = "Edit user #" . $_GET['uid'];

include_once ("modules/DB.php");
include_once ("modules/UI.php");
include_once ("header.php");

//--- Check if there is a user that should be preloaded
extract($_GET);

if (isset($uid)) {
    
    $User = RetrieveUser($uid);
    
}

$User=$User[0]; //because the function above returns one row
?>

<script>
$(document).ready(function(){
    $("#registrationForm").validate();
});
</script>

<form id="registrationForm" action="actions.php" method="post" autocomplete="off">
    <h2>Login details</h2>
    <p>Enter the users login and authentication details.</p>
    <br/>Name<br/> <input type="text" name="username" class="required" value="<?php echo (is_array($User) ? $User['name'] : ""); ?>" />    
    <br/>Password<br/><input type="password" name="password" id="password" class="required" value="<?php echo (is_array($User) ? $User['password'] : ""); ?>"/>    
    <br/>Confirm password<br/><input type="password" id="password_confirm" class="required" value="<?php echo (is_array($User) ? $User['password'] : ""); ?>"/>    
    <br/>Status<br/> 
    <select name="status" class="required">
        <option value="">---</option>
        <option value="3" <?php echo (is_array($User)&&$User['status']=="3" ?  "SELECTED='SELECTED'" : ""); ?>>Administrator</option>
        <option value="2" <?php echo (is_array($User)&&$User['status']=="2" ?  "SELECTED='SELECTED'" : ""); ?>>Moderator</option>
        <option value="1" <?php echo (is_array($User)&&$User['status']=="1" ?  "SELECTED='SELECTED'" : ""); ?>>Viewer</option>
        <option value="0" <?php echo (is_array($User)&&$User['status']=="0" ?  "SELECTED='SELECTED'" : ""); ?>>Disabled</option>
    </select>
    
    <h3>Permissions</h3>
    
    <?php
    $permissions[] = array (
        'Emails',
        '<input type="radio" name="emails" class="required" value="2" ' . (is_array($User)&&$User['permission_emails']=="2" ?  "checked=\"checked\"" : "") . '>Edit',
        '<input type="radio" name="emails" class="required" value="1" ' . (is_array($User)&&$User['permission_emails']=="1" ?  "checked=\"checked\"" : "") . '>View',
        '<input type="radio" name="emails" class="required" value="0" ' . (is_array($User)&&$User['permission_emails']=="0" ?  "checked=\"checked\"" : ""). '>None'
        );
        
    $permissions[] = array (
        'Social Media',
        '<input type="radio" name="socialmedia" class="required" value="2" ' . (is_array($User)&&$User['permission_social_medias']=="2" ?  "checked=\"checked\"" : "") . '>Edit',
        '<input type="radio" name="socialmedia" class="required" value="1" ' . (is_array($User)&&$User['permission_social_medias']=="1" ?  "checked=\"checked\"" : "") . '>View',
        '<input type="radio" name="socialmedia" class="required" value="0" ' . (is_array($User)&&$User['permission_social_medias']=="0" ?  "checked=\"checked\"" : ""). '>None'
        );
    
    $permissions[] = array (
        'Users',
        '<input type="radio" name="users" class="required" value="2" ' . (is_array($User)&&$User['permission_users']=="2" ?  "checked=\"checked\"" : "") . '>Edit',
        '<input type="radio" name="users" class="required" value="1" ' . (is_array($User)&&$User['permission_users']=="1" ?  "checked=\"checked\"" : "") . '>View',
        '<input type="radio" name="users" class="required" value="0" ' . (is_array($User)&&$User['permission_users']=="0" ?  "checked=\"checked\"" : ""). '>None'
        );

    $permissions[] = array (
        'Reports',
        '<input type="radio" name="reports" class="required" value="2" ' . (is_array($User)&&$User['permission_reports']=="2" ?  "checked=\"checked\"" : "") . '>Edit',
        '<input type="radio" name="reports" class="required" value="1" ' . (is_array($User)&&$User['permission_reports']=="1" ?  "checked=\"checked\"" : "") . '>View',
        '<input type="radio" name="reports" class="required" value="0" ' . (is_array($User)&&$User['permission_reports']=="0" ?  "checked=\"checked\"" : ""). '>None'
        );
    
    $permissions[] = array (
        'Campaigns',
        '<input type="radio" name="campaigns" class="required" value="2" ' . (is_array($User)&&$User['permission_campaigns']=="2" ?  "checked=\"checked\"" : "") . '>Edit',
        '<input type="radio" name="campaigns" class="required" value="1" ' . (is_array($User)&&$User['permission_campaigns']=="1" ?  "checked=\"checked\"" : "") . '>View',
        '<input type="radio" name="campaigns" class="required" value="0" ' . (is_array($User)&&$User['permission_campaigns']=="0" ?  "checked=\"checked\"" : ""). '>None'
        );
    
    $permissions[] = array (
        'SMSs',
        '<input type="radio" name="sms" class="required" value="2" ' . (is_array($User)&&$User['permission_sms']=="2" ?  "checked=\"checked\"" : "") . '>Edit',
        '<input type="radio" name="sms" class="required" value="1" ' . (is_array($User)&&$User['permission_sms']=="1" ?  "checked=\"checked\"" : "") . '>View',
        '<input type="radio" name="sms" class="required" value="0" ' . (is_array($User)&&$User['permission_sms']=="0" ?  "checked=\"checked\"" : ""). '>None'
        );
    
    $permissions[] = array (
        'Recipients',
        '<input type="radio" name="recipients" class="required" value="2" ' . (is_array($User)&&$User['permission_recipients']=="2" ?  "checked=\"checked\"" : "") . '>Edit',
        '<input type="radio" name="recipients" class="required" value="1" ' . (is_array($User)&&$User['permission_recipients']=="1" ?  "checked=\"checked\"" : "") . '>View',
        '<input type="radio" name="recipients" class="required" value="0" ' . (is_array($User)&&$User['permission_recipients']=="0" ?  "checked=\"checked\"" : ""). '>None'
        );
    
    echo TableStart();
    echo TableHeaderRow(array("Section", "","",""));
    
    foreach ($permissions as $permission) {
        
        echo TableRow($permission);
    }
    
    echo TableEnd();
    
    ?>
    <input type="hidden" name="user_id" value="<?php echo (is_array($User) ? $uid : ""); ?>" />
    <input type="hidden" name="a" value="5" />
    <input type="hidden" name="r" value="user_list_view.php" />
    <p><input type="submit" value="Save" /></p>
</form>

<h3>Activity log</h3>
<?php
//--- Show the last 5 entries for the user in the log file
$UserHistory = RetrieveUserHistory($uid, "LIMIT 0,5");

if (is_array($UserHistory)) {
    
    echo TableStart();
    $Headers = array ("Source IP address", "Description", "Time", );
    echo TableHeaderRow($Headers);
    
    foreach ($UserHistory as $History) {
        
        $Data = explode("|", $History['description']);
        
        $Description = $Data[0];
        $Parameters = $Data[1];
        $IP = substr($Data[2],4, strlen($Data[2]));
        
        $Row = array (
            $IP, $Description, $History['date']
        );
        
        echo TableRow($Row);
    }
    
    echo TableEnd();
    
} else {
    
    echo "<p>No activity logged for this user as yet</p>";
    
}
?>
<p><a href="user_history_list_view.php?uid=<?php echo $uid; ?>">View full activity</a></p>

<?php
include_once ("footer.php");
?>
