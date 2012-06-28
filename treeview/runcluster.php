<?php

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
session_start();
$json = Array();

// get the /tmp/php..... file uploaded to the server
//$infile = "{$_FILES['file']['tmp_name']}";
$infile=$_SESSION['filename'];

// if the user elected to add new leaf labels, create file
// containing the user input values
   $addLabels=TRUE;
   $labelFile="/tmp/labelFile" . rand(0,10000);
   $fp = fopen($labelFile,'w');
	error_log($_POST['labels2']);
	error_log($_POST['labels']);
   fwrite($fp,$_POST['labels2']);
   fclose($fp);

// if uploading a tsv or csv indicated by the POST['type']
if ( $_POST['type']=='tsv' or $_POST['type']=='csv' or $_POST['type']=='txt')
{
    // get clustering parameters
    $method = $_POST['method'];
    $metric = $_POST['metric'];
    $output = $_POST['outputtype']; // i.e. pdf, svg, phyloxml
    $title  = ( $_POST['title'] );
    $p      = $_POST['p'];
    $type   = $_POST['type'];
    # $addLabels = $addLabels;
    # $labelFile = $labelFile;

	$scrubtags = $_SESSION['scrubtags'];
	$divitags  = $_SESSION['divitags'];



    // build the argument string for Rscript
    // flags do nothing,
    // future: make flags work, right now, R script assumes current order
    $rArgs = "$infile $method $metric $output \"$title\"
	$p $type $labelFile $scrubtags $divitags";

	// stdout = <filename>,<r>,<rowlabel>,<rowlabel2> ...

    $stdout= callR( "clustr.r", "$rArgs" );

	$stdout=explode(",<r>,",$stdout);
	
	// $file: name of the file on the server
	$file = $stdout[0];
	$rowlabels = $stdout[1];

    // if the output is not pdf, open the file and dump contents to
    // json object
    if ( $output != 'pdf' )
    {
        $out = openfile( $file );

        $json['type'] = $output;
        $json['output'] = $out;
		$json['rowlabels'] = $rowlabels;
    }
    // if the output file is a pdf, we need to open the file and 
    // download the string as an application/pdf
    else
    {
        $out = openfile( $file, "b" );
	//$name = $_FILES['file']['name'];
        require( "download.php" );  // download script
        //downloadString( $out, "$name.pdf", "pdf", false );

	//The file name is the same as the dendrogram title
		downloadString($out,"$title.pdf","pdf",false);
        // and exit so nothing else is sent back to the browser
        exit;
    }
}
// if the option was to upload an xml phylo file, set the output
// to the contents of the uploaded file
else if ( $_POST['type'] == 'xml' )
{
    $json['type'] = 'phyloxml';
    $json['output'] = openfile( "$infile" );
}

// set the content type so the browser doesn't try to open the file
// in Word or Acrobat or something
// this way, the "XMLHttpRequest" object contains:
// r.responseXML.firstChild.textContent with the encoded json string,
// don't know if this works in IE
// then echo to string to the page
header("Content-type: text/javascript");
echo json_encode( $json );

?>
