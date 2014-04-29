<?php
$PageTitle = "User history for User #" . $_GET['uid'];

include_once("modules/DB.php");
include_once("modules/UI.php");
include_once("header.php");

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

    $Total = RetrieveUserHistory($uid,null,true); //get the total count of event entries

    $Total = $Total[0]['count(*)'];

    echo "<p>Total Number of logged event(s):$Total</p>";

    //--- Show pagination
    echo "\nPage : <select onchange=\"document.location.href='" . $_SERVER['SCRIPT_NAME'] . "?" . ($uid!=""?"uid=$uid&":""). "page=' + this.value\">";

    for($i=1;$i<=(($Total/UI_ITEMS_PER_PAGE)+1);$i++) {

        echo "\n\t<option " .($i == $page ? "selected=selected" : "") . " >$i</option>";

    }

    echo "\n</select>";
    ?>
</div>

<?php
$UserHistory = RetrieveUserHistory($uid, $limit);

if (is_array($UserHistory)) {
    
    echo TableStart();
    $Headers = array ("Source IP address", "Description", "Time", );
    echo TableHeaderRow($Headers);
    
    foreach ($UserHistory as $History) {
        
        $Data = explode("|", $History['description']);
        
        $Description = $Data[0];
        $Parameters = $Data[1];
        $IP = substr($Data[2],4, strlen($Data[2]));
        
        $Row = array (
            $IP, $Description, $History['date']
        );
        
        echo TableRow($Row);
    }
    
    echo TableEnd();
    
} else {
    
    echo "<p>No activity logged for this user as yet</p>";
    
}


?>
