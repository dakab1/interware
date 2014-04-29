<?php

$PageTitle = "View users";
$PageBackground = "images/icons/User.png";

include_once ("modules/DB.php");
include_once ("header.php");
include_once ("modules/UI.php");

extract ($_GET);

?>
<div>
    <input type="button" onclick="document.location.href='user_editor.php';" value="Add New" />    
</div>


<div class="pagination">
    <?php
    if (!isset($page)) {

        $page = 1;// default to page 1

    } elseif($page<1) {

        $page=1;  	 

    }

    $limit = " LIMIT " . (($page-1) * UI_ITEMS_PER_PAGE) . "," . UI_ITEMS_PER_PAGE;

    $Users = RetrieveUsers($limit);

    $Status = array("0"=>"None","1"=>"View","2"=>"Edit");//permission status captions

    $Permission = array("0"=>"Disabled","1"=>"Viewer","2"=>"Moderator","3"=>"Administrator");

    $Total = RetrieveUsersCount();

    $Total = $Total[0]['users'];

    echo "<p>Total Number of Users:$Total</p>";

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
//--- Display the table containing the list of all the users in the system
if (is_array($Users)) {

    echo TableStart();
    echo TableHeaderRow(array("User id","Username","Status","Actions"));
    
    foreach ($Users as $User) {
        
        $edit_link = "<a href='user_editor.php?uid=" . $User['id'] . "'>edit</a>";
        $delete_link = "<a>delete</a>";
        echo TableRow(array($User['id'],$User['name'],$Permission[$User['status']],"$edit_link | $delete_link"));
        
    }
    
    echo TableEnd();
    
}

/*
<div id="dialog-modal" title="Basic modal dialog">
	<p>Are you sure you want to delete this user? This cannot be undone!</p>
</div>

<script>
$(function() {

    $( "#dialog-modal" ).dialog({
            height: 140,
            modal: true
    });
});
</script>
*/

include_once ("footer.php");
?>
