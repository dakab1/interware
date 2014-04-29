<?php
//--- Check if the user is logged in
session_start($_GET["s"]);

if (!isset($_SESSION['emailer'])) {
    
    header("location:login.php");
    
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>

        <title>DRM Consulting emailer</title>
        <link type="text/css" href="css/smoothness/jquery-ui-1.7.3.custom.css" rel="stylesheet" />	
        <link type="text/css" href="css/emailer_general.css" rel="stylesheet" />	
        <script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.7.3.custom.min.js"></script>
        <script type="text/javascript" src="js/jquery.validate.js"></script>

        <script type="text/javascript">
        $(function() {
            $( "#accordion" ).accordion();
        });
        </script>
         
        <style>
        body {
          min-width: 440px;      /* 2x LC width + RC width */
        }
        #container {
          padding-left: 220px;   /* LC width */
          padding-right: 0px;  /* RC width */
        }
        #container .column {   
          position: relative;
          float: left;
        }
        #center {
          width: 100%;
        }
        #left {
          width: 220px;          /* LC width */
          right: 220px;          /* LC width */
          margin-left: -100%;
        } 
        #right {
          width: 0px;          /* RC width */
          margin-right: -0px;  /* RC width */
        }
        #footer {
          clear: both;
          text-align: center;
        }
        /*** IE6 Fix ***/
        * html .left {
          left: 0px;           /* RC width */
        } 
        
        /* Custom */
        #content_area {
            width:100%;
            height:100%;
            min-height: 500px;
            border:0px;
        }
        
        .loginBox {
            float: right;
        }  
        
        </style>
        
    </head> 
    <body>
         
        <div id="header">
            Communication Application
            <div class="loginBox">
                Logged in as <?php echo $_SESSION['emailer']['name'] ."@" . $_SERVER["REMOTE_ADDR"]; ?>
                (<a href="login.php?logout=1">Log-off</a>)    
            </div>
           <hr noshade="noshade" style="margin-top:30px;"/>
        </div>
        
        <div id="container">
            <div id="center" class="column"><iframe id="content_area" name="content_area" src="home.php"></iframe></div>

            <div id="left" class="column">

                <div id="accordion">
                        <h3><a href="#">Campaigns</a></h3>
                        <div>
                            <ul>
                                <li><a target="content_area" href="campaign_editor.php">Campaign Wizard</a></li>
                                <li><a target="content_area" href="campaign_list_view.php">View all campaigns</a></li>
                            </ul>
                        </div>
                        <h3><a href="#">Emails</a></h3>
                        <div>
                            <ul>
                                <li><a target="content_area" href="email_editor.php">Create new email</a></li>
                                <li><a target="content_area" href="email_list_view.php">View emails</a></li>
                                <li><a target="content_area" href="queued_email_view.php">View queue</a></li>
                            </ul>
                        </div>
                        <h3><a href="#">Social Media</a></h3>
                        <div>
                            <ul>
                                <li><a target="content_area" href="social_posts.php">Social posts</a></li>
                            </ul>
                        </div>
                        <h3><a href="#">Recipients</a></h3>
                        <div>
                            <ul>
                                <li><a target="content_area" href="recipient_list_view.php">View recipients</a></li>
                                <li><a target="content_area" href="recipient_unsubscribed_view.php">Opt-out recipients</a></li>
                            </ul>
                        </div>
                        <h3><a href="#">Reports</a></h3>
                        <div>
                                <p>coming soon</p>
                        </div>
                        
                        <h3><a href="#">Settings & Permissions</a></h3>
                        <div>
                            <ul>
                                <li><a target="content_area" href="user_list_view.php">View users</a></li>
                                <li><a target="content_area" href="user_editor.php">Add user</a></li>
                                <li><a target="_blank" href="register-twitter.php">Add Twitter account</a></li>
                                <li><a target="content_area" href="social_list_view.php">View social accounts</a></li>
                            </ul>
                        </div>
                </div>

            </div>
            <div id="right" class="column"></div>
            
        </div>

        <div id="footer">
            <hr noshade="noshade" />
            Copyright Reserved by DRM Consulting Pty(Ltd)
        </div>
                
        
    </body>
</html>