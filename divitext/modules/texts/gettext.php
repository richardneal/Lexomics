<?php // /modules/texts/gettext.php
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

// Retrives a single text sent as the 'textid' parameter in POST
// from the user's session

$HOME = "../..";

require_once( "$HOME/includes/nav.php" );
require_once( $MODLOGIN );
require_once( $MODTEXTS );

session_start();
login();

$errors = false;

$tid = $_POST['textid'];


if ( !$tid && !$errors )
{
    trigger_error( "No text id in POST." );
    $errors[] = "No text id.";
}

$text = $_SESSION['user']['texts'][$tid];

if ( !$text && !$errors )
{
    trigger_error( "No text indexed by $tid." );
    $errors[] = "No such text with that id.";
}

$textstr = $text->get_text();

if ( !$textstr && !$errors )
{
    trigger_error( "Text could not be opened for reading." );
    $errors[] = "Text could not be opened.";
}

$message = null;

if ( $errors )
{
    $message['success'] = false;
    $message['errors']  = $errors;
}
else
{
    $message['success'] = true;
    $message['text']    = utf8_decode( $textstr );
    $message['name']    = $text->GET_name();
}

echo json_encode( $message );

?>
