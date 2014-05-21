<?php
/*
 * Author   :  Dean Kabasa
 * Email    :  me@deankabasa.com
 * Date     :  18 May 2014    
 */

ini_set("display_errors", "no");

@include_once("modules/config.php");

if (isset($_POST['action'])) {

    //--- Used to flag any errors during installation
    $error = false;

    switch ($_POST['action']) {

        case "Install":

            extract($_POST);

            //--- Websites root folder on the server
            $web_root = $domain_setup_document_root . $domain_setup_path;

            //--- Check web root exists and is correct
            if (!file_exists($web_root)) {
                $error = true;
                $domain_error = "<span style='color:red'>Specified domain root and path ($web_root) combination does not seem to exist.</span>";
            }

            //--- Validate database connection and create database structure
            if (!$error) {

                //--- Connect to database using passed credentials
                $db = new mysqli($_POST['database_setup_host_name'], $_POST['database_setup_username'], $_POST['database_setup_password'], $_POST['database_setup_database_name']);

                if (mysqli_connect_error()) {

                    $database_error = "<span style='color:red'>Database error \"" . mysqli_connect_error() . "\"</span>";
                    $error = true;
                } else {

                    $database_error = "<span style='color:green'>Database connected successfully</span>";

                    //--- Create database structure
                    $install_sql = file_get_contents("interware_default.sql");

                    if (!$db->multi_query($install_sql)) {

                        $database_error = "<span style='color:red'>Failed to create database structure. MySQL returned \"" / $db->error() . "\"<span style='color:red'>";

                        $error = true;
                    } else {

                        //--- Create webmaster user
                        $username = addslashes($webmaster_setup_username);

                        $password = addslashes($webmaster_setup_password);

                        //Very weird thing you have to do so that mysqli doesn't throw "mysqli Commands out of sync; you can't run this command now"
                        while ($db->more_results()) {
                            $db->next_result();
                            if ($res = $db->store_result()) {
                                $res->free();
                            }
                        }
                        
                        $db->multi_query("INSERT INTO `user` (
                                            `id` ,
                                            `name` ,
                                            `password` ,
                                            `status` ,
                                            `permission_emails` ,
                                            `permission_social_medias` ,
                                            `permission_reports` ,
                                            `permission_users` ,
                                            `permission_campaigns` ,
                                            `permission_sms` ,
                                            `permission_recipients`
                                            )
                                            VALUES (
                                            NULL ,  '$username',  '$password',  '2',  '2',  '2',  '2',  '2',  '2',  '2',  '2'
                                            );
                                            INSERT INTO `campaign` (
                                            `id` ,
                                            `name` ,
                                            `start_date` ,
                                            `user_id` ,
                                            `status` ,
                                            `end_date` ,
                                            `created_date`
                                            )
                                            VALUES (
                                            NULL ,  'Default Campaign',  '" . date("Y-m-d H:i:s") . "',  '1',  '1',  '2114-05-21 00:00:00',NOW() 
                                            );");
                        if ($db->error) {
                            $error = true;
                            $message = "<span style='color:red'>Failed to create user with mysql error \"" . $db->error . "\"</span>";
                        }
                    }
                }
            }

            //--- Write new config.php file to the server
            if (!$error) {
                //--- update modules/config.php
                $config_template = file_get_contents("modules/config_template.php");

                //--- Insert new parameters
                $config_template = str_replace("[<THISURL>]", $domain_setup_domain_name, $config_template);
                $config_template = str_replace("[<PATH>]", $domain_setup_path, $config_template);
                $config_template = str_replace("[<WEBMASTER>]", $webmaster_setup_email, $config_template);
                $config_template = str_replace("[<HOMEPWD>]", $domain_setup_document_root, $config_template);
                $config_template = str_replace("[<DBUSER>]", $database_setup_username, $config_template);
                $config_template = str_replace("[<DBPASS>]", $database_setup_password, $config_template);
                $config_template = str_replace("[<DBNAME>]", $database_setup_database_name, $config_template);
                $config_template = str_replace("[<DBHOST>]", $database_setup_host_name, $config_template);

                //--- Backup old config file
                if (file_exists($web_root . "/modules/config.php")) {

                    if (!rename($web_root . "/modules/config.php", "modules/config_" . date("YmdHis") . ".php")) {
                        $message = "<span style='color:red'>Failed to backup current $web_root/modules/config.php</span>";
                        $error = true;
                    }
                }

                if ($fh = fopen($web_root . "/modules/config.php", "w")) {

                    if (!fwrite($fh, $config_template)) {
                        $message = "<span style='color:red'>Failed to write to $web_root/modules/config.php. Copy the contents of the following text area and manually create the config file via ftp <br/><textarea>$config_template</textarea></span>";
                        $error = true;
                    }

                    //--- Close the config file
                    fclose($fh);
                } else {
                    $message = "<span style='color:red'>Failed to create $web_root/modules/config.php. Copy the contents of the following text area and manually create the config file via ftp <br/><textarea>$config_template</textarea></span>";
                    $error = true;
                }
            }


            //--- Add cronjobs Linux/OSX only
            //TODO: Check OS and also a checkbox to allow user to manually set this
            if (!$error) {
                //--- Create SH file to execute cronjobs
                if ($fh3 = fopen($web_root . "/crons/crons.sh", "w")) {

                    //Change to cron directory
                    fwrite($fh3, "#!/bin/bash");
                    fwrite($fh3, "\ncd " . $web_root . "/crons");

                    $php_path = exec("which php"); //TODO: Validate response is proper php path

                    try {
                        fwrite($fh3, "\n" . $php_path . " " . $web_root . "/crons/send_mail.php");
                        fwrite($fh3, "\n" . $php_path . " " . $web_root . "/crons/send_sms.php");
                        fwrite($fh3, "\n" . $php_path . " " . $web_root . "/crons/send_social_post.php");
                        fwrite($fh3, "\n" . $php_path . " " . $web_root . "/crons/trigger_mail.php");
                        fwrite($fh3, "\n" . $php_path . " " . $web_root . "/crons/trigger_sms.php");
                    } catch (Exception $e) {
                        $message = "<span style='color:red'>Failed to write .sh file of scheduled scripts</span>";
                        $error = true;
                    }

                    fclose($fh3);

                    //--- Give 755 permission
                    if (!chmod($web_root . "/crons/crons.sh", "755"))
                        echo "Failed to change permissions";
                } else {
                    $message = "<span style='color:red'>Failed to create $web_root./crons/crons.sh file of scheduled scripts</span>";
                    $error = true;
                }


                if ($fh2 = fopen("/tmp/crontab.txt", "w")) {

                    //--- Get existing cronjobs
                    //$existing = exec("crontab -l");
                    
                    //--- Write new cronjobs
                    $new_crons = "\n* * * * * " . $web_root . "/crons/crons.sh 2>&1 >> " . $web_root . "/crons/cron.log\n";
                    if (!fwrite($fh2, $existing . $new_crons)) {

                        $message = "<span style='color:red'>Failed to write to cronjobs text file.  Try create them manually</span>";
                        $error = true;
                    } else {

                        //--- Add new cronjobs
                        echo exec("crontab /tmp/crontab.txt");
                    }

                    //--- Close file containing crons
                    fclose($fh2);
                } else {

                    $message = "<span style='color:red'>Failed to open cronjobs text file.  Try create them manually</span>";
                    $error = true;
                }
            }

            //--- If all okay redirect to login page
            if (!$error) {

                //--- Display installation confirmation
                die("<html><head><title>Interware installation</title><link type='text/css' href=\"css/emailer_general.css\" rel=\"stylesheet\" /></head><body><h1>Installation complete!</h1><p>Installation was successful. <a href='" . $domain_setup_domain_name . $domain_setup_path . "/login.php'>Click here</a> to login to interware.</p></body></html>");
            }

            break;
        case "update":
            break;
    }
}
?>
<html>
    <head>
        <title>Interware installation</title>
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
            <form class="install_form" id="installForm" method="POST">
                <h3>Domain setup</h3>
                <?= (!empty($domain_error) ? $domain_error : ""); ?>
                <span><label>Domain name</label><input class="required" type="text" name="domain_setup_domain_name" value="<?= ($domain_setup_domain_name ? $domain_setup_domain_name : "http://" . $_SERVER['HTTP_HOST']) ?>" />***</span><br/>
                <span><label>Path</label><input class="required" type="text" name="domain_setup_path" value="<?= ($domain_setup_path ? $domain_setup_path : str_replace("/install.php", "", $_SERVER['PHP_SELF'])) ?>" />***</span><br/>
                <span><label>Document root</label><input class="required" type="text" name="domain_setup_document_root" value="<?= ($domain_setup_document_root ? $domain_setup_document_root : $_SERVER['DOCUMENT_ROOT']) ?>" />***</span><br/>
                <h3>Database setup</h3>
                <?= (!empty($database_error) ? $database_error : ""); ?>
                <span><label>Host name</label><input class="required" type="text" name="database_setup_host_name" value="<?= ($database_setup_host_name ? $database_setup_host_name : "") ?>" />***</span><br/>
                <span><label>Username</label><input class="required" type="text" name="database_setup_username" value="<?= ($database_setup_username ? $database_setup_username : "") ?>" />***</span><br/>
                <span><label>Password</label><input class="" type="text" name="database_setup_password" value="<?= ($database_setup_password ? $database_setup_password : "") ?>" /></span><br/>
                <span><label>Database name</label><input class="required" type="text" name="database_setup_database_name" value="<?= ($database_setup_database_name ? $database_setup_database_name : "") ?>" />***</span><br/>
                <h3>Webmaster setup</h3>
                <?= (!empty($webmaster_error) ? $webmaster_error : ""); ?>
                <span><label>Userame</label><input class="required" type="text" name="webmaster_setup_username" value="<?= ($webmaster_setup_username ? $webmaster_setup_username : "") ?>" />***</span><br/>
                <span><label>Password</label><input class="required" type="text" name="webmaster_setup_password" value="<?= ($webmaster_setup_password ? $webmaster_setup_password : "") ?>" />***</span><br/>
                <span><label>Webmaster email address</label><input class="required" type="text" name="webmaster_setup_email" value="<?= ($webmaster_setup_email ? $webmaster_setup_email : "") ?>" />***</span><br/>
                <!-- Will implement once phpmailer has been integrated into the project-->
                <!---
                <h3>Email setup</h3>
                <?= (!empty($email_error) ? $email_error : ""); ?>
                <span><label>Server name</label><input class="" type="text" name="email_setup_server_name" value="<?= ($email_setup_server_name ? $email_setup_server_name : "") ?>" /></span><br/>
                <span><label>Port</label><input class="" type="text" name="email_setup_port" value="<?= ($email_setup_port ? $email_setup_port : "") ?>" /></span><br/>
                <span><label>Server type</label>
                    <select class="" name="email_setup_server_type">
                        <option>---</option>
                    </select>
                </span><br/>
                <span><label>Requires authentication</label><input type="checkbox" name="email_setup_requires_authentication" value="<?= ($email_setup_requires_authentication ? $email_setup_requires_authentication : "") ?>" /></span><br/>
                <span><label>Username</label><input class="" type="text" name="email_setup_username" value="<?= ($email_setup_username ? $email_setup_username : "") ?>" /></span><br/>
                <span><label>Password</label><input class="" type="text" name="email_setup_password" value="<?= ($email_setup_password ? $email_setup_password : "") ?>" /></span><br/>
                -->
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