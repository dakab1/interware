<?php
$PageTitle = "SMS editor";
$PageBackground = "images/icons/SendSMS.png";

include_once ("modules/DB.php");
include_once ("header.php");

extract ($_GET);

//--- Permission check
if ($Permissions[$_SERVER['PHP_SELF']] > $_SESSION['emailer']['permission_sms']) {
    echo "<span>You do not have permission to access this page</span>";
    include_once ("footer.php");
    die;
} 

//--- Get the contents of an sms if the sms_id is passed
if ($sms_id) {
    
    $SMS = RetrieveSMS($sms_id);
    
    if (is_array($SMS)) {
        
        $cid = $SMS[0]['campaign_id'];
        
    }
    
}

?>
Enter SMS contents(MAX 160 characters)
<form method="POST" action="actions.php">
<p>
    <textarea name="text"><?php echo ($SMS[0]['text'] != "" ? $SMS[0]['text'] : "") ?></textarea>
</p>

<input type="hidden" name="a" value="11"/>
<input type="hidden" name="sms_id" value="<?php echo (isset($sms_id) ? $sms_id : ""); ?>" />
<input type="hidden" name="cid" value="<?php echo (isset($cid) ? $cid : ""); ?>" />
<input type="hidden" name="r" value="<?php echo (isset($r) ? $r : "sms_upload_csv.php"); ?>" />
<br/><input type="button" value="<<Back" onclick="document.location.href='<?php echo $_SERVER['HTTP_REFERER']; ?>'" /><input type="submit" value="Save" />

</form>
<?php
include_once ("footer.php");
?>
