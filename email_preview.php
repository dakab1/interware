<?php
include_once ("modules/DB.php");
include_once ("modules/utils.php");

session_start();
?>
<html>
    <head>
        <title>Email preview</title>
    </head>
    <body>
        
    <input type="button" onclick="document.location.href='email_list_view.php'" value="<< Back"/>
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
            Send sample to <input type="text" name="email_address" />
            <input type="submit" value="Send" />
            <br/>Field 1 <input type="text" name="custom[field1]" value="Custom field 1" />
            <br/>Field 2 <input type="text" name="custom[field2]" value="Custom field 2" />
            <br/>Field 3 <input type="text" name="custom[field3]" value="Custom field 3" />
            <br/>Field 4 <input type="text" name="custom[field4]" value="Custom field 4" />
            <input type="hidden" value="<?php echo $_GET['eid']; ?>" name="eid"/>
            <input type="hidden" value="18" name="a"/>
        </form>
    </div>
    <?php
    //--- Check if a mail_id has been passed and use that instead
    if (isset($_GET['eid'])) {

        $Email = RetrieveMail($_GET['eid']);
        $Email = $Email[0];

        $from = stripslashes($Email['from_address']);
        $bcc = stripslashes($Email['bcc_address']);
        $subject = stripslashes($Email['subject']);
        $message = stripslashes($Email['message']);

    } else {

        $from = $_POST['from_address'];
        $bcc = $_POST['bcc_address'];
        $subject = $_POST['subject'];
        $message = $_POST['message'];

    }

    //--- Display the message contents
    echo "\n<p style='font-size:10px; font-family: \"Lucida Sans Unicode\", \"Lucida Grande\", Sans-Serif;'><b>From:</b>$from</p>";
    echo "\n<p style='font-size:10px; font-family: \"Lucida Sans Unicode\", \"Lucida Grande\", Sans-Serif;'><b>BCC:</b>$bcc</p>";
    echo "\n<p style='font-size:10px; font-family: \"Lucida Sans Unicode\", \"Lucida Grande\", Sans-Serif;'><b>Subject:</b>$subject</p>";
    
    $Attachments = RetrieveMailAttachments($_GET['eid']);

    if (is_array($Attachments)) {
        echo "\n<span style='font-size:10px; font-family: \"Lucida Sans Unicode\", \"Lucida Grande\", Sans-Serif;'>Attachments</span><br/>";
        echo "<ul>";
        foreach ($Attachments as $Attachment) {
            echo "<li><a style='font-size:10px; font-family: \"Lucida Sans Unicode\", \"Lucida Grande\", Sans-Serif;' href='attachment_view.php?id=" . $Attachment['id']."'>" . $Attachment['filename'] . "</a></li>";

        }
        echo "</ul>";
    } else {

        echo "<p>There are no email attachments.</p>";
    }
    
    echo "\n<p><hr noshade=\"noshade\" style=\"border:1px solid #ABADB3;\" /></p>";
    echo stripslashes($message);
    echo "\n<p><hr noshade=\"noshade\" style=\"border:1px solid #ABADB3;\" /></p>";
    ?>
    <input type="button" onclick="document.location.href='<?php echo $_SERVER['HTTP_REFERER']; ?>'" value="<< Back"/>
    <input type="button" onclick="window.print()" value="Print" />
    
    </body>
</html>