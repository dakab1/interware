<?php
$PageTitle = "Link Facebook Account";

include_once ("modules/config.php");
include_once ("facebookoauth/facebook.php");
include_once ("verify_access.php");
include_once ("header.php");

global $FacebookConfig;

session_start();

$facebook = new Facebook($FacebookConfig);

//$params = array('scope' => 'publish_stream,read_insights,read_friendlists,read_stream,manage_pages','redirect_uri' => THISURL . PATH . "/index.php?r=facebook-callback.php?" );
$params = array('scope' => 'publish_stream,read_insights,read_friendlists,read_stream,manage_pages','redirect_uri' => THISURL . PATH . "/facebook-callback.php?" );

$URL = $facebook->getLoginUrl($params); // $params is optional. 

echo "<a target='_blank' href='" . $URL . "'>Login to facebook</a>";

include_once ("footer.php");
?>
