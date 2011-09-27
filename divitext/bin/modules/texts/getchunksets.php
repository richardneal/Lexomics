<?php // /modules/texts/getchunksets.php
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

$chunksets = null;

$i = 0;
foreach ( $utexts as $k => $text )
{
    if ( !$text )
        continue;

    $tid = $k;
    $tcs = $text->GET_chunksets();

    if ( $tcs )
    {
        $j = 0;
        foreach ( $tcs as $kk => $cs )
        {
            if ( !$cs )
                continue;
            $chunksets[$i]['cs']     = $cs->GET_name();
            $chunksets[$i]['csid']   = $cs->GET_id();
            $chunksets[$i]['text']   = $text->GET_name();
            $chunksets[$i]['textid'] = $tid;
            $chunksets[$i]['size']   = count( $cs->GET_spaces() ) + 1;
            $i++;
        }
    }
}

echo json_encode( $chunksets );

?>
