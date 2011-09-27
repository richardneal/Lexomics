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
$TITLE = "diviText";    // title of page
$DTVERSION = "1.2.1";   // current version of diviText

// require nav page for quick navigation of important files
require( "$HOME/includes/nav.php" );

// require the header
// includes <html> and <head> as well as <script> includes
require( $HEADER ); 

?>

<!-- BEGIN CONTENT -->


<script type="text/javascript" src="divitext.js"></script>

<!-- VISUAL CUTTER PANEL -->
<div id="cutter-panel"></div>

<!-- HELP/INFO PANELS -->
<div id="help-simple">
<h3>Simple Cutter</h3>
Cut the text into <i>chunks</i> number of chunks. The last chunk will be at 
least half of the size of all the other chunks. (Note: Depending on 
<i>chunks</i> and the length of the text, the number of chunks may not be equal 
to <i>chunks</i>.
</div>

<div id="help-advanced">
<h3>Advanced Cutter</h3>
Cut the text into chunks of fixed <i>size</i>. The last chunk will be at 
least <i>last proportion</i> times <i>size</i> words in length.
</div>

<div id="help-visual">
<h3>Visual Cutter -- Info</h3>
Click on a word to define the start of a new chunk. Click a word at the start 
of a chunk to remove the chunk break at that location.
</div>














<!-- END CONTENT -->

<? 
chdir( "$INDEXWD" );    // go back to diviText root
require( $FOOTER );     // require the footer 
?>
