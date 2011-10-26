<?php 
// /action.php
// the single script that all ajax and upload requests are made to
// info about the request is made via POST['action']

require_once("includes/nav.php");
require_once($MODMSG);

$MSG = new Msg();
$action = $_POST['action'];
if (!$action) {
    $MSG->mlog( 'e', "No action." );
} 
else
{
    switch ($action) {
    case 'uploadtext':
        if ($_FILES['file']) {
            $FH = @fopen( $_FILES['file']['tmp_name'], 'r' );
            $str = utf8_encode( 
                @fread($FH, filesize( $_FILES['file']['tmp_name'])) 
            );
            @fclose($FH );
            if ($str) {
                $MSG->rlog('textname', $_FILES['file']['name']);
                $MSG->rlog('text', utf8_decode($str));
            }
            else
                $MSG->mlog('e', "File upload error.");
        }
        else
            $MSG->mlog('e', "Major file upload error.");
        break;
    default :
        $MSG->mlog('w', "No action in switch.");
    }
}

// output
header("Content-type: application/json");
$MSG->json_output();

?>
