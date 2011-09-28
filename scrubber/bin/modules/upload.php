<?php
// /modules/upload.php
// Upload module for handling all file uploads from texts, lemma lists ...

class Upload
{
    function __construct( $files )
    {
        $this->file = $files;
    }

    public function filetext( $file = 0 )
    {
        
    }

    private function _readfile( $file = 0 )
    {
        //$FH = fopen( 
    }

    // data

    private $files = null;
}

