<?php
session_start();

//--- Permission check
if ($Permissions[$_SERVER['PHP_SELF']] > $_SESSION['emailer']['permission_social_medias']) {
    echo "<span>You do not have permission to access this page</span>";
    include_once ("footer.php");
    die;
} 

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
