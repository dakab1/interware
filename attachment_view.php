<?php
include_once ("modules/DB.php");
include_once ("verify_access.php");

extract ($_GET);

//--- Get the file contents
$Attachment = RetrieveMailAttachment($id);

if (is_array($Attachment)) {
    
    //--- Notify the browser that this is a CSV document
    header("Cache-control: private");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Description: File Transfer");
    header("Content-disposition: attachment; filename=". $Attachment[0]['filename']);
    $content =  base64_decode($Attachment[0]['data']);    
    echo $content;
    
} else {
    
    $PageTitle = "Attachment not found.";
    include_once ("header.php");
    
    echo "Attachment not found.";
    
    echo "<a href='" . $_SERVER['HTTP_REFERER'] . "'>Back to editor.</a>";
    
    include_once ("footer.php");
    
}
?>