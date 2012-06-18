<?php // /download.php
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

// /download.php
// downloads a zip of all of the user's texts and chunksets
// RETURN
//  - nothing
// DOWNLOAD
//  - zip of all of user's texts and chunksets in user.texts.zip

$HOME = ".";

require_once( "$HOME/includes/nav.php" );

require_once( $MODLOGIN );
require_once( $MODDOWNLOAD );

session_start();
login();

$FILE = "texts.zip";
$NEWDIR = "user.texts";

`mkdir $NEWDIR`;
`mv * $NEWDIR`;
`zip -r $FILE $NEWDIR`;
`mv $NEWDIR/* .`;
downloadFile( "$FILE" );
`rm -r $NEWDIR $FILE`;




?>
