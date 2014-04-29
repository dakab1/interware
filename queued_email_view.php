<?php

$PageTitle = "Queued Email Campaigns";
$PageBackground = "images/icons/Queue.png";

include_once ("modules/config.php");
include_once ("modules/DB.php");
include_once ("header.php");

extract($_GET);

//--- Permission check
if ($Permissions[$_SERVER['PHP_SELF']] > $_SESSION['emailer']['permission_emails']) {
    echo "<span>You do not have permission to access this page</span>";
    include_once ("footer.php");
    die;
} 

echo "\n<p><input type='button' value='Refresh' onclick=\"document.location.href='queued_email_view.php'\" /></p>";

if (!isset($page)) {
    $page = 1;// default to page 1
} elseif($page<1) {
    $page=1;  	 
}

$limit = "LIMIT " . (($page-1) * UI_ITEMS_PER_PAGE) . "," . UI_ITEMS_PER_PAGE;

$Total = RetrieveEmailsCount(($cid ? $cid : false));

$Total = $Total[0]['emails'];

echo "<p>Total Number of Emails:$Total</p>";

//--- Show pagination
echo "\nPage : <select onchange=\"document.location.href='" . $_SERVER['SCRIPT_NAME'] . "?" . ($cid!=""?"cid=$cid&":""). "page=' + this.value\">";

for($i=1;$i<=(($Total/UI_ITEMS_PER_PAGE)+1);$i++) {

    echo "\n\t<option " .($i == $page ? "selected=selected" : "") . " >$i</option>";
    
}

echo "\n</select>";

$Campaigns = RetrieveQueuedMailCampaigns();

//--- Display queued campaigns
echo "\n<table class='table'>";
echo "<tr class='table-header'><td>Campaign Name</td><td>Email Subject</td><td>Send Date and Time</td><td>Status</td><td>Count</td><td></td></tr>";//headers

if (is_array($Campaigns)) {
    
    foreach ($Campaigns as $Campaign) {

        echo "\n<tr class='table-row' onmouseover=\"this.className='table-row-hover'\" onmouseout=\"this.className='table-row'\">";
        echo "\n\t<td>" . $Campaign['name'] . "</td>";
        echo "\n\t<td>" . $Campaign['subject'] . "</td>";
        echo "\n\t<td>" . $Campaign['send_start_date'] . "</td>";
        echo "\n\t<td>";  

        switch($Campaign['status']) {
            case STATUS_CANCELLED:
                echo "Cancelled";
            break;

            case STATUS_NOT_SENT:
                echo "Not yet sent";
            break;

            case STATUS_QUEUED:
                echo "Queued";
            break;

            case STATUS_SENT:
                echo "Sent";
            break;
            default:
                echo "&nbsp;";
            break;
        }

        echo "</td>";
        echo "<td>" . ($Campaign['status'] == STATUS_QUEUED ? "\n\t<input type='button' value='Stop' onclick=\"document.location.href='actions.php?status=" . STATUS_CANCELLED. "&r=" . urlencode($_SERVER['REQUEST_URI']) . "&a=17&mail_id=" . $Campaign['mail_id'] . "'\" />" : "&nbsp;" ) . "</td>";
        echo "\n\t<td style='text-align:left'>" . ($Campaign['status_count'] !="" ? $Campaign['status_count'] : "&nbsp;") . "</td>";
        echo "\n</tr>";

        echo "<tr class='table-seperator'><td colspan='5'></td></tr>";

    }
} else {
    
    echo "<tr><td colspan='6'>No campaigns queued yet.</td></tr>";
}
echo "\n</table>";

include_once ("footer.php");
?>