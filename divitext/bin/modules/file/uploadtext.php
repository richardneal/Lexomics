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

$HOME = "../..";

require_once( "$HOME/includes/nav.php" );
require_once( $MODLOGIN );
require_once( $MODTEXTS );

session_start();
login();

//require_once( $MODDIR );

$errors = null;

// if the file is too big, can't finish the rest of the script
if ( $_FILES['file']['size'] == 0 )
{
    $errors[] = "File is too big.";
    trigger_error( "File too big." );
}
else
{

    $newtext = new Text();
    // ensure id doe not exist
    $id = $oid = Text::id_from_name( $_POST['name'] );
    //error_log( "=======================><><><><" . $id );
    $i = 1;
    while ( array_key_exists( $id, $_SESSION['user']['texts'] ) )
    {
        $id = $oid . "_$i";
        $i++;
    }

    $errors = $newtext->set_data( $_POST, $_FILES['file'], 
        $_SESSION['user']['dir'], $id );
}

$message = Array();

if ( !$errors )
{
    $_SESSION['user']['texts'][$id] = $newtext;

	$message['success'] = true;
	$message['textpath'] = "texts/" . $id;
}
else
{
	$message['success'] = false;
	$message['errors']  = $errors;
}

echo json_encode( $message );

?>
