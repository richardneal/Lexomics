<?php session_start(); ?>
<html>
<head>
<title>Scrubber</title>
</head>
<body>
<?php 
$file = file_get_contents($_SESSION["file"]);
echo "<b>This is the start of your file: </b>" . "<br />" . substr(htmlspecialchars($file), 0, 1000) . "<p>";
//echo "<b>This is your file without all the formatting: </b>" . "<br />" . substr(htmlspecialchars($file), 0, 1000) . "<p>";
if(isset($_SESSION["scrubbed"]))
	echo "<b>This is that start of your scrubbed file: </b>" . "<br />" . substr($_SESSION["scrubbed"], 0, 1000) . "<p>";
if(isset($_SESSION["stopwords"]))
    echo "<b>Your stopwords are: </b>" . "<br />" . file_get_contents($_SESSION["stopwords"]) . "<p>";
if(isset($_SESSION["lemmas"]))
    echo "<b>Your lemmas are: </b>" . "<br />" . preg_replace("/(\r?\n)/", "<br />", str_replace(", ", "->", file_get_contents($_SESSION["lemmas"]))) . "<p>";
if(isset($_SESSION["consolidations"]))
    echo "<b>Your consolidations are: </b>" . "<br />" . preg_replace("/(\r?\n)/", "<br />", str_replace(", ", "->", file_get_contents($_SESSION["consolidations"]))) . "<p>";
?>

<form action='scrub.php' method="post">
<fieldset>
<legend><b>Scrubbing Options </b></font></legend>
<table width="50%" cellpadding="10" cellspacing="0" border="0">
<tr><td width="40%">
Strip Tags: <input type="checkbox" name="punctuation" checked="checked"/>
<br />Remove Formatting: <input type="checkbox" name="formatting" checked="checked"/>
<br />Make Lowercase: <input type="checkbox" name="lowercase" checked="checked"/>
<?php if(strpos($file, "&ae;")) : ?>
	<br />Format Special Characters: <input type="checkbox" name="special" checked="checked"/>
<?php endif; ?>
</td>
<td width="40%">
<?php if(isset($_SESSION["stopwords"])) : ?>
    Remove Stopwords: <input type="checkbox" name="stopwords" checked="checked"/>
<?php endif; ?>
<?php if(isset($_SESSION["lemmas"])) : ?>
    <br />Lemmatize: <input type="checkbox" name="lemmas" checked="checked"/>
<?php endif; ?>
<?php if(isset($_SESSION["consolidations"])) : ?>
    <br />Consolidate: <input type="checkbox" name="consolidation" checked="checked"/>
<?php endif; ?>
<br />
</td>
<td width="0%">
<input type="submit" name="submit" value="Scrub">
</td>
</tr>
</table>
</fieldset><br>
</form>


<fieldset>
<legend><b>Upload </b></font></legend>
<table width="50%" cellpadding="10" cellspacing="0" border="0">
<tr><td width="30%">
<form action="uploader.php" method="post"
enctype="multipart/form-data">
<input type="hidden" name="type" value="stopwords" /> 
<label for="file">Stop Words:</label>
<input type="file" name="file" id="file" /> 
<br />
<input type="submit" name="stopwords" value="Upload Stop Words" />
</form>
</td>
<td width="30%">
<form action="uploader.php" method="post"
enctype="multipart/form-data">
<input type="hidden" name="type" value="lemmas" /> 
<label for="file">Lemmas:</label>
<input type="file" name="file" id="file" /> 
<br />
<input type="submit" name="stopwords" value="Upload Lemmas" />
</form>
</td>
<td width="30%">
<form action="uploader.php" method="post"
enctype="multipart/form-data">
<input type="hidden" name="type" value="consolidations" /> 
<label for="file">Consolidations:</label>
<input type="file" name="file" id="file" /> 
<br />
<input type="submit" name="stopwords" value="Upload Consolidations" />
</form>
</td>
</tr>
</table>
</fieldset><br>
</form>

<?php if(isset($_SESSION["scrubbed"])) : ?>
    <form action='downloader.php' method="post">
	<input type="submit" name="submit" value="Download">
	</form>
<?php endif; ?>

<form action='clear.php' method="post">
<input type="submit" name="submit" value="Clear">
</form>


</body>
</html>
