<?php
include_once ("modules/DB.php");
include_once ("modules/utils.php");

session_start();
?>
<html>
    <head>
        <title>SMS preview</title>
    </head>
    <body>
        
    <input type="button" onclick="document.location.href='sms_list_view.php'" value="<< Back"/>
    <input type="button" onclick="window.print()" value="Print" />
    
<?php
    //--- Permission check
    if ($Permissions[$_SERVER['PHP_SELF']] > $_SESSION['emailer']['permission_emails']) {
        echo "<span>You do not have permission to access this page</span>";
        include_once ("footer.php");
        die;
    } 
    ?>
    <div style="padding:4px; border:1px solid #ABADB3;font-size:10px; font-family: 'Lucida Sans Unicode', 'Lucida Grande', Sans-Serif;">
        <form action="actions.php" method="get">
            Send sample to <input type="text" name="msisdn" />
            <input type="submit" value="Send" />
            <br/>Field 1 <input type="text" name="custom[field1]" value="Custom field 1" />
            <br/>Field 2 <input type="text" name="custom[field2]" value="Custom field 2" />
            <br/>Field 3 <input type="text" name="custom[field3]" value="Custom field 3" />
            <br/>Field 4 <input type="text" name="custom[field4]" value="Custom field 4" />
            <input type="hidden" value="<?php echo $_GET['sms_id']; ?>" name="sms_id"/>
            <input type="hidden" value="19" name="a"/>
        </form>
    </div>
    <?php
    //--- Check if a mail_id has been passed and use that instead
    if (isset($_GET['sms_id'])) {
        
        $SMS = RetrieveSMS($_GET['sms_id']);
        $SMS = $SMS[0];
        
        $message = stripslashes($SMS['text']);

    } else {

        $message = $_POST['text'];

    }

    //--- Display the message contents
    echo "\n<p><hr noshade=\"noshade\" style=\"border:1px solid #ABADB3;\" /></p>";
    echo "<span style=\"font-size:10px; font-family: 'Lucida Sans Unicode', 'Lucida Grande', Sans-Serif;\">" . stripslashes($message) . "</span>";
    echo "\n<p><hr noshade=\"noshade\" style=\"border:1px solid #ABADB3;\" /></p>";
    ?>
    <input type="button" onclick="document.location.href='<?php echo $_SERVER['HTTP_REFERER']; ?>'" value="<< Back"/>
    <input type="button" onclick="window.print()" value="Print" />
    
    </body>
</html>