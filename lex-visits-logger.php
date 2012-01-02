<?php
/*
Plugin Name: Lex's Visits Logger
Plugin URI: http://le-boxon-de-lex.fr
Description: A plug-in to track visits, using files to store information.
Version: 1.0
Author: Lex
Author URI: http://le-boxon-de-lex.fr
License: GPL2
*/


/**
 * Define logging strategies.
 */
define( "LEX_VL_STRATEGY_DAILY", "1" );
define( "LEX_VL_STRATEGY_ALL", "2" );
define( "LEX_VL_STRATEGY_NONE", "3" );


/**
 * Define logs organization.
 */
define( "LEX_VL_ROTATION_MONTHLY", "1" );
define( "LEX_VL_ROTATION_DAILY", "2" );


/** 
 * Define the options.
 */
define( "LEX_VL_OPTION_STRATEGY", "lex_vl_strategy" );
define( "LEX_VL_OPTION_ROTATION", "lex_vl_rotation" );
define( "LEX_VL_OPTION_DIRECTORY", "lex_vl_directory" );


/**
 * Add a hook on initialization to track visits.
 */
add_action( 'init', 'lex_track_visit' );


/**
  * Tracks a visit, when possible.
  */
function lex_track_visit() {

	$time = time();

	// Get the logging strategy
	$strategy = get_option( LEX_VL_OPTION_STRATEGY );
	if( ! $strategy )
		$strategy = LEX_VL_STRATEGY_DAILY;
	
	// Follow the strategy
	if( $strategy == LEX_VL_STRATEGY_DAILY )
		lex_vl_strategy_daily( $time );
	else if( $strategy == LEX_VL_STRATEGY_ALL )
		lex_vl_strategy_all( $time );
	
	// Eventually, set or update the cookie
	$seconds_per_day = 3600 * 24;
	setcookie( "last_visit", $time, $time + $seconds_per_day, COOKIEPATH, COOKIE_DOMAIN );
}


/**
 * "Daily" strategy
 * We log at most 1 entry per cookie and per day.
 * Side effect: a cookie can result in 1 entry at 23:59 and another one at 00:01.
 *
 * @param $time the time
 */
function lex_vl_strategy_daily( $time ) {

	$current_time = date( "yyyy-mm-jj", $time );
	
	// Should we track this visit?
	$new_user = false;
	if( isset( $_COOKIE[ 'last_visit' ] )) {
		$last_time = date( "yyyy-mm-jj", $_COOKIE[ 'last_visit' ]);
		$new_user = strcmp( $last_time, $current_time ) != 0;
	}
	else {
		$new_user = true;
	}
	
	// Track this visit
	if( $new_user )
		lex_track_this_visit( $time );
}


/**
 * "All" strategy
 * We log every made request. The cookie is ignored.
 * Side effect: bigger files.
 *
 * @param $time the time
 */
function lex_vl_strategy_all( $time ) {
	lex_track_this_visit( $time );
}


/**
  * Logs information from this user.
  * @param $time the visit time, as a timestamp
  */
function lex_track_this_visit( $time ) {

	// Check the log directory
	$incoming_stats_directory = get_option( LEX_VL_OPTION_DIRECTORY );
	if( ! $incoming_stats_directory )
		return;


	// Prepare the data to write
	$data = "\n\nvisit-time = $time \nincoming-page = " . $_SERVER[ 'REQUEST_URI' ];
	if( isset( $_SERVER[ 'HTTP_REFERER' ] ))
		$data .= "\nreferrer = " . $_SERVER[ 'HTTP_REFERER' ];
		
	$ip = "";
	if( isset( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ]))
        $ip = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
    else 
		$ip = $_SERVER[ 'REMOTE_ADDR' ];
	$data .= "\nip-address = " . trim( $ip );

	
	// Prepare the log directory
	$file = $_SERVER[ 'DOCUMENT_ROOT' ];
	if( strcmp( substr( $file, -1 ), "/" ) != 0
			&& strcmp( substr( $incoming_stats_directory, 0, 1 ), "/" ) != 0 )
		$file .= "/";
	
	$file .= $incoming_stats_directory;
	if( strcmp( substr( $file, -1 ), "/" ) != 0 )
		$file .= "/";
		
	if( ! file_exists( $file ))
		mkdir( $file, 0777, true );
	
	
	// Find the log file
	$rotate = get_option( LEX_VL_OPTION_ROTATION );
	if( ! $rotate )
		$rotate = LEX_VL_ROTATION_MONTHLY;
		
	if( $rotate == LEX_VL_ROTATION_MONTHLY )
		$file .= date( "Y-m", $time ) . ".txt";
	else if( $rotate == LEX_VL_ROTATION_DAILY )
		$file .= date( "Y-m-d", $time ) . ".txt";

	
	// Lock the log file
	$fp = fopen( $file, "a" );
	if( $fp ) {
		if( flock( $fp, LOCK_EX )) {
		
			// Write in the log file
			fwrite( $fp, $data );
		
			// Unlock the log file
			flock( $fp, LOCK_UN );
		}
		else {
			// Nothing. "C'est la vie..."
			// To use for debug purpose only.
			// echo "The log file could not be locked.";
		}
		
		fclose( $fp );
	}
}


/**
 * Adds the admin menus.
 */
add_action( 'admin_menu', 'lex_visits_logger_admins' );
function lex_visits_logger_admins() {
	add_options_page( "Visits Logger", "Visits Logger", "manage_options", "lex-visits-logger-management", 'lex_visits_logger_admin' );
}


/**
 * Displays the configuration menu in the administration panel.
 */
function lex_visits_logger_admin() {

?>

<div class="wrap">
	<h2>Visits Logger</h2>
	
	<form id="lex_dir_form" name="lex_dir_form" method="POST" action="<?php echo $_SERVER[ 'PHP_SELF' ] . "?" . esc_html( $_SERVER['QUERY_STRING']) ?>">
	<h3>Select the logging directory</h3>
	<p>
		<input type="text" size="100" value="<?php $t = get_option( LEX_VL_OPTION_DIRECTORY ); echo $t ? $t : ""; ?>" name="lex_directory" />
		<input type="button" onclick="document.lex_dir_form.submit();" value="Submit" />
	</p>
	</form>

	<br />
	<form id="lex_strategy_form" name="lex_strategy_form" method="POST" action="<?php echo $_SERVER[ 'PHP_SELF' ] . "?" . esc_html( $_SERVER['QUERY_STRING']) ?>">
	<h3>Select the tracking strategy</h3>
	<?php 
		$choice = get_option( LEX_VL_OPTION_STRATEGY ); 
		if( ! $choice )
			$choice = LEX_VL_STRATEGY_DAILY;
	?>
	<p>
		<input style="margin-right: 5px;" type="radio" value="<?php echo LEX_VL_STRATEGY_DAILY; ?>" name="lex_strategy" <?php if( $choice == LEX_VL_STRATEGY_DAILY ) echo 'checked="true"' ?>>
			<strong>Daily:</strong> log 1 entry per cookie and per day (a cookie is used).
		</input><br />
		
		<input style="margin-right: 5px;" type="radio" value="<?php echo LEX_VL_STRATEGY_ALL; ?>" name="lex_strategy" <?php if( $choice == LEX_VL_STRATEGY_ALL ) echo 'checked="true"' ?>>
			<strong>All:</strong> log 1 entry per viewed page (no cookie is used).
		</input><br />
		
		<input style="margin-right: 5px;" type="radio" value="<?php echo LEX_VL_STRATEGY_NONE; ?>" name="lex_strategy" <?php if( $choice == LEX_VL_STRATEGY_NONE ) echo 'checked="true"' ?>>
			<strong>None:</strong> disable tracking.
		</input><br /><br />
		
		<input type="button" onclick="document.lex_strategy_form.submit();" value="Submit" />
	</p>
	</form>
	
	<br />
	<form id="lex_lr_form" name="lex_lr_form" method="POST" action="<?php echo $_SERVER[ 'PHP_SELF' ] . "?" . esc_html( $_SERVER['QUERY_STRING']) ?>">
	<h3>Select the logs rotation period</h3>
	<?php
		$choice = get_option( LEX_VL_OPTION_ROTATION ); 
		if( ! $choice )
			$choice = LEX_VL_ROTATION_MONTHLY;
	?>
	<p>
		<input style="margin-right: 5px;" type="radio" value="<?php echo LEX_VL_ROTATION_MONTHLY; ?>" name="lex_rotation" <?php if( $choice == LEX_VL_ROTATION_MONTHLY ) echo 'checked="true"' ?>>
			<strong>Monthly:</strong> to have one log file per month.
		</input><br />
		
		<input style="margin-right: 5px;" type="radio" value="<?php echo LEX_VL_ROTATION_DAILY; ?>" name="lex_rotation" <?php if( $choice == LEX_VL_ROTATION_DAILY ) echo 'checked="true"' ?>>
			<strong>Daily:</strong> to have one log file per day.
		</input><br /><br />
		
		<input type="button" onclick="document.lex_lr_form.submit();" value="Submit" />
	</p>
	</form>
</div>

<?php

}


/**
 * Processes the administration forms.
 */
if( isset( $_POST[ 'lex_directory' ])) {
	update_option( LEX_VL_OPTION_DIRECTORY, $_POST[ 'lex_directory' ]);
	unset( $_POST[ 'lex_directory' ]);

} else if( isset( $_POST[ 'lex_strategy' ])) {
	update_option( LEX_VL_OPTION_STRATEGY, $_POST[ 'lex_strategy' ]);
	unset( $_POST[ 'lex_strategy' ]);
	
} else if( isset( $_POST[ 'lex_rotation' ])) {
	update_option( LEX_VL_OPTION_ROTATION, $_POST[ 'lex_rotation' ]);
	unset( $_POST[ 'lex_rotation' ]);	
}

?>