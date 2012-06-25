<?php // /modules/texts/merge.php
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

// merge.php
// uses a list of chunkset ids to merge into a single vector field
// POST-PARAMS:
//  name:string
//      - name to tack onto download file
//  transpose:string
//      - if "true", transpose the matrix to have words as column headers,
//        else, chunks are column headers
//  chunksets:jsonstring
//      - array of objects with chunkset ids and corresponding text ids
//      : [
//          {csid:'foochunks',textid:'footext'},
//          {csid:'barchunks10',textid'bar'}
//        ]
// RETURN:
//  - nothing, octet download stream of merge file directly downloaded by 
//    browser

$DELIM = "\t";
$HOME = "../..";

// require some important scripts
require_once( "$HOME/includes/nav.php" );
require_once( $MODLOGIN );
require_once( $MODDOWNLOAD );
require_once( $MODTEXTS );

// start session
session_start();
login();

$metadata = "";
$divimeta = "";

// get user info
$uid = $_SESSION['user']['id'];
$utexts = $_SESSION['user']['texts'];

// get merge info
$mergename = $_POST['name'];    // name of merge file
$transpose = $_POST['transpose'] == 'true' ? true : false;

$csjson = $_POST['chunksets'];  // json list of chunksets
$csjson = preg_replace( "/'/", "\"", $csjson );
$cslist = json_decode( $csjson );

// get a hash for each chunkset
$cshash = null;
foreach ( $cslist as $_cs )
{
    // odd notation, not in an assoc array, but as an object, thanks 
    // json_decode
    $tid = $_cs->textid;
    $csid = $_cs->csid;
    $text = $utexts["$tid"];
    if( $text )
    {
        $metadata .= $text->get_metadata();
        $allcs = $text->get_chunksets();
        $cs = $allcs ? $allcs["$csid"] : null;
        if ( $cs )
        {
            $cshash[] = $cs->get_hash();
        }
    }
}

if ( !$cshash )
    exit();

// want all unqiue words
$allwords = null;       // a list of all words, dupes too
$quickhash = array();   // hash indexed by chunk name
foreach ( $cshash as $cs )
{
    foreach ( $cs as $name => $hash )
    {
        $hashkeys = array_keys( $hash );
        $allwords = array_merge( $hashkeys, (array)$allwords );
        $quickhash["$name"] = $hash;
    }
}
$uniquewords = array_unique( $allwords );   // set of unique words across texts
$chunknames = array_keys( $quickhash );

$merge = "";
$file = "";

// words as column headers
if ( $transpose )
{
    $merge .= "Transposed$DELIM";
    $merge .= implode( "$DELIM", $uniquewords ) . "\n";
    foreach ( $chunknames as $chunk )
    {
        $line = "$chunk";

        if($divimeta == ""){
            $divimeta = "DiviText Options: " . substr($chunk, 0, strpos($chunk, "_")) . " Word Chunks, " . substr($chunk, strpos($chunk, "_", 6)+1, 1) . "." . substr($chunk, strpos($chunk, "_", 6)+2, 1) . " Last Proportion";
        }

        foreach ( $uniquewords as $word )
        {
            $count = @$quickhash[$chunk]["$word"];
            $count = $count ? $count : 0;
            $line .= "$DELIM$count";
        }
        $merge .= "$line\n";
    }
    $merge .= $metadata . "\n" . $divimeta;

    $file = "merge_transpose_$mergename.tsv";
}
// chunk names as column headers
else
{

    $merge .= "Key$DELIM";
    $merge .= implode( "$DELIM", $chunknames ) . "\n";
    foreach ( $uniquewords as $word )
    {
        $line = "$word";
        foreach ( $chunknames as $chunk )
        {
            // @ hides any thrown error message
            $count = @$quickhash[$chunk]["$word"];
            $count = $count ? $count : 0;
            $line .= "$DELIM$count";
        }
        $merge .= "$line\n";
    }
    $merge .= $metadata;

    $file = "merge_$mergename.tsv";
}
downloadString( $merge, $file, "tsv", true );
?>
