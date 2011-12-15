<?php
// /action.php
// the single script that all ajax and upload requests are made to
// info about the request is made via POST['action']

require_once dirname(__FILE__) . '/nav.php';
require_once dirname(__FILE__) . '/error.php';

$msg = new Msg();
$action = $_POST['action'];
if (!$action) {
    $msg->mlog( 'e', "No action." );
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
                $msg->rlog('textname', $_FILES['file']['name']);
                $msg->rlog('text', utf8_decode($str));
            }
            else
                $msg->mlog('e', "File upload error.");
        }
        else
            $msg->mlog('e', "Major file upload error.");
        break;
    default :
        $msg->mlog('w', "No action in switch.");
    }
}

// output
header("Content-type: application/json");
$msg->json_output();

?>
