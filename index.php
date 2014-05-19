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

        <title>Interware</title>
        <link type="text/css" href="css/emailer_general.css" rel="stylesheet" />	

        <!--<link type="text/css" href="css/custom-theme/jquery-ui-1.7.3.custom.css" rel="stylesheet" />-->	
        <link type="text/css" href="css/start/jquery-ui-1.7.3.custom.css" rel="stylesheet" />
        <script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.7.3.custom.min.js"></script>
        
        <script type="text/javascript">
        $(function() {
            $( "#accordion" ).accordion({ autoHeight: false });
        });
        </script>
         
        <style>
        body {
          min-width: 440px;      /* 2x LC width + RC width */
          margin:0px;
          padding:0px;
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
          background-color: #ABADB3;
          height:100%;
          width: 220px;          /* LC width */
          right: 220px;          /* LC width */
          margin-left: -100%;
        } 
        #right {
          width: 0px;          /* RC width */
          margin-right: -0px;  /* RC width */
        }
        
        #header {
            
            background: url('images/Interware_1024X80.jpg');
            background-repeat: no-repeat;
            background-size:100%;
            height:60px;
            color: #FFFFFF;
            
        }
        
        #footer {
            
            color: #FFFFFF;
            background-color: #4B75C2;
            font-weight: bolder;
            /*background-image: url('images/DRM-footer.png');*/
            background-repeat: no-repeat;
            background-size:100%;
            
            height:50px;
            
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
            min-height: 550px;
            border:0px;
            background-color: #E1E1E1;
        }
        
        .loginBox {
            float: right;
        }
        
        body {
            /*background-color: #4B75C2;*/
        }
        
        </style>
        
    </head> 
    <body>
         
        <div id="header">
            <!--Communication Application-->
            <div class="loginBox">
                <p>
                    Logged in as <?php echo $_SESSION['emailer']['name'] ."@" . $_SERVER["REMOTE_ADDR"]; ?>
                    (<a href="login.php?logout=1">Log-off</a>)    
                </p>
            </div>
        </div>
        
        <div id="container">
            <div id="center" class="column"><iframe id="content_area" name="content_area" src="<?php 
                
                echo ($_GET['r'] == "" ? "home.php?s=" . $_GET['s'] : $_GET['r'] . "?" . $_SERVER['QUERY_STRING']); 
                ?>"></iframe></div>

            <div id="left" class="column">

                <div id="accordion">
                        <h3><a href="#">Campaigns</a></h3>
                        <div>
                            <ul>
                                <li style="list-style-image: url('images/icons/tiny/add.png')"><a target="content_area" href="campaign_editor.php<?php echo "?s=" . $_GET['s'] ?>">Create new</a></li>
                                <li style="list-style-image: url('images/icons/tiny/zoom.png')"><a target="content_area" href="campaign_list_view.php<?php echo "?s=" . $_GET['s'] ?>">View all</a></li>
                            </ul>
                        </div>
                        <h3><a href="#">Emails</a></h3>
                        <div>
                            <ul>
                                <li style="list-style-image: url('images/icons/tiny/mailadd.png')"><a target="content_area" href="email_editor.php<?php echo "?s=" . $_GET['s'] ?>">Create new email</a></li>
                                <li style="list-style-image: url('images/icons/tiny/zoom.png')"><a target="content_area" href="email_list_view.php<?php echo "?s=" . $_GET['s'] ?>">View emails</a></li>
                                <li style="list-style-image: url('images/icons/tiny/zoom.png')"><a target="content_area" href="queued_email_view.php<?php echo "?s=" . $_GET['s'] ?>">View queue</a></li>
                            </ul>
                        </div>
                        <h3><a href="#">SMS</a></h3>
                        <div>
                            <ul>
                                <li style="list-style-image: url('images/icons/tiny/add.png')"><a target="content_area" href="sms_editor.php<?php echo "?s=" . $_GET['s'] ?>">Create new SMS</a></li>
                                <li style="list-style-image: url('images/icons/tiny/zoom.png')"><a target="content_area" href="sms_list_view.php<?php echo "?s=" . $_GET['s'] ?>">View SMS</a></li>
                                <li style="list-style-image: url('images/icons/tiny/zoom.png')"><a target="content_area" href="queued_sms_view.php<?php echo "?s=" . $_GET['s'] ?>">View queue</a></li>
                            </ul>
                        </div>
                        <h3><a href="#">Social Media</a></h3>
                        <div>
                            <ul>
                                <li style="list-style-image: url('images/icons/tiny/comment.png')"><a target="content_area" href="social_posts.php<?php echo "?s=" . $_GET['s'] ?>">Social posts</a></li>
                            </ul>
                        </div>
                        <h3><a href="#">Recipients</a></h3>
                        <div>
                            <ul>
                                <li style="list-style-image: url('images/icons/tiny/contacts.png')"><a target="content_area" href="recipient_all_view.php<?php echo "?s=" . $_GET['s'] ?>">View recipients</a></li>
                                <li style="list-style-image: url('images/icons/tiny/contactsremove.png')"><a target="content_area" href="recipient_unsubscribed_view.php<?php echo "?s=" . $_GET['s'] ?>">Opt-out recipients</a></li>
                            </ul>
                        </div>
                        <!--
                        <h3><a href="#">Reports</a></h3>
                        <div>
                                <p>coming soon</p>
                        </div>
                        -->
                        <h3><a href="#">Settings & Permissions</a></h3>
                        <div>
                            <ul>
                                <li style="list-style-image: url('images/icons/tiny/settings.png')"><a target="content_area" href="user_list_view.php<?php echo "?s=" . $_GET['s'] ?>">Users</a></li>
                                <li style="list-style-image: url('images/icons/tiny/settings.png')"><a target="content_area" href="social_list_view.php<?php echo "?s=" . $_GET['s'] ?>">Social accounts</a></li>
                                <li style="list-style-image: url('images/icons/tiny/settings.png')"><a target="content_area" href="manage_home.php<?php echo "?s=" . $_GET['s'] ?>">Home page</a></li>
                                <!--
                                <li><a target="content_area" href="user_editor.php<?php echo "?s=" . $_GET['s'] ?>">Add user</a></li>
                                <li><a target="blank" href="register-twitter.php<?php echo "?s=" . $_GET['s'] ?>">Add Twitter account</a></li>
                                <li><a target="content_area" href="register-facebook.php<?php echo "?s=" . $_GET['s'] ?>">Add Facebook account</a></li>
                                -->
                            </ul>
                        </div>
                </div>

            </div>
            <div id="right" class="column"></div>
            
        </div>

        <div id="footer">
            <!--<hr noshade="noshade" />-->
            <p>&nbsp;</p>
            <p>Interware <?=date("Y")?></p>
        </div>
                
        
    </body>
</html>