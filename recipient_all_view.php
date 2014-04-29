<?php

$PageTitle = "White listed recipients";
$PageBackground = "images/icons/Contactlist.png";

include_once ("modules/DB.php");
include_once ("header.php");
include_once ("modules/UI.php");

extract ($_GET);

//--- Permission check
if ($Permissions[$_SERVER['PHP_SELF']] > $_SESSION['emailer']['permission_recipients']) {
    echo "<span>You do not have permission to access this page</span>";
    include_once ("footer.php");
    die;
} 

?>
<div class="pagination">
    <?php
    if (!isset($page)) {
        $page = 1;// default to page 1
    } elseif($page<1) {
        $page=1;  	 
    }

    $limit = "LIMIT " . (($page-1) * UI_ITEMS_PER_PAGE) . "," . UI_ITEMS_PER_PAGE;

    $Total = RetrieveRecipientsAll(true);

    $Total = $Total[0]['emails'];

    echo "<p>Total Number of Recipients:$Total</p>";

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

<input type="button" value="Print" onclick="" />

<?php
$Recipients = RetrieveRecipientsAll();

if (is_array($Recipients)) {
    
    echo TableStart();
    
    echo TableHeaderRow(array("Email","Cellphone" ,""));
    
    foreach ($Recipients as $Recipient) {
        
        //--- Check if the recipient has been blacklisted
        $data=RetrieveUnsubscribe($Recipient['email']);
        if(is_array($data)) continue;

        echo TableRow(array(stripslashes($Recipient["email"]), $Recipient['msisdn'],"<a href='actions.php?a=6&e=" . urlencode($Recipient['email']). "'>Opt-out</a>"));
        
    }
    
    echo TableEnd();
    
} else {
    
    echo "<p>The are no recipients loaded as yet.</p>";
}
?>
