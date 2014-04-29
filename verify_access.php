<?php
include_once ("modules/DB.php");
include_once ("modules/utils.php");

$ValidatedConcurrent = true;
$ValidatedSession = true;

session_start();

//--- Encrypted session + ip + time down to minute
$encrypted = sha1(($_GET['s'] ? $_GET['s'] : session_id()) . $_SERVER['REMOTE_ADDR']);

//--- Get total number of logged in sessions
$SessionCount = RetrieveActiveSessions();

if ($SessionCount > MAX_ADMIN_SESSIONS) {
    
    $Error = "More than " . MAX_ADMIN_SESSIONS ." concurrent users logged in. Please try again later."; 
    $ValidatedConcurrent = false;
}

if (!RetrieveSession($encrypted, date("Y-m-d H:i:s"))) {
    
    $Error = "Your session is invalid. Please log in again.";
    $ValidatedSession = false;
}

if ((!$ValidatedConcurrent) || (!$ValidatedSession)) {
    
    SaveEvent($_SESSION['emailer']['id'], "Invalid session when trying to access " . $_SERVER['PHP_SELF'] . "| parameters:" . $_SERVER['QUERY_STRING'] . "| ip:" . $_SERVER['REMOTE_ADDR'], date("Y-m-d H:i:s"));

    //--- Force logout and redirect to login page
    ?>
    <html>
        <head></head>
        <body>
            <script type='text/javascript'>
                parent.location.href='login.php?logout=1&Error=<?php echo urlencode($Error); ?>';
            </script>
        </body>
    </html>
    <?php
    
}

/* Sample permissions check
//--- Check if the user is suppose to have access to this page based on admin permissions
if ($Permissions[$_SERVER['PHP_SELF']] > $_SESSION['emailer']['permission_campaigns']) {
    echo "<span>You do not have permission to access this page</span>";
    include_once ("footer.php");
    die;
} 
 * 
 */

if (DEBUG_MODE) {

    echo "<p>Your permission -> " . $_SESSION['emailer']['permission_campaigns'] . "</p>";
    echo "<p>Page permission -> " . $Permissions[$_SERVER['PHP_SELF']] . "</p>";

}

?>
