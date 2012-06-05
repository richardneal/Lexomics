<?php 
session_start(); 
$file = file_get_contents($_SESSION["file"]);
if (is_null($_SESSION["POST"])) {
    $_SESSION["POST"]["punctuationbox"] = "on";
    if(preg_match("'<[^>]+>'U", $file) > 0)
        $_SESSION["POST"]["formattingbox"] = "on";
    $_SESSION["POST"]["lowercasebox"] = "on";
    if(strpos($file, "&ae;") or strpos($file, "&d;") or strpos($file, "&t;"))
        $_SESSION["POST"]["specialbox"] = "on";
    $_SESSION["POST"]["stopwordbox"] = "on";
    $_SESSION["POST"]["lemmabox"] = "on";
    $_SESSION["POST"]["consolidationbox"] = "on";
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Scrubber</title>
<link rel="stylesheet" type="text/css" href="display.css"/>
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
        <div id="buttons">
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
            <br />
        </div>
        </fieldset>
        <br />
        <fieldset>
            <legend><b>Scrubbing Options </b></font></legend>
            <input type="checkbox" name="punctuationbox" <?php if(isset($_SESSION["POST"]["punctuationbox"])) echo "checked" ?>/> Remove Punctuation
            <?php if(preg_match("'<[^>]+>'U", $file) > 0): ?>
            <br /><input type="checkbox" name="formattingbox" <?php if(isset($_SESSION["POST"]["formattingbox"])) echo "checked" ?> onClick="tagSelect(formattingbox)"/> Strip Tags
            <div id="tagBox" style=<?php if(is_null($_SESSION["POST"]["formattingbox"])) echo "visibility: invisible;" ?>>
                <input type="radio" name="tags" value="keep" checked/> Keep Words Inside Tags<br />
                <input type="radio" name="tags" value="discard" /> Discard Words Inside Tags
            </div>
            <?php else : ?>
            <br />
            <?php endif; ?>
            <input type="checkbox" name="lowercasebox" <?php if(isset($_SESSION["POST"]["lowercasebox"])) echo "checked" ?>/> Make Lowercase
            <?php if(strpos($file, "&ae;") or strpos($file, "&d;") or strpos($file, "&t;")) : ?>
                <br /><input type="checkbox" name="specialbox" <?php if(isset($_SESSION["POST"]["specialbox"])) echo "checked" ?>/> Format Special Characters
            <?php endif; ?>
            <?php if(isset($_SESSION["stopwords"])) : ?>
                <br /><input type="checkbox" name="stopwordbox" <?php if(isset($_SESSION["POST"]["stopwordbox"])) echo "checked" ?>/> Remove Stopwords
            <?php endif; ?>
            <?php if(isset($_SESSION["lemmas"])) : ?>
                <br /><input type="checkbox" name="lemmabox" <?php if(isset($_SESSION["POST"]["lemmabox"])) echo "checked" ?>/> Lemmatize
            <?php endif; ?>
            <?php if(isset($_SESSION["consolidations"])) : ?>
                <br /><input type="checkbox" name="consolidationbox" <?php if(isset($_SESSION["POST"]["consolidationbox"])) echo "checked" ?>/> Consolidate
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
    <div id="info">
        Richard Neal <br />
        <a href="http://wheatoncollege.edu/lexomics/"> Lexomics @ Wheaton College</a>
    </div>
</div>

<b>Unscrubbed: </b>
<div id="unscrubbedtext">
<?php 
    echo htmlspecialchars($file);
?>
</div>

<?php if(isset($_SESSION["scrubbed"])) : ?>
<b>Scrubbed: </b>
<div id="scrubbedtext">
<?php 
	echo $_SESSION["scrubbed"];
?>
</div>
<?php endif; ?>

<div id="stopwordtext">
<?php 
if(isset($_SESSION["stopwords"])) {
    $explodedsw = explode(", ", file_get_contents($_SESSION["stopwords"]));
    sort($explodedsw);
    echo "<b>Stop Words: </b>" . "<br />";
    foreach(array_values($explodedsw) as $swvalue)
        echo $swvalue . ", ";
    echo "<p>";
}
?>
</div>

<div id="lemmatext">
<?php 
if(isset($_SESSION["lemmas"]))
    echo "<b>Lemmas: </b>" . "<br />" . preg_replace("/(\r?\n)/", "<br />", str_replace(", ", " → ", file_get_contents($_SESSION["lemmas"]))) . "<p>";
?>
</div>

<div id="consolidationtext">
<?php 
if(isset($_SESSION["consolidations"]))
    echo "<b>Consolidations: </b>" . "<br />" . preg_replace("/(\r?\n)/", "<br />", str_replace(", ", " → ", file_get_contents($_SESSION["consolidations"]))) . "<p>";
if(is_null($_SESSION["POST"]["formatting"])){
    //this doesn't work yet
    $tagBox = "invisible";
}
?>
</div>

</div>
</body>
</html>