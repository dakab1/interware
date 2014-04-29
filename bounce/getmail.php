<?php
define ("IMAP_HOST", "mail.visionware.co.za");
define ("IMAP_PORT","143");
define ("IMAP_USER", "bounce@visionware.co.za");
define ("IMAP_PASS", "H3TeqXT)f]cF");

//--- Connect to imap server
if (!$mailbox_handle = imap_open(IMAP_HOST . ":" . IMAP_PORT, IMAP_USER, IMAP_PASS)) {
    
    die ("Failed to connect");
}

//--- Select mailbox

//--- Download emails

//--- Close connection to imap server

?>
