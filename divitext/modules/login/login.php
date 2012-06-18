<?php // /modules/login/login.php
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

// Login Module
// This module provides necessary information about "users."
// A user is an instance of a user session on the server. This is defined
// by the time that the user spends on the site. Users are assigned a user
// id that serves as their directory in the data folder. 
//
// All relavent user information is stored in the $_SESSION variable.
// :: 'user'.'id'
//      This is the assigned name of the user, a uniqid() prepended with 
//       'divi_'.
// :: 'user'.'dir'
//      The folder that contains the user's data.
// :: 'user'.'texts'
//      An array of Text objects.

// ensure that the directory and texts modules are present
require_once( $MODDIR );
//require_once( $MODTEXTS );

// == Login and Logout Functions ====================================
// functions directly called by user scripts (public facing)

// login
// called to set the user and move into user dir
function login()
{
    if ( !isset( $_SESSION['user'] ) )
    {
        if ( !new_session() )
            trigger_error( "User could not be created." );
    }

    // now the user dir should exist, move to it
    if ( !chdir( $_SESSION['user']['dir'] ) )
        trigger_error( "User directory ({$_SESSION["user"]["dir"]}) unavailable." );
}

// logout
// called to log the user out
function logout()
{

}

// == Hidden Login Functions ========================================
// functions called only from within this script

// new_session
// creates new user session and variables
// POST
//  - $_SESSION['user'] exists and contains keys 'id', 'dir', and 'texts'
//  - $_SESSION['user']['dir'] is created
function new_session()
{
    $_SESSION['user'] = Array();
    $_SESSION['user']['id'] = uniqid( 'divi_' );
    $_SESSION['user']['dir'] = DIVI_DIR . "/" . $_SESSION['user']['id'];
    $_SESSION['user']['texts'] = Array();
    //return true;
    return mkdir( $_SESSION['user']['dir'], 0700, true );
}






?>
