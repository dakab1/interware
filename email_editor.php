<?php
$PageTitle = "Email editor " . ($_GET['eid'] ? " - Mail #" . $_GET['eid'] : "");
$PageBackground = "images/icons/Messages.png";

include_once("modules/DB.php");
include_once ("header.php"); //starts session and displays all top contents

extract ($_GET);

//--- Permission check
if ($Permissions[$_SERVER['PHP_SELF']] > $_SESSION['emailer']['permission_emails']) {
    echo "<span>You do not have permission to access this page</span>";
    include_once ("footer.php");
    die;
} 

//--- Get the email contents if an email id is passed
if (isset($eid)) {
    
    //--- Get the contents of the mail
    $Mail = RetrieveMail($eid);
    
    //--- Get the mail attachments
    $Attachments = RetrieveMailAttachments($eid);
    
}

?>
<script type="text/javascript" src="tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
    // Default skin
    tinyMCE.init({
        // General options
        mode : "exact",
        elements : "elm1",
        theme : "advanced",
        plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups,autosave",

        // Theme options
        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
        theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,
        //Mad File Manager

        relative_urls : false,
        remove_script_host : false,
        convert_urls : false,        
        file_browser_callback : MadFileBrowser,

        // Example content CSS (should be your site CSS)
        content_css : "css/emailer_general.css",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "lists/template_list.js",
        external_link_list_url : "lists/link_list.js",
        external_image_list_url : "lists/image_list.js",
        media_external_list_url : "lists/media_list.js",

        // Replace values for the template plugin
        template_replace_values : {
                username : "Some User",
                staffid : "991234"
        }

    });

    function MadFileBrowser(field_name, url, type, win) {
        tinyMCE.activeEditor.windowManager.open({
            file : "mfm.php?field=" + field_name + "&url=" + url + "",
            title : 'File Manager',
            width : 640,
            height : 450,
            resizable : "no",
            inline : "yes",
            close_previous : "no"
        }, {
            window : win,
            input : field_name
        });
        
        return false;
    }

</script>
<form action="actions.php" method="post" id="emailForm" enctype="multipart/form-data">
    <input name="eid" type="hidden" value="<?php echo ($Mail[0]['id'] != "" ? $Mail[0]['id'] : ""); ?>" />
    <input name="cid" type="hidden" value="<?php echo ($cid != "" ? $cid : "1") ?>" />
    <br/>From<br/><input type="text" class="required email" name="from_address" value="<?php echo ($Mail[0]['from_address'] != "" ? $Mail[0]['from_address'] : "") ?>"/>
    <br/>bcc<br/><input type="text" name="bcc_address" value="<?php echo ($Mail[0]['bcc_address'] != "" ? $Mail[0]['bcc_address'] : "") ?>" />
    <br/>subject<br/><input type="text" class="required" name="subject" value="<?php echo ($Mail[0]['subject']!="" ? $Mail[0]['subject'] : ""); ?>" />
    
    <br/>Choose from templates<br/>
    <select onchange="
        $.get('actions.php?a=10&file=' + this.value, function(data) {
            //document.getElementById('elm1').value= data;
            tinyMCE.get('elm1').focus(); 
            tinyMCE.activeEditor.setContent(data);            
        }, 'html');">
        <option value="">None</option>
        <?php
        //--- Get the list of pre_loaded templates from the template folder
        $Templates = GetFolderContents(TEMPLATE_FOLDER);
        
        foreach ($Templates as $Template) {
            
            echo "\n<option value=\"" . urlencode(TEMPLATE_FOLDER . "/" . $Template) ."\">" . substr($Template, 0, strpos($Template, ".")) . "</option>";
            
        }
        ?>
    </select>
    <br/>
    
    <input type="button" onclick="AddFile('attachments_view')" value="Add attachment"/>
    <div id='attachments_view'>
        <?php
        //--- Get list of all attachments
        if (isset($Mail[0]['id'])) {
            
            $Attachments = RetrieveMailAttachments($Mail[0]['id']);
        
            if (is_array($Attachments)) {
                echo "\n<b>Attachments</b><br/>";
                echo "<ul>";
                foreach ($Attachments as $Attachment) {
                    echo "<li><a href='attachment_view.php?id=" . $Attachment['id']."'>" . $Attachment['filename'] . "</a></li>";
                    
                }
                echo "</ul>";
            } else {
                
                echo "<p>There are no email attachments.</p>";
            }
        } else {
            echo "<ul><li>There are no email attachments.</li></ul>";            
        }
        ?>
        
    </div>
    
    <textarea id="elm1" class="required" name="message" rows="15" cols="80" style="width: 80%"><?php echo (is_array($Mail) ? stripslashes($Mail[0]['message']) : ""); ?></textarea>
    
    <input type="hidden" name="a" value="2" />
    <input type="hidden" name="r" value="<?php echo (isset($r) ? urldecode($r) : "upload_csv.php"); ?>" />
    <input type="hidden" name="s" value="<?php echo $s; ?>" />
    <!--<input type="hidden" name="cid" value="<?php echo $_GET['cid'] ?>" />-->
    <input type="button" value="<< Back" onclick="document.location.href='<?php echo $_SERVER['HTTP_REFERER']; ?>'"/>
    <!--<input type="button" value="Preview" onclick="ShowPreview()"/>-->
    <input type="button" value="Save" onclick="ShowNext()"/>
</form> 

<script>
$(document).ready(function(){
    $("#emailForm").validate();
});

function ShowPreview () {
    
    var emailForm = document.getElementById("emailForm");
    
    //emailForm.target="_blank";
    emailForm.action="email_preview.php<?php (isset($Mail[0]['id']) ? "?eid=" . $Mail[0]['id'] : "") ?>";
    emailForm.submit();
    
}

function ShowNext () {
    
    var emailForm = document.getElementById("emailForm");
    
    //emailForm.target="self";
    emailForm.action="actions.php";
    emailForm.submit();
    
}

/* "Global Variables */
var starting_num = 4; // Number of file upload boxes to begin with
var maximum = 6; // Max number of file upload boxes
var tag = '<input type="file" name="attachment[]" /><!-- New line --><br />';

function AddFile(name){ // 
    
    var div=document.getElementById(name);

    id=0;

    for (i=0; i<div.childNodes.length; i++){ // Start a loop to search the array
        if (div.childNodes[i].nodeName=="INPUT") { // Search for the INPUT tags only
                 id++; // Inrement Count of input tags
        }
    } // End loop

    /* LEts check to see if input tags are less then max */
    if(id<maximum) { 
            /* Write the text */
            div.innerHTML += tag;
    } else {
            alert("Sorry... Can't add anymore files... Your already at " + id + "."); // Give error... To many input tags.
    }
    
}

</script>

<!--
<div id='attachments_upload'>
    Enter number of attachments to upload <input type="text" name="form_count" />
    <input type="submit" value="" />
</div>
-->

<?php
include_once ("footer.php");
?>