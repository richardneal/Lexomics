<?php
	// modules/counter.php
	// generic counting and uploading tools

	// file_id
	function file_id( $uid, $name, $text )
	{
		$hashme = "$uid$name$text";
		$id = sha1( $hashme );

		return $id;
	}

	// open_file
	// opens a file and returns its contents
	function open_file( $file )
	{
		$FH = fopen( $file, "r" );
		$textstr = fread( $FH, filesize( $file ) );
		fclose( $FH );

		return $textstr;
	}

	// str_to_array
	// simply explodes a string on spaces after removing the extraneous spaces
	//  in addition to anything in the scrubber
	function str_to_array( $str, $scrubber = "" )
	{
		$str = scrub_text( $str, $scrubber );
		$textarray = explode( " ", $str );
	
		return $textarray;
	}

	// add_txt
	// turns raw txt text into canonical form and adds it and text to db
	// PARAMS:
	//	filestr:string REF - string containing raw txt file data
	//	name:string - string user input as name of text
	//	uid:int - user_id
	//	[textassoc:int - text association for chunks, none by default
	//	[start:int - start word of text/chunk, defaults to 0
	//	[end:int - end word of text/chunk, when not specified, defaults 
	//		to total-1 ]]]
	// RETURN:
	//	json data about the text, or failure
	function add_txt( $file, $name, $uid, $textassoc = null, 
		$start = 0, $end = 0 )
	{
		// open the file
		$filestr = open_file( $file );

// =========== GENERALIZE THIS PLZ =======================================
		// arrayify the text into canonical array form
		$acanon = str_to_array( $filestr, "[^a-zA-Z0-9_\s]" );

		// get the hash of the text
		$hash = count_words( $acanon );

		// get counts of uniques, hapax, ...
		$counts = get_counts( $hash );

		// get a unique text hash to be used as location data
		$thash = file_id( $uid, $name, $filestr );

		// must build aarray to send to db add function
		$data['name'] = $name;
		$data['words'] = $hash;
		$data['user'] = $uid;
		$data['counts'] = $counts;
		$data['start'] = $start;
		$data['end'] = $end ? $end : $counts['total'] - 1;
		$data['assoc'] = $textassoc;
		$data['canon'] = $filestr;
		$data['hash'] = $thash;

		// call the add function in MODQUERY and retrive the text_id
		$tid = add_text_to_db( $data );

		return $tid;
	}

	// these are useful, in the sense that they are cool ======================
	
	// txt_to_array()
	// opens a txt file and converts to an array
	function txt_to_array( $file, $scrubber = "[^a-zA-Z0-9_\s]" ) {

		// open file, gobble in the text and close
		$FH = fopen( $file, "r" );
		$textstr = fread( $FH, filesize( $file ) );
		fclose( $FH );

		$textstr = scrub_text( $textstr, $scrubber );

		// explode into array on spaces
		$textarray = explode( " ", $textstr );

		return $textarray;

	}

	// array_to_txt()
	// write to a txt from an array of words
	// written in canonical form
	function array_to_txt( $array, $file ) {

		$text = implode( $array, " " );
		$FH = fopen( $file, "w" );
		fwrite( $FH, $text );
		fclose( $FH );

	}

	// count_words()
	// makes an array of words a hash of counts
	// indexed by word name
	function count_words( $textarray ) {

		$wordcount = array();
		// iterate through the array of words and up the count
		foreach ( $textarray as $word ) {

			//echo $word . " " . var_dump( isset( $wordcount["$word"] ) ) . "\n";
			$wordcount[ "$word" ] 
				= isset( $wordcount[ "$word" ] ) ? 
					$wordcount[ "$word" ] + 1 
					: 1;
			//$wordcount[ "$word" ]++;

		}

		return $wordcount;

	}

	// scrub_text()
	// clean text of punctuation and --capitals-- and extra spaces
	function scrub_text( $textstr, $scrubber ) {

		// remove things in $scrubber
		$scrubber = "/$scrubber/";
		$textstr = preg_replace( $scrubber, "", $textstr );

		// replace any number of spaces with a single space,
		// trailing and leading spaces with nothing
		$textstr = preg_replace( "/\s+/", " ", $textstr );
		$textstr = preg_replace( "/\s+$/", "", $textstr );
		$textstr = preg_replace( "/^\s+/", "", $textstr );

		// lowercase everything
		//$textstr = mb_strtolower( $textstr );

		return $textstr;

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

	// NOT NEEDED HERE, yet?
	// print_hash_table()
	// print just the hash table
	// returns a CSV string of the table
	function print_hash_table( &$hash, $title = "" ) {

		$total = get_total( $hash );
		$unique = get_unique( $hash );
		$hapax = get_hapax( $hash );

		// start the csv
		$csv = "$title,$total,$unique,$hapax\n";
		$csv = "{$csv}RANK,WORD,COUNT,FREQUENCY\n";
		// print table header
?>
<div class="results">
	<table class="resultstable">
		<tr>
			<th>Rank</th>
			<th>Word</th>
			<th>Count</th>
			<th>Frequency</th>
		</tr>
<?
		$color = 1;
		$rank = 0;
		$allrank = 1;
		$prevcount;

		// iterate through the list of word printing them out
		foreach ( $hash as $word => $count ) {

			$prop = round( ($count / $total), 10 );
			
			if ( $count != $prevcount ) 
				$rank = $allrank;
			$prevcount = $count;
			$allrank++;

			$rowclass = ( $color % 2 ) ? "oddrow" : "evenrow";
			$color++;
?>
		<tr class="<? echo $rowclass ?>">
			<td><? echo $rank ?></td>
			<td class="intext"><? echo $word ?></td>
			<td><? echo $count ?></td>
			<td><? echo $prop ?></td>
		</tr>
<?

			// update csv
			$csv = "$csv$rank,$word,$count,$prop\n";

		}

?>
	</table>
</div>
<?

		// return the csv
		return $csv;

	}

?>
