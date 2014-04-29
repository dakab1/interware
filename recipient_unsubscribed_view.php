<?php
$PageTitle = "Recipients Unsubscribed List";
$PageBackground = "images/icons/Contactlist.png";

include_once("modules/DB.php");
include_once("modules/UI.php");
include_once("header.php");

//--- Permission check
if ($Permissions[$_SERVER['PHP_SELF']] > $_SESSION['emailer']['permission_recipients']) {
    echo "<span>You do not have permission to access this page</span>";
    include_once ("footer.php");
    die;
} 

extract ($_GET);

?>
<div class="pagination">
    <?php
    
    if (!isset($page)) {
        $page = 1;// default to page 1
    } elseif($page<1) {
        $page=1;  	 
    }

    $limit = "LIMIT " . (($page-1) * UI_ITEMS_PER_PAGE) . "," . UI_ITEMS_PER_PAGE;

    $Total = RetrieveUnsubscribeCount();
    $Total = $Total[0]['Unsubscibed'];

    echo "<p>Total Number of Unsubscibed Recipients:$Total</p>";

    //--- Show pagination
    echo "\nPage : <select onchange=\"document.location.href='" . $_SERVER['SCRIPT_NAME'] . "?" . ($eid!=""?"eid=$eid&":""). "page=' + this.value\">";

    for($i=1;$i<=(($Total/UI_ITEMS_PER_PAGE)+1);$i++) {

        echo "\n\t<option " .($i == $page ? "selected=selected" : "") . " >$i</option>";

    }

    echo "\n</select>";
    ?>
</div>

<!--
<div class="searchBox">
    <input type="text" name="q" />
    <input type="submit" target="" value="Search" />
</div>
-->
<?php

$Recipients = RetrieveUnsubscribe(($e!=""?$e:""));

if (is_array($Recipients)) {
    
    //--- Display queued campaigns
    echo TableStart();
    echo TableHeaderRow(array("Email Address", "Action"));

    foreach($Recipients as $Reciepient) {

        echo TableRow(array(stripcslashes($Reciepient['email_address']), "<a href='actions.php?a=7&e=" . $Reciepient['email_address'] . "'>whitelist</a>" ));

    }
    
    echo TableEnd();
    
} else {
    
    echo "<p>No recipients found.</p>";
}

include_once ("footer.php");
?>
