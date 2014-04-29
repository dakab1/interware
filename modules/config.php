<?php
ini_set("display_errors","no");
global $FacebookConfig;

//--- Site settings
define ("THISURL","");
define ("WEBMASTER", "");
define ("TITLE", "Interware");
define ("PATH", "");
define ("HOMEPWD", "");
define ("TEMPLATE_FOLDER","media/templates");

//--- SMS settings
define ("SMS_USER","");
define ("SMS_PASS","");
define ("SMS_CLIENT_ID","");
define ("SMS_API_URL","");
define ("SMS_API_ID", "");

//--- Twitter settings
define('CONSUMER_KEY', '');
define('CONSUMER_SECRET', '');
define('OAUTH_CALLBACK', THISURL . PATH . '/twitter-callback.php');

$FacebookConfig = array();
$FacebookConfig['appId'] = '';
$FacebookConfig['secret'] = '';
$FacebookConfig['fileUpload'] = false; // optional

//--- Email settings
define ("EMAIL_SEND_RATE","0"); //number of seconds to wait between email sends

//--- User permission definitions
define ("PERMISSION_NONE","0");
define ("PERMISSION_VIEW","1");
define ("PERMISSION_EDIT","2");


//--- Page Permissions
$Permissions = array (
    PATH ."/campaign_editor.php" => PERMISSION_EDIT,
    PATH ."/campaign_list_view.php" => PERMISSION_VIEW,
    PATH ."/email_editor.php" => PERMISSION_EDIT,
    PATH ."/email_list_view.php" => PERMISSION_VIEW,
    PATH ."/email_preview.php" => PERMISSION_VIEW,
    PATH ."/email_schedule_editor.php" => PERMISSION_EDIT,
    PATH ."/home.php" => PERMISSION_NONE,
    PATH ."/index.php" => PERMISSION_NONE,
    PATH ."/login.php" => PERMISSION_NONE,
    PATH ."/mfm.php" => PERMISSION_EDIT,
    PATH ."/queued_email_view.php" => PERMISSION_VIEW,
    PATH ."/queued_sms_view.php" => PERMISSION_VIEW,
    PATH ."/recipient_all_view.php" => PERMISSION_VIEW,
    PATH ."/recipient_list_view.php" => PERMISSION_VIEW,
    PATH ."/recipient_unsubscribed_view.php" => PERMISSION_VIEW,
    PATH ."/register-twitter.php" => PERMISSION_EDIT,
    PATH ."/sms_editor.php" => PERMISSION_EDIT,
    PATH ."/sms_list_view.php" => PERMISSION_VIEW,
    PATH ."/sms_schedule_editor.php" => PERMISSION_EDIT,
    PATH ."/sms_upload_csv.php" => PERMISSION_EDIT,
    PATH ."/sms_list_view.php" => PERMISSION_VIEW,
    PATH ."/sms_schedule_editor.php" => PERMISSION_EDIT,
    PATH ."/sms_upload_csv.php" => PERMISSION_EDIT,
    PATH ."/social_list_view.php" => PERMISSION_VIEW,
    PATH ."/social_posts.php" => PERMISSION_EDIT,
    PATH ."/twitter-callback.php" => PERMISSION_EDIT,
    PATH ."/twitter-editor.php" => PERMISSION_EDIT,
    PATH ."/upload_csv.php" => PERMISSION_EDIT,
    PATH ."/user_editor.php" => PERMISSION_NONE,
    PATH ."/user_history_list_view.php" => PERMISSION_NONE,
    PATH ."/user_list_view.php" => PERMISSION_NONE
);

//--- DB settings
define ("DBUSER", "");
define ("DBPASS", "");
define ("DBNAME","");
define ("DBHOST","");

//--- Database predefined values
define ("STATUS_CANCELLED","-1");
define ("STATUS_NOT_SENT","0");
define ("STATUS_QUEUED","1");
define ("STATUS_SENT","2");

//--- Admin settings
define("MAX_ADMIN_SESSIONS","3");

//--- Debugging options
define ("DEBUG_MODE",false); //if debug mode is set to true all SQL queries will be displayed
define ("DEBUG_MODE_ACTIONS", false); //debug action.php and disable output buffering
define ("DEBUG_MODE_CRONS", false); //debug action.php and disable output buffering

//--- UI options
define ("UI_ITEMS_PER_PAGE","20");

if (!mysql_connect(DBHOST, DBUSER, DBPASS)) {
    die("Unable to connect to the DB server. If the problem persists please contact the webmaster");
    define ("DB_CONNECTED", true);
}

if (!mysql_select_db(DBNAME)) {
    die("Unable to find database. If the problem persists please contact the webmaster");
}