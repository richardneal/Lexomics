<?php
// diviText is a graphical text segmentation tool for use in text mining.
//     Copyright (C) 2011 Amos Jones and Lexomics Research Group
// 
//     This program is free software: you can redistribute it and/or modify
//     it under the terms of the GNU General Public License as published by
//     the Free Software Foundation, either version 3 of the License, or
//     (at your option) any later version.
// 
//     This program is distributed in the hope that it will be useful,
//     but WITHOUT ANY WARRANTY; without even the implied warranty of
//     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//     GNU General Public License for more details.
// 
//     You should have received a copy of the GNU General Public License
//     along with this program.  If not, see <http://www.gnu.org/licenses/>.

// get working directory, because other script will most certainly change it
// and we want to get back there so we can grab the footer
$INDEXWD = getcwd();

$HOME  = ".";           // home of index is at root of diviText app
$TITLE = "scrubber";    // title of page
$DTVERSION = "1.2.1";   // current version of diviText

// require nav page for quick navigation of important files
require( "$HOME/includes/nav.php" );

// require the header
// includes <html> and <head> as well as <script> includes
require( $HEADER ); 

?>

<!-- BEGIN CONTENT -->


<script type="text/javascript" src="scrubber.js"></script>

<!-- VISUAL CUTTER PANEL -->
<div id="cutter-panel"></div>

<!-- HELP/INFO PANELS -->
<div id="lemmas">
<h3>Lemma Info</h3>
You'll be able to upload a lemma list here.
</div>

<div id="orthography">
<h3>Orthography Info</h3>
You'll be able to upload a list of orthographical replacements here.
</div>

<div id="stopwords">
<h3>Stop Words Info</h3>
You'll be able to upload a list of stop words here.
</div>














<!-- END CONTENT -->

<? 
chdir( "$INDEXWD" );    // go back to diviText root
require( $FOOTER );     // require the footer 
?>
