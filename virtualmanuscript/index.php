
<?php // virtual manuscript

	$HOME = "../../";
	$TITLE = "Virtual Manuscript";
	$BANNER = "{$HOME}images/bannernew.png";
	require_once( "{$HOME}includes/links.php" );
	include( $HEADER );

?>

<?php // always build the menu
	$menujs = "../menu/menubvmjs.js";
	include( "../menu/menu.php" );
?>

<?php // determine mode of the page
	
	// if the URL GET params are not all set, stay in intro mode (print directions)
	// if all the GET params are set, go to results mode
	$mode = "intro";
	if ( ISSET( $_GET['file'] ) and ISSET( $_GET['sort'] ) and
		 ISSET( $_GET['tags'] ) and ISSET( $_GET['consolidation'] ) and
		 ISSET( $_GET['top'] ) && $_GET['file'] != "" ) 
		$mode = "results";
		
	//echo $mode;
?>

<?php

	if ( $mode == "intro" )
		include( "directions.html" );
	else {

		// prepare the function to print results
		include( "../modules/manuscript.php" );
		$filelist = explode( " ", $_GET['file'] );

		// create a hopefully unique userid based on chosen files and time
		$userid = substr( sha1( uniqid().$_GET['file'] ), 16 );
		$userdir = "built_manu/$userid/";
		mkdir( $userdir, 0766 );
	
		// iterate through each file, make a hash for it and 
		// stick it in the array of hashes
		foreach ( $filelist as $fileshort) {

			$file = makeDIR( $fileshort );
			$hashes[] = get_hash( $file );
			copy( $file, "$userdir$fileshort.csv" );
		
		}

		$hash = merge_hashes( $hashes );
		$hash = sort_hash( $hash );
		
		// get CSV text and write it to the user file
		$csvstr = print_hash_to_csv( $hash );
		$USERFH = fopen( "{$userdir}selection_STATS.csv", "w" );
		fwrite( $USERFH, $csvstr );
		fclose( $USERFH );

		// zip the directory and remove it
		$link = "built_manu_zips/$userid.zip";
		exec( "zip -r $link $userdir" );
		exec( "rm -fr $userdir" );

		// chmod the zip to be deletable by anything other than apache
		chmod( $link, 0666 );

		print_hash( $hash, $link );

	}
?>

<?php

	include( $FOOTER );

?>


