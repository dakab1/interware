<?php

$PageTitle = "Campaigns List";
$PageBackground = "images/icons/Calendar.png";

include_once("modules/DB.php");
include_once("modules/UI.php");
include_once("header.php");

extract ($_GET);

//--- Permission check
if ($Permissions[$_SERVER['PHP_SELF']] > $_SESSION['emailer']['permission_campaigns']) {
    echo "<span>You do not have permission to access this page</span>";
    include_once ("footer.php");
    die;
} 

?>
<!--
<div class="searchBox">
    <input type="text" name="q" />
    <input type="submit" target="" value="Search" />
</div>
-->
<?php

if (!isset($page)) {
    $page = 1;// default to page 1
} elseif($page<1) {
    $page=1;  	 
}

$limit = "LIMIT " . (($page-1) * UI_ITEMS_PER_PAGE) . "," . UI_ITEMS_PER_PAGE;

$Total = RetrieveCampaignsCount();

$Total = $Total[0]['campaigns'];

echo "<p>Total Number of Campaigns:$Total</p>";

//--- Show pagination
echo "\nPage : <select onchange=\"document.location.href='" . $_SERVER['SCRIPT_NAME'] . "?s=$s&" . ($cid!=""?"cid=$cid&":""). "page=' + this.value\">";

for($i=1;$i<=(($Total/UI_ITEMS_PER_PAGE)+1);$i++) {

    echo "\n\t<option " .($i == $page ? "selected=selected" : "") . " >$i</option>";
    
}

echo "\n</select>";

$Campaigns = RetrieveCampaigns($limit);

//--- Display queued campaigns
echo TableStart();
echo TableHeaderRow(array("Campaign ID", "Campaign Name", "Start Date"/*, "End Date", "Status",""*/));

if (is_array($Campaigns)) {
    
    foreach($Campaigns as $Campaign) {

        echo TableRow(array($Campaign['id'], "<a href='campaign_editor.php?s=$s&cid=" . $Campaign['id'] . "'>" . stripcslashes($Campaign['name']) . "</a>", $Campaign['start_date']/*, $Campaign['end_date'], $Campaign['status'], $Campaign['user_id']*/));

    }
    
} else {
    
    echo  "<tr><td colspan='3'>No campaigns have been created yet.</td></tr>";
}

echo TableEnd();
?>
