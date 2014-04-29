<?php
include_once ("../twitteroauth/twitteroauth.php");
include_once ("../facebookoauth/facebook.php");
include_once ("../modules/config.php");
include_once ("../modules/DB.php");
global $FacebookConfig;

$ScheduledPosts = RetrieveSocialPostsSchedule(false, "WHERE `send_date` <= now() AND `sent` = '0'");

if (is_array($ScheduledPosts)) {
    
    foreach($ScheduledPosts as $Schedule) {
        
        $Post = RetrieveSocialPosts($Schedule['social_post_id']);

        $Network = RetrieveOauth($Schedule['social_network_oauth_id']);

        switch ($Network[0]['network']) {
            
            case "twitter":
                
                //--- Create a TwitterOauth object with consumer/user tokens
                $twitterObj = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $Network[0]['access_token'], $Network[0]['access_token_secret']);
                $twitterObj->format = 'xml';
                
                if ($response = $twitterObj->post("statuses/update", array("status"=>$Post[0]['text']))) {

                    $SQL = "UPDATE `campaign_social` SET `sent` = '1' WHERE `id` = '" . $Schedule['id'] ."'";
                } else {

                    UpdateLog("send_social_post cron job failed to update post " ,print_r($Post,1));

                }
                
            break;
        
            case "Facebook":
                
                try {
                    $facebook = new Facebook($FacebookConfig);
                    $facebook->setAccessToken($Network[0]['access_token']);
                    $facebook->setApiSecret($Network[0]['access_token_secret']);
                    
                    //--- Debugging code for facebook post
                    if (DEBUG_MODE_CRONS) {
                        
                        DebugBox("Posting to facebook", 
                        "Posting with the following:" .
                        '<p>$Post: ' . 
                        json_encode($Post) . 
                        '</p>' . 
                        '<p>$Schedule: ' . 
                        json_encode($Schedule) . 
                        '</p>' . 
                        '<p>$Network: ' . 
                        json_encode($Network) . 
                                '</p>');
                    }
                    $Response =  $facebook->api("/" . $Network[0]['extra'] . "/feed","POST",array("message"=>$Post[0]['text']));
                    
                    if (DEBUG_MODE_CRONS) {
                        
                        DebugBox("Facebook post response", print_r($Response,1));
                        
                    }
                    
                    $SQL = "UPDATE `campaign_social` SET `sent` = '1' WHERE `id` = '" . $Schedule['id'] ."'";
                    
                } catch(FacebookApiException $e) {
                    
                    //-- Log facebook post error
                    if (DEBUG_MODE_CRONS) {
                        DebugBox("Error occured", $e);
                        UpdateLog(json_encode($Network), json_encode($e), "social_post_errors.log");
                    }
                    
                }
                
            break;
        
        }

        ExecuteQuery($SQL);

    }
    
    //echo $response; print_r($Network);// debug        
    echo "Done";
    
} else {
    
    echo "No scheduled posts";
}


?>