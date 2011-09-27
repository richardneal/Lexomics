<?php
//This script runs a cluster analysis on the user's data, converts the dendrogram into an xml file and then has the user download said file


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
$infile = "{$_FILES['file']['tmp_name']}";

// if uploading a tsv indicated by the POST['type']
if ( $_POST['type'] == 'tsv' )
{
    // get clustering parameters
    $method = $_POST['method'];
    $metric = $_POST['metric'];
    $output = 'phyloxml'; //create an xml file
    $title  = ( $_POST['title'] );

    // build the argument string for Rscript
    // flags do nothing,
    // future: make flags work, right now, R script assumes current order
    $rArgs = "-f $infile -m $method -d $metric -o $output -t \"$title\"";

    // the file name is the expected output of clustr.r
    $file = callR( "clustr.r", "$rArgs" );



    $out = openfile( $file, "b" );

    //give the file to the user to download
    $name = $_FILES['file']['name'];
    require( "download.php" );  // download script
    downloadFile( $file);

    // and exit so nothing else is sent back to the browser
    exit;
}
?>
