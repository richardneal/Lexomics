<?php
// getLabels.php
// summary:
// 	- uploads the file to the server and stores it
//	with a random name in /tmp. Then stores the name
//	of that file to the session
//	- gets all of the row names in the file and 
//	return them as json to javascript
//	- get the bottom two lines (divitext/scrubber options)
//	and save them to the session
// PRE: $_FILES['tmp_name'] must be defined
// POST: 
//	- javascript: (decode) 
//	json object where <var name>.rowlabels
//	- $_SESSION['scrubtags'] : <string> information about how
//	the file was originally scrubbed. All white space has been 
//	replace by underscores.
//	- $_SESSION['divitags'] : <string> same as scrubtags above, 
//	but for divitext
//	- $_SESSION['filename'] : name of the uploaded file on the server


	session_start();
	
	// be sure that old parameters aren't being used
	$_SESSION['filename']="";
	$_SESSION['scrubtags']=" ";
	$_SESSION['divitags']=" ";

	// be sure there are no upload errors
	if ($_FILES['file']['error']!=0)
	{
		$json['error']=true;
		die();
	}
	else $json['error']=false;


//	- if the file is an xml file, save the filename to the session
//	and quit
if ($_POST['type']=='xml')
{
	// file on the server
	$file=$_FILES['file']['tmp_name'];
	// generate new random name
	$random=rand(0,1000);
	$filename="/tmp/treeview$random";
	// move file to that name
	move_uploaded_file($file, $filename);
	// save name to session
	$_SESSION['filename']=$filename;
}
	
//	- tsv, txt, or csv
//		- read the file to get the row labels and send them
//		back to javascript:
//		- get the option footers and save them to the session
//		- rewrite the file without the scrubber/divtext options
else 
{
	// file on the server
	$file=$_FILES['file']['tmp_name'];
	$random=rand(0,1000);
	$filename="/tmp/treeview$random";
	// move it to $filename
	move_uploaded_file($file, $filename);
	
	// file that contains the data without the option rows
	$tmpfile="/tmp/tmpfile$random";
	


	//	get the delimiter
	$ftype=$_POST['type'];
	$delim="";
	if ($ftype=='tsv') $delim="\t";
	else if ($ftype=='csv') $delim=",";
	else $delim="\t";

	// open both the in and outfile
	$fp=fopen($filename,"r");
	$fout=fopen($tmpfile,"w");

	// begin reading file
	// first line is the header
	$line=fgets($fp);
	fwrite($fout,$line);	
	// second line starts the data
	$line=fgets($fp);
	fwrite($fout,$line);
	// change whitespace in whitespace delimited to tab
	if ($ftype=='txt')
		$line=preg_replace('/\s/',"\t",$line);
	// - $arr is an array of all the data in the line
	// where the first element will be the row label
	$arr=explode($delim,$line);
	// 	- $rowlabels is a string that will hold all of the row labels
	//	delimited by commas
	$rowlabels=$arr[0];

	// continue reading data
	while(!feof($fp))
	{
		$line=fgets($fp);
		//	- if the line in question is a tag line, Scrubber Options or
		//	DiviText Options will be the first thing in the line.
		//	Thus, for the scrub options: If the line contains "Scrubber Obtions"
		//	$scrub will hold a 0, indicating that the string was found at the
		//	first position in the line. Otherwise, $scrub will be null
		$scrub=strpos($line,"Scrubber Options");
		// 	(same for $divi)
		$divi=strpos($line,"DiviText Options");
		// 	- if the line is empty, it will hold either "" or "\n" depending on
		//	where in the file it's located (end or not)
		//	- the triple equal dictates that not only the value must match,
		//	but also the type (a double equal would always return boolean false
		//	regardless of whether the variable was null or 0)
		if ($line!="" && $line!="\n" && !($scrub===0) && !($divi===0))
		{

			// - this if-else structure prevents an inexplicable error
			// that occurs otherwise
			if ($ftype=='txt')
			{
				$lline=preg_replace('/\s/',"\t",$line);
				$arr=explode($delim,$lline);
			}
				
			else
				$arr=explode($delim,$line);

			$rowlabels=$rowlabels.','.$arr[0];
			fwrite($fout,$line);
			
		}
		else if ($scrub===0)
		{
			// the way we're passing to R can't handle whitespace
			$line=preg_replace('/\s/',"_",$line);
			$_SESSION['scrubtags']=$line;
		}
		else if ($divi===0)
		{
			// the way we're passing to R can't handle whitespace
			$line=preg_replace('/\s/',"_",$line);
			$_SESSION['divitags']=$line;
		}
			
		
	}

	// so R is sure the file was properly ended
	fwrite($fout,"\r\n");

	// close the handles
	fclose($fp);
	fclose($fout);

	// so R knows things are ended
	$rowlabels=$rowlabels."\r\n";
	
	// save the name of the file to the session
	$_SESSION['filename']=$tmpfile;

	// pass the row names back to javascript
	header("Content-type: text/javascript");	
	$json['rowlabels']=$rowlabels;
	echo json_encode($json);
}
?>
