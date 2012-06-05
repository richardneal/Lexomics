<?php 
session_start(); 
$file = file_get_contents($_SESSION["file"]);
if (!isset($_SESSION["POST"])) {
    $_SESSION["POST"]["punctuation"] = "on";
    $_SESSION["POST"]["formatting"] = "on";
    $_SESSION["POST"]["lowercase"] = "on";
    $_SESSION["POST"]["special"] = "on";
    $_SESSION["POST"]["stopwords"] = "on";
    $_SESSION["POST"]["lemmas"] = "on";
    $_SESSION["POST"]["consolidations"] = "on";
}
?>
<html>
<head>
<title>Scrubber</title>
<style type="text/css">
#container
{
    min-width: 1024px;
    width:expression(document.body.clientWidth < 1024? "1024px": "auto" );
}
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
#unscrubbedtext { 
    height: 35%;
    overflow-y: auto;
    overflow-x: hidden;
    margin-bottom: 2%;
}
#scrubbedtext { 
    height: 35%;
    overflow-y: auto;
    overflow-x: hidden;
    margin-bottom: 3%;
}
#stopwordtext {
    height: 20%;
    overflow-y: auto;
    overflow-x: hidden;
    float: left;
    margin-left: 30px;
    margin-right: 10%;
}
#lemmatext {
    height: 20%;
    overflow-y: auto;
    overflow-x: hidden;
    float: left;
    
}
#consolidationtext {
    height: 20%;
    overflow-y: auto;
    overflow-x: hidden;
    float: left;
    margin-left: 10%;
    margin-right: 30px;
}

</style>
<script type="text/javascript">
        <!--
            function tagSelect(formatting) {
                var tags = document.getElementById("tagBox");
                if(formatting.checked == 1) 
                    tags.style.visibility = 'visible'; 
                else 
                    tags.style.visibility = 'hidden';
            }
        //-->
    </script>
</head>
<body>
    <div id="container">
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
            <input type="checkbox" name="punctuation" <?php if(isset($_SESSION["POST"]["punctuation"])) echo "checked" ?>/> Remove Punctuation
            <br /><input type="checkbox" name="formatting" <?php if(isset($_SESSION["POST"]["formatting"])) echo "checked" ?> onClick="tagSelect(formatting)"/> Strip Tags
            <div id="tagBox" style=<?php if(is_null($_SESSION["POST"]["formatting"])) echo "visibility: invisible;" ?>>
                <input type="radio" name="tags" value="keep" checked/> Keep Words in Tags<br />
                <input type="radio" name="tags" value="discard" /> Discard Words in Tags
            </div>
            <input type="checkbox" name="lowercase" <?php if(isset($_SESSION["POST"]["lowercase"])) echo "checked" ?>/> Make Lowercase
            <?php if(strpos($file, "&ae;") or strpos($file, "&d;") or strpos($file, "&t;")) : ?>
                <br /><input type="checkbox" name="special" <?php if(isset($_SESSION["POST"]["special"])) echo "checked" ?>/> Format Special Characters
            <?php endif; ?>
            <?php if(isset($_SESSION["stopwords"])) : ?>
                <br /><input type="checkbox" name="stopwords" <?php if(isset($_SESSION["POST"]["stopwords"])) echo "checked" ?>/> Remove Stopwords
            <?php endif; ?>
            <?php if(isset($_SESSION["lemmas"])) : ?>
                <br /><input type="checkbox" name="lemmas" <?php if(isset($_SESSION["POST"]["lemmas"])) echo "checked" ?>/> Lemmatize
            <?php endif; ?>
            <?php if(isset($_SESSION["consolidations"])) : ?>
                <br /><input type="checkbox" name="consolidations" <?php if(isset($_SESSION["POST"]["consolidations"])) echo "checked" ?>/> Consolidate
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


<div id="unscrubbedtext">
<?php 
echo "<b>This is your unscrubbed file: </b>" . "<br />" . htmlspecialchars($file) . "<p>";
?>

</div>
<div id="scrubbedtext">
<?php 
if(isset($_SESSION["scrubbed"]))
	echo "<b>This is your scrubbed file: </b>" . "<br />" . $_SESSION["scrubbed"] . "<p>";
?>
</div>

<div id="stopwordtext">
<?php 
if(isset($_SESSION["stopwords"]))
    echo "<b>Your stopwords are: </b>" . "<br />" . file_get_contents($_SESSION["stopwords"]) . "<p>";
?>
</div>

<div id="lemmatext">
<?php 
if(isset($_SESSION["lemmas"]))
    echo "<b>Your lemmas are: </b>" . "<br />" . preg_replace("/(\r?\n)/", "<br />", str_replace(", ", " → ", file_get_contents($_SESSION["lemmas"]))) . "<p>";
?>
</div>

<div id="consolidationtext">
<?php 
if(isset($_SESSION["consolidations"]))
    echo "<b>Your consolidations are: </b>" . "<br />" . preg_replace("/(\r?\n)/", "<br />", str_replace(", ", " → ", file_get_contents($_SESSION["consolidations"]))) . "<p>";
if(is_null($_SESSION["POST"]["formatting"])){
    echo "check";
    $tagBox = "invisible";
}
?>

</div>
</div>
</body>
</html>