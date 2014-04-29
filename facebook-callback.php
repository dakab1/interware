<?php
$PageTitle = "Facebook setup";

include_once ("modules/config.php");
include_once ("modules/DB.php");
include_once ("facebookoauth/facebook.php");
include_once ("header.php");

session_start();

global $FacebookConfig;

$facebook = new Facebook($FacebookConfig);

$sn_id = SaveOauth($_SESSION['emailer']['id'], $description, $facebook->getAccessToken(), $facebook->getApiSecret(), "Facebook");

$Pages = $facebook->api("/me/accounts", "GET");

//--- Disable the list of pages in a drop down
if (is_array($Pages) && isset ($sn_id)) {
    
    echo "<p>Select a page where published posts will appear and click the link button.</p>";
    
    echo  "\n<form action='actions.php' method='post' onsubmit='return validate(this)'>";
    echo "\n<select id='extra' name='extra' onchange=\"document.getElementById('description').value=this.options[this.selectedIndex].text\">";
    echo "<option value=''>---</option>";
    foreach ($Pages['data'] as $Page) {

        echo "\t\t\n<option value='" . $Page['id'] . "|" . $Page['access_token'] . "'>" . $Page['name'] . " (" . $Page['category']. ")</option>";
    
    }
    
    echo "\n</select>";
    echo "\n<input type='submit' value='Link' />";
    echo "\n<input type='hidden' id='description' name='description' value=''/>";
    echo "\n<input type='hidden' name='token' value='" . $facebook->getAccessToken() . "'/>";
    echo "\n<input type='hidden' name='network' value='Facebook'/>";
    echo "\n<input type='hidden' name='a' value='15'/>";
    echo "\n<input type='hidden' name='sn_id' value='$sn_id' />";
    echo "\n<input type='hidden' name='r' value='index.php?r=" . urlencode("social_list_view.php") ."' />";
    echo "\n</form>";
    
}

include_once ("footer.php");
?>
<script>
    function validate (formObj) {
        
        if (formObj.elements['extra'].value == '') {
            
            alert ("Please select a fan page before proceeding");
            return false;
        }
        
        return true;
    }
</script>
