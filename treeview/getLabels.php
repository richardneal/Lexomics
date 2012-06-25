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
	$_SESSION['filename']="";
	$_SESSION['scrubtags']="";
	$_SESSION['divitags']="";

if ($_POST['type']=='xml')
{
	$file=$_FILES['file']['tmp_name'];
	$random=rand(0,1000);
	$filename="/tmp/treeview$random";
	move_uploaded_file($file, $filename);
	$_SESSION['filename']=$filename;
}
	
else 
{
	$file=$_FILES['file']['tmp_name'];
	$random=rand(0,1000);
	$filename="/tmp/treeview$random";
	move_uploaded_file($file, $filename);
	
	$tmpfile="/tmp/tmpfile$random";


	$ftype=$_POST['type'];
	$delim="";
	if ($ftype=='tsv') $delim="\t";
	else if ($ftype=='csv') $delim=",";
	else $delim=" ";

	$fp=fopen($filename,"r");
	$fout=fopen($tmpfile,"w");

	
	$line=fgets($fp);
	fwrite($fout,$line);	
	$line=fgets($fp);
	fwrite($fout,$line);
	if ($ftype=='txt')
		$line=preg_replace('/\s/'," ",$line);
	$arr=explode($delim,$line);
	$rowlabels=$arr[0];

	while(!feof($fp))
	{
		$line=fgets($fp);
		$scrub=strpos($line,"Scrubber Options");
		$divi=strpos($line,"DiviText Options");
		if ($line!="" && $line!="\n" && !($scrub===0) && !($divi===0))
		{
			if ($ftype=='txt')
				$line=preg_replace('/\s/'," ",$line);
			$arr=explode($delim,$line);
			$rowlabels=$rowlabels.','.$arr[0];
			fwrite($fout,$line);
		}
		else if ($scrub===0)
		{
			$line=preg_replace('/\s/',"_",$line);
			$_SESSION['scrubtags']=$line;
		}
		else if ($divi===0)
		{
			$line=preg_replace('/\s/',"_",$line);
			$_SESSION['divitags']=$line;
		}
			
		
	}

	fwrite($fout,"\r\n");

	fclose($fp);
	fclose($fout);

	$rowlabels=$rowlabels."\r\n";
	
	$_SESSION['filename']=$tmpfile;

	header("Content-type: text/javascript");	
	$json['rowlabels']=$rowlabels;
	echo json_encode($json);
}
?>
