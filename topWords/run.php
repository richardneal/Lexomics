 <?php
// run.php
// called by rtest.js
// uses POST to retrieve values from form
// calls 'callR.r' to create /tmp/<randomnumber>/<filename>.zip
// downloads zip file

// function: callR ****************************************************
// PRE: 	$r: name of the script that takes the arguments and calls the
//			distance function (currently callR.r)
// 		$a: string of arguments. MUST BE IN THE ORDER:
//			$infile $clades $levels $metric $p $linkage $outfile $distance $tsv
// POST: returns ALL STANDARD OUTPUT from the R function followed by
//			the full path to the zip file. (newline delimited)
// *******************************************************************
function callR($r, $a = "")
{
        $cmd = escapeshellcmd("Rscript $r $a");
        return `$cmd`;
}



// if anything needed to be sent back to the browser...
// $json = Array()

// get the /tmp/php... file uploaded to the server
$infile = "{$_FILES['file']['tmp_name']}";
//copy($infile , "/tmp/foo.tsv");
//$infile = "/tmp/foo.tsv";

$clades		= $_POST['clades'];	// number of clades the tree is to be cut in to
$levels		= $_POST['levels'];	// number of KW values to look at
$linkage	= $_POST['method'];	// average, ward, ... etc
$metric		= $_POST['metric'];	// euclidean, maximum, ... etc
$p			= $_POST['p'];		// minkowski power
$dataset	= $_POST['dataset'];// outfile name
$distance	= $_POST['distance']; // Kruskal-Wallis, Anova-F, ... etc
// This flag is here in the case that more types may be added
$tsv		= TRUE;

// remove all characters other than a-z, A-Z, and 0-9 from the
//  output file name
// otherwise R will fail and tool will break
$pattern='/[^a-zA-Z0-9]/';
$dataset=preg_replace($pattern,"_",$dataset);
// in the case that the user does not provide an output file name
if ($dataset=='') $dataset="dist-output";

//dist.function.R takes number as argument for distance metric
if ($distance=='Kruskal-Wallis') $distance = 1;
//elseif ($distance=='Absolute-Distance') $distance = 2;
else $distance = 2;


// string of arguments used by the RSCRIPT
// MUST BE IN THIS ORDER
$rArgs = "$infile $clades $levels $metric $p $linkage $dataset $distance $tsv";
$stdout=callR("callR.r","$rArgs");

// $stdout contains all stdout of R code
// split $stdout at newlines
$stdout=explode("\n",$stdout);
// $stdout is an array. The last thing in the array is the full path
//  of the actual file that we need
$file=$stdout[count($stdout)-1];

// ** Download the zipped file ******************************
//copy($file, "/tmp/foo.zip" );
if (file_exists($file)) {
	header("Content-type: application/zip");
	header("Content-Disposition: attachment; filename=$dataset.zip");
	header("Pragma: no-cache");
	header("Expires: 0");
	ob_clean();
	flush();
	readfile($file);
}
exit;
?>
