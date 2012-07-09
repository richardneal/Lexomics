<?php
ob_start();
session_start();

define( "SCRUB_DIR", '/tmp/scrubber/' );

function extracttext($filename) {
    //Check for extension
    //$ext = end(explode('.', $filename));
    //if its docx file
    //if($ext == 'docx')
    $dataFile = "word/document.xml";
    //else it must be odt file
    //else
    //$dataFile = "content.xml";     
       
    //Create a new ZIP archive object
    $zip = new ZipArchive;

    // Open the archive file
    if (true === $zip->open($filename)) {
        // If successful, search for the data file in the archive
        if (($index = $zip->locateName($dataFile)) !== false) {
            // Index found! Now read it to a string
            $text = $zip->getFromIndex($index);
            // Load XML from a string
            // Ignore errors and warnings
            $xml = DOMDocument::loadXML($text, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
            // Remove XML formatting tags and return the text
            return strip_tags($xml->saveXML());
        }
        //Close the archive file
        $zip->close();
    }
 
    // In case of failure return a message
    return "File not found";
}

if ( !is_dir( SCRUB_DIR ) )
    mkdir( SCRUB_DIR, 0700, true );

if ((($_FILES["file"]["type"] == "text/plain")
|| ($_FILES["file"]["type"] == "text/html")
|| ($_FILES["file"]["type"] == "text/sgml")
|| ($_FILES["file"]["type"] == "text/xml")))
  {
  if ($_FILES["file"]["error"] > 0)
    {
    echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
    }
  else
    {
    echo "Upload: " . $_FILES["file"]["name"] . "<br />";
    echo "Type: " . $_FILES["file"]["type"] . "<br />";
    echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
    echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";

    move_uploaded_file($_FILES["file"]["tmp_name"], SCRUB_DIR . $_FILES["file"]["name"]);
    echo "Stored in: " . SCRUB_DIR . $_FILES["file"]["name"];
    $_SESSION[$_POST['type']] = SCRUB_DIR . $_FILES["file"]["name"];
    if ($_POST['type'] == "consolidations") {
        $_SESSION["POST"]['consolidationbox'] = "on";
    }
    else if ($_POST['type'] == "lemmas") {
        $_SESSION["POST"]['lemmabox'] = "on";
    }
    else if ($_POST['type'] == "stopwords") {
        $_SESSION["POST"]['stopwordbox'] = "on";
    }
    else if ($_POST['type'] == "specials") {
        $_SESSION["POST"]['specialbox'] = "on";
    }
    header('Location: ' . "display.php");
    die();
    }
  }
elseif ($_FILES["file"]["type"] == "application/vnd.openxmlformats-officedocument.wordprocessingml.document") {
  if ($_FILES["file"]["error"] > 0)
    {
    echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
    }
  else
    {
    echo "Upload: " . $_FILES["file"]["name"] . "<br />";
    echo "Type: " . $_FILES["file"]["type"] . "<br />";
    echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
    echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";
    $file = SCRUB_DIR . basename($_FILES["file"]["name"], ".docx") . ".txt";
    $writefile = fopen($file, 'w') or die("can't open file");
    fwrite($writefile, extracttext($_FILES["file"]["tmp_name"]));
    fclose($writefile);
    echo "Stored in: " . $file;
    $_SESSION[$_POST['type']] = $file;
    
    header('Location: ' . "display.php");
    die();
    }
}
else
  {
    echo "Upload: " . $_FILES["file"]["name"] . "<br />";
    echo "Type: " . $_FILES["file"]["type"] . "<br />";
    echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
    //header('Location: ' . $_SERVER['HTTP_REFERER']);
    //die();
  }
ob_flush();
?>
