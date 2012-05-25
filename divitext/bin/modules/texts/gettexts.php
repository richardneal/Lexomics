<?php // /modules/texts/gettexts.php
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

// require some important scripts
require_once( "$HOME/includes/nav.php" );
require_once( $MODLOGIN );
require_once( $MODTEXTS );

// start session
session_start();
login();

$uid = $_SESSION['user']['id'];
$utexts = $_SESSION['user']['texts'];

// some dope info
//$t = new Text();
//$t->SET_name( "Dope Text by Shakebeer" );
//$t->SET_id( "dopetextbyshakebeer" );
//$t->SET_folder( "dopetextbyshakebeer" );
//$t->add_chunkset( '100 100 .5' );
//$_SESSION['user']['texts'] = Array();
//$_SESSION['user']['texts'][] = $t;

$texts = Array();

$i = 0;
foreach ( $utexts as $k => $text )
{
    if ( !$text )
        continue;

	$id = $k;

	$texts[$i]['text'] = $text->GET_name();
	$texts[$i]['tid'] = $k;
	$texts[$i]['size'] = $text->GET_size();
    $texts[$i]['id'] = "texts/$k";
    $texts[$i]['type'] = "text";
    $texts[$i]['icon'] = "icons/book.png";
	$tcs = $text->GET_chunksets();

	if ( $tcs )
	{
        $j = 0;
		foreach ( $tcs as $kk => $cs )
        {
            if ( !$cs )
                continue;
            $texts[$i]['children'][$j]['text'] = $cs->GET_name();
            $texts[$i]['children'][$j]['tid'] = $cs->GET_id();
            $texts[$i]['children'][$j]['spaces'] = $cs->GET_spaces();
			$texts[$i]['children'][$j]['leaf'] = true;
			$texts[$i]['children'][$j]['icon'] = "icons/page_white_stack.png";
			$texts[$i]['children'][$j]['type'] = "chunkset";
            
            $j++;
		}
	}
	else
	{
		$texts[$i]['leaf'] = true;
	}

	$i++;
}

echo json_encode( $texts );

?>
