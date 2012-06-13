<?php
ob_start();
session_start();

define( "SCRUB_DIR", '/tmp/scrubber/' );

if ( !is_dir( SCRUB_DIR ) )
    mkdir( SCRUB_DIR, 0700, true );

if ((($_FILES["file"]["type"] == "text/plain")
|| ($_FILES["file"]["type"] == "text/html")
&& ($_FILES["file"]["type"] == "text/xml")))
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
    header('Location: ' . "display.php");
    die();
    }
  }
else
  {
    echo $_FILES["file"]["type"];
  //header('Location: ' . $_SERVER['HTTP_REFERER']);
  //die();
  }
ob_flush();
?>
