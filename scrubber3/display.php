<?php session_start(); ?>
<html>
<head>
<title>Success!</title>
</head>
<body>
<?php 
$file = file_get_contents($_SESSION["file"]);
$parsedstring = htmlentities($file, $flags = ENT_XML1);
echo $parsedstring;
echo "<b>This is your file: </b>" . "<br />" . substr($file, 0, 1000) . "<p>";
echo "<b>This is your file without all the formatting: </b>" . "<br />" . substr(htmlspecialchars($file), 0, 1000) . "<p>";
echo "<b>This is your file on scrubber: </b>" . "<br />" . substr($_SESSION["scrubbed"], 0, 1000) . "<p>";
if($_SESSION["stopwords"])
    echo "<b>Your stopwords are: </b>" . "<br />" . file_get_contents($_SESSION["stopwords"]) . "<p>";
if($_SESSION["lemmas"])
    echo "<b>Your lemmas are: </b>" . "<br />" . preg_replace("/(\r?\n)/", "<br />", str_replace(", ", "->", file_get_contents($_SESSION["lemmas"]))) . "<p>";
if($_SESSION["consolidations"])
    echo "<b>Your consolidations are: </b>" . "<br />" . preg_replace("/(\r?\n)/", "<br />", str_replace(", ", "->", file_get_contents($_SESSION["consolidations"]))) . "<p>";
?>

<form action='scrub.php' method="post">
Remove Punctuation: <input type="checkbox" name="punctuation" checked="checked"/>
Remove Formatting: <input type="checkbox" name="formatting" checked="checked"/>
Make Lowercase: <input type="checkbox" name="lowercase" checked="checked"/>
<?php if($_SESSION["stopwords"]) : ?>
    Remove Stopwords: <input type="checkbox" name="stopwords" checked="checked"/>
<?php endif; ?>
<?php if($_SESSION["lemmas"]) : ?>
    Lemmatize: <input type="checkbox" name="lemmas" checked="checked"/>
<?php endif; ?>
<?php if($_SESSION["consolidations"]) : ?>
    Consolidate: <input type="checkbox" name="consolidation" checked="checked"/>
<?php endif; ?>
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
<input type="hidden" name="type" value="consolidations" /> 
<label for="file">Consolidations:</label>
<input type="file" name="file" id="file" /> 
<br />
<input type="submit" name="stopwords" value="Upload Consolidations" />
</form>

<form action='downloader.php' method="post">
<input type="submit" name="submit" value="Download">
</form>


</body>
</html>
