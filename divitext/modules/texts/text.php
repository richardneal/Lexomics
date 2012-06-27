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

require_once( $MODCHUNKSET );

class Text
{
// public
    
    // constructors
    public function __construct()
    {

    }

    // simple getters
    public function GET_name()      { return $this->name; }
    public function GET_folder()    { return $this->folder; }
    public function GET_orig()      { return $this->orig; }
    public function GET_id()        { return $this->id; }
    public function GET_chunksets() { return $this->chunksets; }
    public function GET_size()      { return $this->size; }

    public function GET_metadata()  { return $this->metadata; }

    // better getters
    public function get_text()
    {
        $FH = fopen( $this->orig, 'r' );
        $text = utf8_encode( fread( $FH, $this->size ) );
        fclose( $FH );

        return $text;
    }

    // simple setters
    public function SET_name( $_n )   { $this->name   = $_n; }
    public function SET_folder( $_f ) { $this->folder = $_f; }
    public function SET_id( $_o )     { $this->id     = $_o; }
    public function SET_size( $_s )   { $this->size   = $_s; }
    //public function SET_( $_ ) { $this-> = $_; }

    // better setters
    public function set_data( $post, $file, $dir, $id )
    {
        $errors = false;
        // [file] => Array
        //(
        //    [name] => filename.txt
        //    [type] => text/plain
        //    [tmp_name] => /tmp/phpWf60WP
        //    [error] => 0
        //    [size] => 87403
        //)

        if ( !preg_match(  "/^text/", $file['type'] ) )
        {
            trigger_error( "Invalid filetype {$file['type']}. File is " .
                $file['size'] . " bytes in size." );
            $errors[] = "Invalid filetype {$file['type']}. File is " .
                $file['size'] . " bytes in size.";
            return $errors;
        }

        $this->name   = $post['name'];
        $this->size   = $file['size'];
        $this->type   = $file['type'];

        
        //$this->id     = self::id_from_name( $post['name'] );
        $this->id = $id;
        $this->folder = $dir . "/" . $this->id;
        $this->orig   = $this->folder . "/" .  $this->id . ".txt";


        //richard added this for metadata
        $this->metadata = "";
        $errors[] = xattr_get($file['tmp_name'], 'Scrubber Options');
        $filearray = file($file['tmp_name']);
        if (strpos(end($filearray), "Scrubber Options:") !== false) {
            $this->metadata = array_pop($filearray);
            $fileopen = fopen($file['tmp_name'], 'w');
            fwrite($fileopen, implode('', $filearray));
            fclose($fileopen);
        }

        
        if ( !$errors && !mkdir( $this->folder, 0700 ) )
        {
            trigger_error( "Could not create text directory '{$this->folder}'." );
            $errors[] = "Text dir failure.";
        }

        if ( !$errors && !move_uploaded_file( $file['tmp_name'], $this->orig ) )
        {
            trigger_error( "Could not move uploaded file to desination." );
            $errors[] = "Could not move file.";
        }

        if ( $errors )
        {
            rrmdir( $this->folder );
        }
        else
        {
            $this->clean_text();
        }

        return $errors;
    }

    // remove text
    public function remove_text()
    {
        //return rmdir( $this->folder );
        rrmdir( $this->folder );
        return true;
    }

    // chunkset functions
    public function add_chunkset( $_cs ) 
    {
       $this->chunksets[$_cs->GET_id()] = $_cs;
    }

    public function chunk( &$spaces, &$csname )
    {
        $errors = null;

        $csido = self::id_from_name( $csname );
        $csid  = $csido;
        $i = 1;
        while ( array_key_exists( $csid, $this->chunksets ) )
        {
            $csid = $csido . "_$i";
            error_log( $csid );
            $i++;
        }

        $textstr = $this->get_text();
        //$textstr = addslashes( $textstr );

        if ( !$textstr )
        {
            $errors[] = "Text could not be retrived for chunking.";
            trigger_error( "Text could not be retrived for chunking." );
            return $errors;
        }

        $cs = new Chunkset();
        $cs->SET_name( $csname );
        $cs->SET_id( $csid );
        $cs->SET_folder( $this->folder . "/" . $csid );
        $cs->SET_textname( $this->name );
        $cs->SET_textid( $this->id );

    trigger_error( "==" . memory_get_usage() );
        $out = $cs->chunk( $textstr, $spaces );

        if ( $errors || $out )
        {
            $errors = $out;
            return $errors;
        }

        $this->add_chunkset( $cs );

        return $errors;
    }

    public function remove_chunkset( $_csid )
    {
        error_log( "================ $_csid" );
        $cs = $this->chunksets[$_csid];
        $cs->remove_chunkset();
        $this->chunksets[$_csid] = null;
    }

    // text processing functions

    public function clean_text()
    {
        $text = "";
        $FH = fopen( $this->orig, 'r' );
        while( !feof( $FH ) )
            $text .= trim( ( fgets( $FH ) ) ) . "\n";
        fclose( $FH );

        // shrink spaces to largest single space
        //$text = ltrim( rtrim( $text ) );
        $text = preg_replace( "[\f\v]", "", $text );
        $text = preg_replace( "/\s*\n\n\s*/", "\n\n", $text );
        $text = preg_replace( "/[\t ]*\t[\t ]*/", "\t", $text );
        $text = preg_replace( "/[ ]+/", " ", $text );

        $FH = fopen( $this->orig, "w+" );
        $len = fwrite( $FH, $text );
        fclose( $FH );

        $this->size = $len;
    }

    // static text id functions
    static public function id_from_name( $name )
    {
        $cname = "$name";//strtolower( $name );
        $cname = preg_replace( "/\.[a-zA-Z]{2,4}$/", "", $cname );
        $cname = preg_replace( "/[^0-9A-Za-z_]/", "", $cname );
        $id = "$cname";
        return $id;
    }

// private

    private $name      = "";
    private $folder    = "";
    private $id        = "";
    private $orig      = "";
    private $chunksets = Array();
    private $size      = 0;
    private $type      = "";
}

function rrmdir( $dir )
{
    if ( is_dir( $dir ) ) 
    { 
        $objects = scandir( $dir ); 
        foreach ( $objects as $object ) 
        { 
            if ( $object != "." && $object != ".." ) 
            { 
                if ( filetype( $dir."/".$object ) == "dir" ) 
                    rrmdir( $dir."/".$object ); 
                else 
                    unlink( $dir."/".$object ); 
            } 
        } 
        reset( $objects ); 
        rmdir( $dir ); 
    } 
}
?>
