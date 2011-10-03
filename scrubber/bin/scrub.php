<?php

/**
 * @file
 * A script that is called by AJAX functionality from within EXT.
 */

/**
 * Provides and abstraction to the strip_tags function with some additional
 * case switching for the type of file that is being parsed.
 * 
 * @param $string 
 *	A string with tags in it (or not) to be parsed and have the tags stripped.
 * 
 * @param $type
 *	The type of file from which the tags are being stripped.
 *
 * @return
 *	A string of scrubbed text. Generally returned via AJAX instead
 *	of a direct call.
 */

function scrub_text($string, $type = 'default') {
	switch ($type) {
		case 'default':
			strip_tags($string);
			break;
		case 'xml':
			strip_tags($string);
			break;
		case 'sgml':
			strip_tags($string);
			break;
	}
}

?>
