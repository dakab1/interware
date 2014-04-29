<?php
include_once 'modules/DB.php';

$PageTitle = "Upload Email recipient list";
$PageBackground = "images/icons/Contactlist.png";
include_once ("header.php"); //starts session and displays all top contents
extract ($_GET);

//--- Permission check
if ($Permissions[$_SERVER['PHP_SELF']] > $_SESSION['emailer']['permission_emails']) {
    echo "<span>You do not have permission to access this page</span>";
    include_once ("footer.php");
    die;
} 

?>
<form id="uploadForm" action="actions.php" enctype="multipart/form-data" method="post">
<input type="hidden" name="eid" value="<?php echo $eid; ?>" />
<br/>CSV File <input type="file" name="list" class="required"/>
<input type="hidden" name="a" value="3">
<input type="hidden" name="cid" value="<?php echo ($cid != "" ? $cid : $_SESSION['emailer']['cid']); ?>" />
<input type="hidden" name="r" value="<?php echo (isset($r) ? urldecode($r) : "email_schedule_editor.php"); ?>" />
<br/><input type="submit" value="upload" />
</form>

<script>
$(document).ready(function(){
    $("#uploadForm").validate();
});
</script>

<?php
include_once ("footer.php");
?>