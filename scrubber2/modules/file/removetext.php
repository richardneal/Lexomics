<? // /modules/file/removetext.php
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

$errors = null;

$tid = $_POST['textid'];

if ( !$tid )
{
    $errors[] = "No text id sent.";
    trigger_error( "No text id sent in POST." );
}

$text = $_SESSION['user']['texts'][$tid];

if ( !$text )
{
    $errors[] = "Text not found.";
    trigger_error( "No text with id: $tid." );
}

$okay = $text->remove_text();

if ( !$okay )
{
    $errors[] = "Unable to remove text.";
    trigger_error( "Could not delete text." );
}
else
{
    $_SESSION['user']['texts'][$tid] = null;
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
}

echo json_encode( $message );

?>
