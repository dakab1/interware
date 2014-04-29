<?php
$PageTitle = "Social Accounts View";

include_once ("modules/DB.php");
include_once ("header.php");
include_once ("modules/UI.php");

extract ($_GET);

//--- Permission check
if ($Permissions[$_SERVER['PHP_SELF']] > $_SESSION['emailer']['permission_social_medias']) {
    echo "<span>You do not have permission to access this page</span>";
    include_once ("footer.php");
    die;
} 

?>
<div>
    <input type="button" value="Add Twitter Account" onclick="document.location.href='register-twitter.php'"/>
    <input type="button" value="Add Facebook Account" onclick="document.location.href='register-facebook.php'" />
</div>
<?php

$Networks = RetrieveOauth();

//--- Display the table containing the list of all the users in the system
if (is_array($Networks)) {

    echo TableStart();
    echo TableHeaderRow(array("Name","Network","Actions"));
    
    foreach ($Networks as $Network) {
        
        $delete_link = "<a href=\"#\" onclick=\"javascript: if(confirm('Are you sure you want to delete the " . $Network['network']. " account " . $Network['description']. "?')) {document.location.href='" . THISURL. PATH . "/actions.php?a=14&sn_id=" . $Network['id'] . "&r=social_list_view.php';}\">delete</a>";
        echo TableRow(array($Network['description'],$Network['network'],$delete_link));
        
    }
    
    echo TableEnd();
    
} else {
    
    echo "No social networks have been configured";
    
}

?>
