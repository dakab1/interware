<?php
/*
 * Author   :  Dean Kabasa
 * Email    :  me@deankabasa.com
 * Date     :  18 May 2014    
 */

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case "install":
            
            //Connect to database using passed credentials
            
            //Create database structure
            
            //update modules/config.php
            
            //Test email connection
            
            //If all okay redirect to login page
            
            //If not okay, go back to installation script with errors
            
            break;
        case "update":
            break;
    }
}
?>
<html>
    <head>
        <title>Interware installation script.</title>
        <link type="text/css" href="css/smoothness/jquery-ui-1.7.3.custom.css" rel="stylesheet" />	
        <link type="text/css" href="css/emailer_general.css" rel="stylesheet" />	
        <script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.7.3.custom.min.js"></script>
        <script type="text/javascript" src="js/jquery.validate.js"></script>
    </head>
    <body>
        <h1>Interware installation script</h1>
        <?= $message ?>
        <div>
            <form class="install_form" id="installForm">
                <h3>Domain setup</h3>
                <?= (!empty($domain_error) ? $domain_error : ""); ?>
                <span><label>Domain name</label><input class="required" type="text" name="" value="" /></span><br/>
                <span><label>Path</label><input class="required" type="text" name="" value="" /></span><br/>
                <h3>Email setup</h3>
                <?= (!empty($email_error) ? $email_error : ""); ?>
                <span><label>Server name</label><input class="required" type="text" name="" value="" /></span><br/>
                <span><label>Port</label><input class="required" type="text" name="" value="" /></span><br/>
                <span><label>Server type</label>
                    <select class="required" name="">
                        <option>---</option>
                    </select>
                </span><br/>
                <span><label>Requires authentication</label><input type="checkbox" name="" value="" /></span><br/>
                <span><label>Username</label><input class="required" type="text" name="" value="" /></span><br/>
                <span><label>Password</label><input class="required" type="text" name="" value="" /></span><br/>
                <h3>Database setup</h3>
                <?= (!empty($database_error) ? $database_error : ""); ?>
                <span><label>Host name</label><input class="required" type="text" name="" value="" /></span><br/>
                <span><label>Username</label><input class="required" type="text" name="" value="" /></span><br/>
                <span><label>Password</label><input class="required" type="text" name="" value="" /></span><br/>
                <span><label>Database name</label><input class="required" type="text" name="" value="" /></span><br/>
                <h3>Webmaster setup</h3>
                <?= (!empty($webmaster_error) ? $webmaster_error : ""); ?>
                <span><label>Userame</label><input class="required" type="text" name="" value="" /></span><br/>
                <span><label>Password</label><input class="required" type="text" name="" value="" /></span><br/>
                <span><label>Webmaster email address</label><input class="required" type="text" name="" value="" /></span><br/>
                <input type="submit" value="Install" name="action"/>
            </form>
        </div>
        <script>
            $(document).ready(function() {
                $("#installForm").validate();
            });
        </script>
    </body>
</html>
