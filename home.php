<?php
global $PageTitle;
$PageTitle = "";
$PageBackground = "images/icons/Home.png";
include_once ("modules/config.php");
include_once ("header.php");

//--- Permission check
/*
if ($Permissions[$_SERVER['PHP_SELF']] > $_SESSION['emailer']['permission_emails']) {
    echo "<span>You do not have permission to access this page</span>";
    include_once ("footer.php");
    die;
} 
 * 
 */
?>

<div class="notice_box">
    <h3>Notice board</h3>
    <?php
    //--- Get the current file contents
    if (!$handle = fopen (HOME_PAGE_NOTICES_FILE, "r")) {

        if (DEBUG_MODE) {

            DebugBox("File open error", "Failed to open " . HOME_PAGE_NOTICES_FILE);

        }

        UpdateLog(print_r($_POST,1), "Failed to open file " . HOME_PAGE_NOTICES_FILE);

    }
    
    @$content = fread($handle, filesize(HOME_PAGE_NOTICES_FILE));
    
    if (!$content) {
        
        if (DEBUG_MODE) {
            
            DebugBox("File read error", "Failed to read the contents of the file " .HOME_PAGE_NOTICES_FILE . " " . print_r($_POST,1));
            
        }
        
        UpdateLog("manage_home.php", "Failed to read the contents of the file " . HOME_PAGE_NOTICES_FILE . " " . print_r($_POST,1));
    }

    fclose($handle);
    
    echo stripcslashes($content);
    ?>
</div>


<div class="notice_box">
    <h3>News</h3>
    <?php
    //--- Display news from rss feed
    DisplayRSS(HOME_PAGE_NEWS_FEED_URL);
    ?>
</div>

<div class="notice_box">
    <h3>System notices</h3>
    All systems are running okay.    
</div>

<div class="notice_box">
    <h3>Statistics</h3>

</div>

<?php
include_once ("footer.php");
?>