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

// start output buffering
//ob_start();

// start session
session_start();


// directory module for path and file handling for the user
//require_once( $MODDIR );

// require the login module to log the user in
require_once( $MODLOGIN );

login();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>

<head>
    <title>diviText</title>
    <!-- EXTJS INCLUDES -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="<?=$EXTCSS?>" />
    <link rel="stylesheet" type="text/css" href="includes/divitext.css" />
    <script type="text/javascript" src="<?=$EXTALL?>"></script>
	<script type="text/javascript" src="<?=$EXTEXT?>"></script>
	<script type="text/javascript" src="<?=$EXTFUF?>"></script>
    <link rel="stylesheet" type="text/css" href="<?=$EXTFUFCSS?>" />
	
    <!-- END EXT INCLUDES -->
    <!-- CUSTOM INCLUDES -->
    <script type="text/javascript" src="<?=$MODCUTTERPANEL?>/cutterpanel.js"></script>
    <link rel="stylesheet" type="text/css" href="<?=$MODCUTTERPANEL?>/cutterpanel.css"/>
    <!-- END CUSTOM INCLUDES -->
</head>

<body>
    <div id="header"> &nbsp;<b>diviText <?=$DTVERSION?> </b></div>
	<div id="content">

<!-- END OF HEADER -->
