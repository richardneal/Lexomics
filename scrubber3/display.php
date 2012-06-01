<?php session_start(); ?>
<html>
<head>
<title>Success!</title>
</head>
<body>
<?php 
$file = file_get_contents($_SESSION["file"]);
echo "<b>This is your file: </b>" . "<br>" . $file . "<p>";
echo "<b>This is your file without all the formatting: </b>" . "<br>" . htmlspecialchars($file) . "<p>";
echo "<b>This is your file on scrubber: </b>" . "<br>" . $_SESSION["scrubbed"] . "<p>";
?>

<form action='scrub.php' method="post">
Remove Punctuation: <input type="checkbox" name="punctuation" checked="checked"/>
Remove Formatting: <input type="checkbox" name="formatting" checked="checked"/>
<input type="submit" name="submit" value="Scrub">
</form>

<form action="uploader.php" method="post"
enctype="multipart/form-data">
<input type="hidden" name="type" value="stopwords" /> 
<label for="file">Stop Words:</label>
<input type="file" name="file" id="file" /> 
<br />
<input type="submit" name="stopwords" value="Upload Stop Words" />
</form>

<form action="uploader.php" method="post"
enctype="multipart/form-data">
<input type="hidden" name="type" value="lemmas" /> 
<label for="file">Lemmas:</label>
<input type="file" name="file" id="file" /> 
<br />
<input type="submit" name="stopwords" value="Upload Lemmas" />
</form>

<form action="uploader.php" method="post"
enctype="multipart/form-data">
<input type="hidden" name="type" value="orth" /> 
<label for="file">Filename:</label>
<input type="file" name="file" id="file" /> 
<br />
<input type="submit" name="stopwords" value="Upload Orthographical Replacements" />
</form>


</body>
</html>
