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
    width: 275px;
    float: right;
    margin: 0px 0px 0px 10px;
    padding: 5px 0 0 5px;
}
#scrub { float: left; margin:0 10px;}
#download { float: left; margin:0 10;}
#clear { float: left; margin:0 10px; }
#buttonnav #submit { left: 0px; width:55px; }
</style>
<script type="text/javascript">
        <!--
            function tagSelect() {
                var tags = document.getElementById("tagBox");
                if(tags.style.visibility == 'visible') 
                    tags.style.visibility = 'hidden'; 
                else 
                    tags.style.visibility = 'visible';
            }
        //-->
    </script>
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
            <input type="checkbox" name="punctuation" checked="checked"/> Remove Punctuation
            <br /><input type="checkbox" name="formatting" checked="checked" onClick="tagSelect()"/> Strip Tags
            <div id="tagBox" style="visibility: visible;">
                <input type="radio" name="tags" value="keep" checked/> Keep Words in Tags<br />
                <input type="radio" name="tags" value="discard" /> Discard Words in Tags
            </div>
            <input type="checkbox" name="lowercase" checked="checked"/> Make Lowercase
            <?php if(strpos($file, "&ae;") or strpos($file, "&d;") or strpos($file, "&t;")) : ?>
                <br /><input type="checkbox" name="special" checked="checked"/> Format Special Characters
            <?php endif; ?>
            <?php if(isset($_SESSION["stopwords"])) : ?>
                <br /><input type="checkbox" name="stopwords" checked="checked"/> Remove Stopwords
            <?php endif; ?>
            <?php if(isset($_SESSION["lemmas"])) : ?>
                <br /><input type="checkbox" name="lemmas" checked="checked"/> Lemmatize
            <?php endif; ?>
            <?php if(isset($_SESSION["consolidations"])) : ?>
                <br /><input type="checkbox" name="consolidations" checked="checked"/> Consolidate
            <?php endif; ?>
            <br />
            
        </fieldset>
    </form>
    <br />

    <fieldset>
        <legend><b>Upload </b></font></legend>
        <form action="uploader.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="type" value="stopwords" /> 
            <label for="file">Stop Words:</label><br />
            <input type="file" name="file" id="file" /> 
            <br />
            <input type="submit" name="stopwords" value="Upload Stop Words" />
        </form>
        <form action="uploader.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="type" value="lemmas" /> 
            <label for="file">Lemmas:</label><br />
            <input type="file" name="file" id="file" /> 
            <br />
            <input type="submit" name="lemmas" value="Upload Lemmas" />
        </form>
        <form action="uploader.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="type" value="consolidations" /> 
            <label for="file">Consolidations:</label><br />
            <input type="file" name="file" id="file" /> 
            <br />
            <input type="submit" name="consolidations" value="Upload Consolidations" />
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