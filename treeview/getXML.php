<?php
//This script runs a cluster analysis on the user's data, converts the dendrogram into an xml file and then has the user download said file

//start the session
session_start();

// call Rscript using the file $r with arguments $a
// and return the STDOUT output of the script
function callR( $r, $a = "" )
{
    $cmd = escapeshellcmd( "Rscript $r $a" );
    return `$cmd`;
}

// open any file $f with mode $m added to 'r' fopen flag 
// and return the contents of the file
function openfile( $f = "", $m = "" )
{
    $contents = "";
    if ( $f )
    {
        $FH = fopen( $f, "r$m" );
        $contents = fread( $FH, filesize( $f ) );
        fclose( $FH );
    }

    return $contents;
}

// init array to return to the browser
$json = Array();

// get the /tmp/php..... file uploaded to the server
//$infile = "{$_FILES['file']['tmp_name']}";
$infile = $_SESSION['filename'];

// if the user elected to add new leaf labels, create file
// containing the user input values
   $addLabels=TRUE;
   $labelFile="/tmp/labelFile" . rand(0,10000);
error_log($labelFile);
   $fp = fopen($labelFile,'w');
   fwrite($fp,$_POST['labels2']);
   fclose($fp);


// if uploading a tsv indicated by the POST['type']
if ( $_POST['type']=='tsv' or $_POST['type']=='csv' or $_POST['type']=='txt')
{
    // get clustering parameters
    $method = $_POST['method'];
    $metric = $_POST['metric'];
    $output = 'phyloxml'; //create an xml file
    $title  = ( $_POST['title'] );
    $type   = $_POST['type'];
    $p	    = $_POST['p'];
    # $addLabels = $addLabels;
    # $labelFile = $labelFile;

	$scrubtags = $_SESSION['scrubtags'];
	$divitags  = $_SESSION['divitags'];

    // build the argument string for Rscript
    // flags do nothing,
    // future: make flags work, right now, R script assumes current order

    $rArgs = "$infile $method $metric $output \"$title\"
	$p $type $labelFile $scrubtags $divitags";

    // the file name is the expected output of clustr.r
    $stdout = callR( "clustr.r", "$rArgs" );
	

	$stdout=explode(",<r>,",$stdout);

	$file = $stdout[0];
	$rowlabels = $stdout[1];



    $out = openfile( $file, "b" );

    //give the file to the user to download
    // $name = $_FILES['file']['name'];
    $name = $title;
    require( "download.php" );  // download script
    downloadFile( $file, "$name.xml");

    // and exit so nothing else is sent back to the browser
    exit;
}
?>
