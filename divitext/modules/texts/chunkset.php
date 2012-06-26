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

// /modules/texts/chunkset.php
// the class definition of a chunkset.
// provides routines for chunking a string and making csv files into hashes of 
// words

require_once( $MODTEXTS );
require_once( $MODCUTTER );
require_once( $MODDIR );


class Chunkset
{
    // public
    
    // constructor
    public function __construct()
    {

    }

    // simple GETters
    public function GET_name()   { return $this->name; }
    public function GET_folder() { return $this->folder; }
    public function GET_id()     { return $this->id; }
    public function GET_spaces() { return $this->spaces; }

    // better getters

    // simple setters
    public function SET_name( $_n )   { $this->name   = $_n; }
    public function SET_folder( $_f ) { $this->folder = $_f; }
    public function SET_id( $_o )     { $this->id     = $_o; }
    public function SET_textname( $_n )   { $this->textname   = $_n; }
    public function SET_textid( $_o )     { $this->textid     = $_o; }


    // chunkers
    
    // generates the structure of the chunkset directory and invokes chunker 
    // routines
    public function chunk( &$text, $spaces )
    {
        $this->spaces = $spaces;
        $cwd = getcwd();
        mkdir( $this->folder, 0700 );
        chdir( $this->folder );
        
        $folders = array( "txt", "tsv" );

        foreach ( $folders as $f )
        {
            if ( !mkdir( "./$f", 0700, true ) )
                $errors[] = "Could not make folder '$f'.";
        }

        $out = $this->chunker( $text );

        chdir( $cwd );
        if ( $out )
            return true;
        else
            return null;
    }

    // turn a string of text into chunk files based on spaces
    private function chunker( &$text, $style = "" )
    {
        $errors = null;
        $text = collapse_spaces( $text );
        $textarr = split_string( $text );
        $chunksarr = split_on_spaces( $textarr, $this->spaces );

        if ( $style == "clean" )
            $chunksarr = remove_junk( $chunksarr );

        $chunkhashes = null;
        foreach( $chunksarr as $end => &$chunkarr )
            $chunkhashes[$end] = count_words( $chunkarr );

        if ( !$chunkhashes )
        {
            $errors[] = "Could not hash chunks. Huh.";
            trigger_error( "Could not hash chunks. Huh." );
            return $errors;
        }

        // if cleaned style, remove all bad words and lc

        $max = array_pop( array_keys( $textarr ) ) + 1;
        $maxlen = strlen( "$max" );
        $pad = "%0{$maxlen}s";
        foreach ( $chunksarr as $end => $chunkarr )
        {
            $endpad = $end + 1;
            $endpad = sprintf( $pad, $endpad );

            $out  = $this->write_txt( $chunkarr, $endpad, $style );
            $out2 = $this->write_csv( $chunkhashes[$end], $endpad, $style );

            if ( $out || $out2 )
                $errors = array_merge( $out, $out2, $errors );
        }

        if ( $errors )
        {
            rrmdir( $this->folder );
            trigger_error( "Something in the chunking process went wrong." );
        }

        return $errors;

    }

    // write the raw text of the chunk
    private function write_txt( &$text, $end, $style = null )
    {
        //$file = $this->folder . "/$style/txt/" . $this->id . "_$end.txt";
        $file = $this->folder . "/txt/" . $this->id . "_$end.txt";
        $FH = fopen( $file, 'w' );

        if ( !$FH )
            return "Could not open file '$file' in mode 'c+'.";

        if ( !fwrite( $FH, utf8_decode( implode( ' ', $text ) ) ) )
            return "Write to '$file' failed.";

        fclose( $FH );

        return null;
    }

    // write the csv/tsv file of a chunk
    private function write_csv( &$hash, $end, $style = null )
    {
        $DELIM = "\t";
        //$file = $this->folder . "/$style/csv/" . $this->id . "_$end.csv";
        $file = $this->folder . "/tsv/" . $this->id . "_$end.tsv";

        $total = get_total( $hash );
        $unique = get_unique( $hash );
        $hapax = get_hapax( $hash );

        hash_sort( $hash, 'c' );

        $csvarr = array();

        // start the csv
        $csvarr[] = array( $this->id . "_$end.txt",$total,$unique,$hapax );
        $csvarr[] = array( "RANK","WORD","COUNT","RELATIVE FREQUENCY" );
        $rank = 0;
        $allrank = 1;
        $prevcount = null;

        // iterate through the list of word printing them out
        foreach ( $hash as $word => $count ) {

            $prop = round( ($count / $total), 10 );

            if ( $count != $prevcount ) 
                $rank = $allrank;
            $prevcount = $count;
            $allrank++;

            // update csv
            $csvarr[] = array( $rank, utf8_decode( $word ), $count, $prop );

        }

		// write out file
		$FH = fopen( $file, 'w' );
        if ( !$FH )
            return "Could not open file '$file' in mode 'c+'.";
        foreach ( $csvarr as $line )
        {
            if ( !fputcsv( $FH, $line, "$DELIM" ) )
                return "Could not write " . implode( ",", $line ) . 
                    " to file '$file'.";
        }
        fclose( $FH );

        return null;
    }

    // remove_chunkset
    public function remove_chunkset()
    {
        rrmdir( $this->folder );
	}

	// get_hash
	// gets a hash of wordcounts for each chunk in chunkset
	public function get_hash()
	{
		$OLDWD = getcwd();

		chdir( $this->folder . "/tsv/" );
		$allcsv = glob( "*.tsv" );	// sorted
	
		// list of all end words to index hash
		$allnums = array_map( "parse_endword", $allcsv );
		
		$hashes = null;
		foreach ( $allnums as $i => $n )
		{
			$chunkname = $this->id . "_$n";
			$hashes["$chunkname"] = self::csv_to_hash( $allcsv[$i] );
		}

		chdir( $OLDWD );

		return $hashes;
	}

    // return a hash of counts indexed by words for a single csv/tsv file
	static private function csv_to_hash( $file )
	{
        $DELIM = "\t";
		$FH = fopen( $file, 'r' );
		if ( !$FH )
		{
			trigger_error( "Could not open file '$file' in mode 'r'." );
			return null;
		}
		fgetcsv( $FH );
		fgetcsv( $FH );

		$hash = null;
		while ( $line = fgets( $FH ) )
        {
            $line = explode( "$DELIM", utf8_encode( $line ) );
            list( $rank, $word, $count, $relfreq ) = $line;
            $hash[ "$word" ] = $count;
		}
		fclose( $FH );

		return $hash;
	}

    // private
    private $name     = "";
    private $folder   = "";
    private $id       = "";
    private $textname = "";
    private $textid   = "";
};

function parse_endword( $str )
{
    $nums = preg_match( '/_(\d+)\.tsv/', $str, $match );
    if ( $nums )
        return $match[1];
    else
        return null;
}



?>
