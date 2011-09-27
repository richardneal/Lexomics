<?php // /modules/texts/cutter.php
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

// necessary functions for cutting texts based on space breaks

// collapse_spaces
function collapse_spaces( &$textstr )
{
    return preg_replace( "/\s+/", " ", $textstr );
}

// clean_string
function clean_string( &$str )
{
    return preg_replace( "/[^a-zA-Z0-9 ]/", "", strtolower( $str ) );
}

// split_string
//
function split_string( &$textstr )
{
    return preg_split( "/ /", $textstr );
}

// split_on_spaces
// 
function split_on_spaces( &$textarray, $spaces )
{
    $chunkarray = null;//new Array();

    trigger_error( "=======" . memory_get_usage() );
    $spaces[] = sizeof( $textarray ) - 1;

    $start = 0;
    foreach ( $spaces as $s => $sp )
    {
        $end = $sp;
        $length = $s ? $end + 1 - $start : $end + 1;

        $chunkarray[$end] = array_slice( $textarray, $start, $length, true );

        $start = $end + 1;
    }

    return $chunkarray;
}

// remove_junk
function remove_junk( $chunks )
{
    foreach( $chunks as $k => $chunk )
        $chunks[$k] = array_map( 'remove_punct', $chunk ); 

    return $chunks;
}

function remove_punct( $str )
{
    $str = strtolower( $str );
    $str = preg_replace( "/[^A-Za-z0-9_]/", "", $str );

    return $str;
}

// count_words()
// makes an array of words a hash of counts
// indexed by word name
function count_words( &$textarray ) 
{
    $wordcount = array();
    // iterate through the array of words and up the count
    foreach ( $textarray as $word ) 
    {
        if ( $word == "" )
            continue;
        $wordcount[ "$word" ] = isset( $wordcount[ "$word" ] ) ? 
                $wordcount[ "$word" ] + 1 : 1;
    }

    return $wordcount;

}


// get_hapax()
// takes a hash table and counts the number of words
// with count == 1
function get_hapax( &$hash ) {

    $hapax = 0;
    foreach ( $hash as $word ) {

        if ( $word == 1 )
            $hapax++;

    }

    return $hapax;

}

// get_total()
// takes a hash table and counts up the total number of word counts
function get_total( &$hash ) {

    $total = 0;
    foreach ( $hash as $word ) {

        $total += $word;

    }

    return $total;

}

// get_unique()
// returns the number of keys
function get_unique( &$hash ) {

    return count( $hash );

}

// get_counts
// quickly get all the counts and return an aarray
// PARAMS:
//	hash:aaray - hash table of word counts
// RETURN:
//	aarray of counts
function get_counts( &$hash )
{
    $counts['unique'] = get_unique( $hash );
    $counts['total'] = get_total( $hash );
    $counts['hapax'] = get_hapax( $hash );

    return $counts;
}

// hash_sort()
// sorts hash based on count or word
// ARGS: 
//	$hash: reference to the hash table indexed by word
//	$sort: the sort type, 'c' for count, 'w' for word
function hash_sort( &$hash, $sort ) {

    if ( $sort == 'c' ) {

        // grab array for word and counts
        $word = array_keys( $hash );
        $count = array_values( $hash );
        
        // sort the counts, then words in $hash
        array_multisort( $count, SORT_DESC, $word, SORT_ASC, $hash );

    }
    else {

        // sort by key, ie. the word name
        ksort( $hash );

    }

}












// ================= OLD JUNK =======================================

	// split_and_strip()
	// reads a file, chops it up word by word and removes 
	// words that are not really words, like spaces
	// ARGS:
	//	$file: a path to the file to read in
	// RETURN: an array of words
	function split_and_strip( $file ) {

		// open file, gobble in the text and close
		$FH = fopen( $file, "r" );
		$textstr = fread( $FH, filesize( $file ) );
		fclose( $FH );

		// replace any number of spaces with a single space,
		// trailing and leading spaces with nothing
		$textstr = preg_replace( "/\s+/", " ", $textstr );
		$textstr = preg_replace( "/\s+$/", "", $textstr );
		$textstr = preg_replace( "/^\s+/", "", $textstr );

		// explode into array on spaces
		$textarray = explode( " ", $textstr );

		// create an array of only words		
		//$retarray = array();
		//foreach ( $textarray as $word )
		//	if ( is_word( $word ) )
		//		$retarray[] = $word;

		// for now, just return the text array
		return $textarray;

	}

	function is_word( $str ) {

		$word = true;

		//if ( preg_match( "/^[^[:alpha:]]$/", $str ) == 0 ) 
		//	$word = false;

		return $word;

	}

	// cutter()
	// cuts the input array into specified chunks
	// ARGS:
	//	$textarray: array containing the words of the text in
	//		individual slots
	// 	$chunksize: the size in words of a chunk
	//	$shiftsize: the number of words to shift
	// 	$lastprop: the proportion of a chunk the last chunk can be
	// RETURN: an array of chunks, where a chunk is a subset of 
	//		the input array indexed by the first and last word number
	//		in the chunk, each chunk will not necessarily be indexed
	//		by word number, but will be textual order
	function cutter( $textarray, $chunksize, $shiftsize, $lastprop ) {

		// set initial chunk
		$start = 0;
		$end = $chunksize;

		// grab the next chunk and add it in if the bounds were not exceeded
		while ( $chunk = array_subset( $textarray, $start, $end ) ) {

			// create the index of the $start..$end, most of the time,
			// if the subset came back having stopped at MAX, we'll
			// need the last key in the $chunk array
			$index = "$start.." . array_pop( array_keys( $chunk ) );
			$chunkarray[$index] = $chunk;

			// get new bounds
			$start += $shiftsize;
			$end += $shiftsize;

		}

		// determine the min size of the last chunk
		// err on the side of too much; better to have a chunk of
		// 4 in chunksize 3 than a chunk of 1
		$lastsize = ceil( $chunksize * $lastprop );

		// find the last chunk
		$lastchunk = end( $chunkarray );

		// the the size of the last chunk is smaller than allowed, 
		//  merge and reindex
		if ( count( $lastchunk ) < $lastsize ) {
			
			// discard the offending chunk
			array_pop( $chunkarray );

			// get the very final index of the last chunk, last word
			$indexend = array_pop( array_keys( $lastchunk ) );

			// remove and capture the chunk to append to
			$secondlast = array_pop( $chunkarray );

			// get the first index of that array and prepend 
			//  that to create the new index to $chunkarray
			$index = array_shift( array_keys( $secondlast ) ) . "..$indexend";
		
			// merge the two chunks in order, and stick it on
			$newchunk = array_merge( $secondlast, $lastchunk );
			$chunkarray[$index] = $newchunk;

		}

		return $chunkarray;

	}

	// array_subset()
	// dumb PHP doesn't have this function, so this is a hacky
	// version that doesn't think about non-numeric keys
	// ARGS:
	//	$array: array indexed by numbers
	//	$start: index of first element in subset
	//	$end: index of first element not in array
	// RETURN: an array [$start,$end) from $array with
	//		the same indicies 
	function array_subset( $array, $start, $end ) {

		$MAX = count( $array );
		//$subset = array();
		for ( $i = $start; $i < $end; $i++ ) {

			if ( $i >= $MAX )
				break;
			$subset[$i] = $array[$i];

		}

		if ( count( $subset ) == 0 )
			return null;
		else
			return $subset;

	}

	// write_texts()
	// take an array of arrays containing chunks of text 
	// and writes them out to appropriate files
	// ARGS:
	//	$chunks: the array of chunks of text indexed by low..hi
	//	$textdir: directory for the chunked text;
	//	$textname: the name of the text without the extension
	function write_texts( $chunks, $textdir, $textname ) {

		echo "Writing $textname with " . count( $chunks ) . " chunks.<br/>";

		// get the biggest number to correctly pad the output name
		$span = array_pop( array_keys( $chunks ) );
		list( $lo, $highest ) = explode( "..", $span );
		$hilen = strlen( $highest );
		$pad = "%0{$hilen}s";

		foreach( $chunks as $span => $text ) {

			// get the lo and high word of the name
			list( $lo, $hi ) = explode( "..", $span );
			$lo++; $hi++;	// add one for humanists
			$lopad = sprintf( $pad, $lo );
			$hipad = sprintf( $pad, $hi );

			$chunkname = "{$textname}_$hipad.txt";
			$chunkfile = "$textdir$chunkname";
			echo "Writing $chunkname<br/>";
			$chunk = implode( " ", $chunks[$span] );
			$FH = fopen( $chunkfile, "w" );
			fwrite( $FH, $chunk );
			fclose( $FH );

		}

	}




?>
