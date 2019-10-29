<?php
include_once ("modules/DB.php");
include_once ("modules/utils.php");

extract($_POST);
extract($_GET);

if (isset ($u) && isset ($p)) {
    
    if ($user = Authenticate($u, $p)) {

        //--- Create a session
        $session = LoginUser($user[0]['id']);
        
        //--- Delete existing session if any
        DeleteUserSession($user[0]['id']);
        
        //--- Save the session details to the database
        SaveSession($session,$session, $user[0]['id'], date("Y-m-d H:i:s", time() + (60*60*24)), date("Y-m-d H:i:s"), $_SERVER['REMOTE_ADDR']);
        
        header("location:index.php?s=$session"); //redirect to home page
        
    } else {
        
        $Error = true;
    }
}

if (isset($_GET['logout'])) {
    
    //--- Kill the logged in sessions
    LogoutUser();
    
}
?>

<html>
    <head>

        <title>Interware</title>
        <link type="text/css" href="css/smoothness/jquery-ui-1.7.3.custom.css" rel="stylesheet" />	
        <link type="text/css" href="css/emailer_general.css" rel="stylesheet" />	
        <script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.7.3.custom.min.js"></script>
        <script type="text/javascript" src="js/jquery.validate.js"></script>

        <script>
        $(function() {
            $( "#accordion" ).accordion();
        });
        </script>
        
        <style>
            .loginBox 
            {
                
                margin-left: auto ;
                margin-right: auto ;
                margin-top: 100px;
                border:1px solid grey;
                padding:5px;
                text-align: center;
                background-image : url("images/DRM-login-box.png"); 
                background-repeat: no-repeat;
                background-size:100%;
                height:300px;
                width:400px;
                border:0px;
                padding-top: 80px;
                color:#fff;
            }
            
            body {
                
                background-image : url("images/DRM-login-page.png"); 
                height:100%; 
                margin:0; 
                padding:0;
                background-repeat: no-repeat;
                background-size:100%;
            }
        </style>
        
    </head>
    <body>
        
        <div class="loginBox">
            <form method="post" id="loginForm">
                <p style="font-weight:bolder">Please enter your login and password.</p>
                <?php
                if (isset($Error)){
                    if ($Error) echo "<span style='font:bold;color: red'>" . ($Error!="" ? $Error : "Invalid username or password.") . "</span>";
                }
                ?>
                <p>Username <input id="u" name="u" type="text "class="required"/></p>
                <p>Password &nbsp;<input name="p" type="password" class="required" /></p>
                <p><a style="color:#fff; text-decoration: underline;" href='#'>forgot your password?</a></p>
                <input type="submit" value="Login" />
            </form>
        </div>        
        
        <script>
        $(document).ready(function(){
            $("#loginForm").validate();
        });
        
        $("#u").focus();//focus on username textbox
        </script>
                
    </body>
</html>