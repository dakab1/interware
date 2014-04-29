<?php
$PageTitle = "Social Media";
$PageBackground = "images/icons/Twitter.png";

include_once ("twitteroauth/twitteroauth.php");
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
<form id="statusForm" action="actions.php">
    <table style="width:100%">
        <tr>
            <td>Status text <input type="text" value="" name="status" style="width:40% " class="required"/></td>
            <td>Date <input type="text" id="datepicker1"  class="required" name="date" value="<?php echo date('Y-m-d') ?>" /></td>
            <td>Time <?php echo TimeEntryField("time", date("H:i:s"), "class='required'")?> </td>
        </tr>
        <tr>
            <td colspan="3">
                <?php
                //--- Get all the users accounts
                $Networks = RetrieveOauth();
                
                if (is_array($Networks)) {
                    
                    echo TableStart();
                    echo TableHeaderRow(array("Account","Network","Send"));
                    
                    foreach ($Networks as $Network) {
                        
                        $checkbox = "<input type='checkbox' name='network[]' value='" . $Network['id']. "' />";
                        
                        echo TableRow(array($Network['description'], $Network['network'], $checkbox));
                        
                    }
                    
                    echo TableEnd();
                    
                }
                ?>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <input type="submit" value="submit update" />
            </td>
        </tr>
    </table>
    <input type="hidden" name="a" value="8" />
    <input type="hidden" name="cid" value="<?php echo ($cid != "" ? $cid : ""); ?>" />
    <?php 
    if (isset($cid)) {
        
        echo "\n<input type='hidden' name='r' value='" . urlencode("campaign_editor.php?cid=$cid") ."' />";
    }
    ?>
</form>

<h3>Queued updates</h3>
<?php
$ScheduledPosts = RetrieveSocialPostsSchedule(false, "WHERE `send_date` >= now() ORDER BY `send_date` ASC LIMIT 5");

if (is_array($ScheduledPosts)) {
    
    echo TableStart();
    echo TableHeaderRow(array("Posted by", "To account","Content", "Send date", ""));
    
    foreach($ScheduledPosts as $Schedule) {
        
        $Post = RetrieveSocialPosts($Schedule['social_post_id']);
        $User = RetrieveUser($Post[0]['user_id']);
        $Network = RetrieveOauth($Schedule['social_network_oauth_id']);
        
        $DeleteLink = "<a href='#' onclick=\"if (confirm('Are you sure you want to delete this post?')){document.location.href='actions.php?r=social_posts.php&a=16&p_id=" . $Schedule['id'] . "'}\">delete</a>";
        echo TableRow(array($User[0]['name'], $Network[0]['description'] . " (" . $Network[0]['network'] . ")", $Post[0]['text'], $Schedule['send_date'], $DeleteLink));

    }
    
    echo TableEnd();
} else {
    
    echo "No scheduled posts";
}
?>

<h3>Previous updates</h3>
<?php
//--- Display the last view updates sent from the system
$Posts = RetrievePreviousSocialPosts("LIMIT 0,3");

if (is_array($Posts)) {
    
    echo TableStart();
    echo TableHeaderRow(array("Posted by", "To account", "Content","Sent date"));
    
    foreach ($Posts as $Post) {
    
        echo TableRow(array($Post['name'],$Post['description'] . "(". $Post['network'] . ")", $Post['text'], $Post['send_date']));
    
    }
    echo TableEnd();
    
} else {
    
    echo "No previous posts to display.";
}

?>

<h3>Mentions</h3>
<?php

//--- Get list of twitter accounts
$Accounts = RetrieveOauth();

if (is_array($Accounts)) {
    
    echo TableStart();
    echo TableHeaderRow(array("By","Content", "Date"));
    
    foreach ($Accounts as $Account) {

        if ($Account['network'] == "twitter") {

            //--- Create a TwitterOauth object with consumer/user tokens
            $twitterObj = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $Account['access_token'], $Account['access_token_secret']);
            
            $Mentions = $twitterObj->get("statuses/mentions", array("count"=>"3"));
            
            $more = true; 
            
            for($i=0; $more==true; $i++) {
                
                if (!is_object($Mentions[$i])) $more = false; //No more to display
                
                $Tweet = $Mentions[$i];
                
                $ScreenName = $Tweet->user->screen_name;

                $User = $twitterObj->get("users/profile_image:", array("screen_name"=>$ScreenName, "size"=>"normal"));
                
                $Name = "<img src='" . $Tweet->user->profile_image_url . "' style='border:1px solid #000' /><br/><a target='blank' href='http://www.twitter.com/" . $ScreenName . "'>" . $Tweet->user->screen_name . "</a>";

                $Date = date("d M Y H:i", strtotime($Tweet->created_at));
                
                $Content = $Tweet->text;
                
                //--- Display tweet 
                if ($Content!="") echo TableRow(array($Name, $Content, $Date));
                
            }
            
        }

    }
    
    echo TableEnd();
    
}
//--- Create a TwitterOauth object with consumer/user tokens
$twitterObj = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $Network[0]['access_token'], $Network[0]['access_token_secret']);
$twitterObj->format = 'xml';

?>

<script>
$(function() {
    $( "#datepicker1" ).datepicker({ dateFormat: 'yy-mm-dd' });
});

$(document).ready(function(){
    $("#statusForm").validate();
});

</script>

<?php
include_once("footer.php");

