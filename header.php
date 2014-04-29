<?php
include_once ("modules/DB.php");
include_once ("modules/utils.php");
include_once ("verify_access.php");

global $PageTitle, $PageBackground;


//--- Log the page impression to the event log
SaveEvent($_SESSION['emailer']['id'], "Page impression on " . $_SERVER['PHP_SELF'] . "| parameters:" . $_SERVER['QUERY_STRING'] . "| ip:" . $_SERVER['REMOTE_ADDR'], date("Y-m-d H:i:s"));

//--- Update the breadcrumb
UpdateBreadcrumb($PageTitle, $_SERVER['REQUEST_URI']);

?>
<!DOCTYPE html>
<head>
    <title><?php echo $PageTitle; ?></title>
    <link type="text/css" href="css/smoothness/jquery-ui-1.7.3.custom.css" rel="stylesheet" />	
    <link type="text/css" href="css/emailer_general.css" rel="stylesheet" />	
    <script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui-1.7.3.custom.min.js"></script>
    <script type="text/javascript" src="js/jquery.validate.js"></script>
</head>
<body style="background-image:url('<?php echo $PageBackground; ?>'); background-position: right top; background-repeat:no-repeat; background-size:100px 100px;">
    <h2><?php echo $PageTitle; ?></h2>        
    <?php BreadcrumbShow(); 
    echo (isset($_GET['error']) ? "<p style='color:red;'>" . $_GET['error'] . "</p>" : "");
    ?>

   