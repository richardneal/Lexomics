<?php 
session_start(); 
$file = file_get_contents($_SESSION["file"]);
?>
<html>
<head>
<title>Scrubber</title>
<style type="text/css">
#sidebar {
    border-top: 1px solid #000000;
    border-left: 1px solid #000000;
    height: 100%;
    width: 25%;
    float: right;
    margin: 0px 0px 0px 10px;
    padding: 5px 0 0 5px;
}
#scrub { float: left; margin:0 10px;}
#download { float: left; margin:0 10;}
#clear { float: left; margin:0 10px; }
#buttonnav #submit { left: 0px; width:55px; }
</style>
</head>
<div id="sidebar">
    <fieldset>
        <legend><b>Tools </b></font></legend>
        <div id="download">
            <?php if(isset($_SESSION["scrubbed"])) : ?>
            <form action='downloader.php' method="post">
                <input type="submit" name="submit" value="Download">
            </form>
            <?php endif; ?>
        </div>
        <div id="clear">
            <form action='clear.php' method="post">
                <input type="submit" name="submit" value="Clear">
            </form>
        </div>
        <form action='scrub.php' method="post">
            <div id="scrub">
                <input type="submit" name="submit" value="Scrub">
            </div>
        </fieldset>
        <br />
        <fieldset>
            <legend><b>Scrubbing Options </b></font></legend>
            Remove Punctuation: <input type="checkbox" name="punctuation" checked="checked"/>
            <br />Strip Tags: <input type="checkbox" name="formatting" checked="checked"/>
            <br />Make Lowercase: <input type="checkbox" name="lowercase" checked="checked"/>
            <?php if(strpos($file, "&ae;")) : ?>
                <br />Format Special Characters: <input type="checkbox" name="special" checked="checked"/>
            <?php endif; ?>
            <?php if(isset($_SESSION["stopwords"])) : ?>
                <br />Remove Stopwords: <input type="checkbox" name="stopwords" checked="checked"/>
            <?php endif; ?>
            <?php if(isset($_SESSION["lemmas"])) : ?>
                <br />Lemmatize: <input type="checkbox" name="lemmas" checked="checked"/>
            <?php endif; ?>
            <?php if(isset($_SESSION["consolidations"])) : ?>
                <br />Consolidate: <input type="checkbox" name="consolidation" checked="checked"/>
            <?php endif; ?>
            <br />
            
        </fieldset>
    </form>
    <br />

    <fieldset>
        <legend><b>Upload </b></font></legend>
        <form action="uploader.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="type" value="stopwords" /> 
            <label for="file">Stop Words:</label>
            <input type="file" name="file" id="file" /> 
            <br />
            <input type="submit" name="stopwords" value="Upload Stop Words" />
        </form>
        <form action="uploader.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="type" value="lemmas" /> 
            <label for="file">Lemmas:</label>
            <input type="file" name="file" id="file" /> 
            <br />
            <input type="submit" name="stopwords" value="Upload Lemmas" />
        </form>
        <form action="uploader.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="type" value="consolidations" /> 
            <label for="file">Consolidations:</label>
            <input type="file" name="file" id="file" /> 
            <br />
            <input type="submit" name="stopwords" value="Upload Consolidations" />
        </form>
    </fieldset><br>
</div>


<body>
<?php 

echo "<b>This is the start of your file: </b>" . "<br />" . substr(htmlspecialchars($file), 0, 2000) . "…<p>";
if(isset($_SESSION["scrubbed"]))
	echo "<b>This is that start of your scrubbed file: </b>" . "<br />" . substr($_SESSION["scrubbed"], 0, 2000) . "…<p>";
if(isset($_SESSION["stopwords"]))
    echo "<b>Your stopwords are: </b>" . "<br />" . file_get_contents($_SESSION["stopwords"]) . "<p>";
if(isset($_SESSION["lemmas"]))
    echo "<b>Your lemmas are: </b>" . "<br />" . preg_replace("/(\r?\n)/", "<br />", str_replace(", ", "->", file_get_contents($_SESSION["lemmas"]))) . "<p>";
if(isset($_SESSION["consolidations"]))
    echo "<b>Your consolidations are: </b>" . "<br />" . preg_replace("/(\r?\n)/", "<br />", str_replace(", ", "->", file_get_contents($_SESSION["consolidations"]))) . "<p>";
?>

</body>
</html>