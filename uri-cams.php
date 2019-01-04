<?php
/*
Plugin Name: URI Cams
Plugin URI: https://www.uri.edu
Description: Webcam picture importer
Version: 1.0.1
Author: URI Web Communications
Author URI: 
@author: John Pennypacker <jpennypacker@uri.edu>
*/

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');



/**
 * Loads up the css
 */
function uri_cams_styles() {
	$css_url = plugins_url( '/css/cams.css', __FILE__ );
	$cache = filemtime( dirname(__FILE__) . '/css/cams.css' );
	wp_enqueue_style( 'uri-cams-css', $css_url, array(), $cache );
}
add_action( 'wp_enqueue_scripts', 'uri_cams_styles' );



/**
 * IP camera shortcode callback
 */
function uri_cams_shortcode($attributes, $content, $shortcode) {

	// get the shortcode attributes and add defaults 
	extract( shortcode_atts(
		array(
			'ip' => '131.128.104.45',
			'username' => 'Viewer',
			'password' => 'bay campus',
			'alt' => '',
			'class' => '',
			'link' => false
		), $attributes )
	);
	
	
	// load the cached image

	$transient_name = 'uri_cams_' . $ip;

	if ( false === ( $photo = get_site_transient( $transient_name ) ) ) {
		// It wasn't there, so regenerate the data and save the transient
		$photo = uri_cams_retrieve_image($ip, $username, $password);
		set_site_transient( $transient_name, $photo, 10 * MINUTE_IN_SECONDS );
	}
	
	$path = $photo['path'];
	$time = $photo['time'];
	
	$filename = uri_cams_get_name($ip);
	$file = uri_cams_get_directory() . '/' . $filename;
	if( file_exists( $file ) ) {
		$path = uri_cams_get_path() . $filename;
		$time = filemtime($file);
	}
	
	if ( empty($path) || empty($time) ) {
		// we don't have a file we can use;  bail out.
		return '';
	}
	
	ob_start();
	include 'templates/uri-cams-shortcode.php';
	$html = ob_get_clean();
	return $html;


}
add_shortcode( 'uri-cams', 'uri_cams_shortcode' );



/**
 * Retrieve a remote image
 * @param str the camera's IP
 * @param str the username for the camera
 * @param str the password for the camera
 * @return mixed arr on success; false on failure
 */
function uri_cams_retrieve_image( $ip, $username, $password ) {

	$url = 'http://' . $ip . '/media/cam0/still.jpg?res=max';
	$timeout = 10;

	// set up curl
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );

	// return to variable
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

	// send a user:password
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
	curl_setopt( $ch, CURLOPT_USERPWD,  $username . ':' . $password );

	// fetch remote contents
	if ( false === ( $response = curl_exec( $ch ) ) )	{
		// if we get an error, use that
		$error = curl_error( $ch );						
	}
	// close the resource
	curl_close( $ch );
		
	if( empty( $error ) ) {
		$destination = uri_cams_get_directory();
		$file = uri_cams_save_file( $destination, $response, $ip );
		$path = uri_cams_get_path() . $file;
		return array(
			'path' => $path,
			'time' => strtotime('now')
		);
	} else {
		return FALSE;
	}
	
}

/**
 * Write a retrieved image to disk
 * @param str the destination directory
 * @param str the data to write i.e. [binary] source of the image
 * @param str an identifier to add the filename e.g. the camera's domain name or IP address
 * @return mixed
 */
function uri_cams_save_file( $destination, $response, $id ) {
	$success = FALSE;
	if ( wp_mkdir_p( $destination ) ) {
		$filename =  uri_cams_get_name($id);
		$success = file_put_contents(trailingslashit( $destination ) . $filename, $response);
	}
	if( $success ) {
		return $filename;
	} else {
		return FALSE;
	}
}

/**
 * Get the server-side path for the uploads directory
 * @return str
 */
function uri_cams_get_directory() {
	return trailingslashit( WP_CONTENT_DIR ) . 'uploads/uri-cams';
}

/**
 * Get the client-side path for the uploads directory
 * @return str
 */
function uri_cams_get_path() {
	return trailingslashit( WP_CONTENT_URL ) . 'uploads/uri-cams/';
}

/**
 * Generate the file name based on the IP address of the camera
 * @param str an identifier to add the filename e.g. the camera's domain name or IP address
 * @return str
 */
function uri_cams_get_name($id) {
	$id = str_replace( '/', '-', $id );
	return 'uri-cams--' . $id . '.jpg';
}

/**
 * Format a UNIX timestamp in a consistent way
 * @return str
 */
function uri_cams_format_date($timestamp) {
	if( ! empty ( $timestamp ) ) {
		$t = new DateTime( '@'.$timestamp );	
	} else {
		$t = new DateTime( 'now' );	
	}
	$t->setTimezone( new DateTimeZone( get_option('gmt_offset') ) );
	return $t->format('Y-m-d H:i:s');
}





/** LOTS OF NON_DRY CODE AHEAD... SORRY **/


/**
 * Shortcode callback for engineering images
 */
function uri_cams_engineering_shortcode($attributes, $content, $shortcode) {

	// get the shortcode attributes and add defaults 
	extract( shortcode_atts(
		array(
			'url' => 'http://www.ele.uri.edu/camera/archive2',
			'alt' => '',
			'class' => '',
			'link' => false
		), $attributes )
	);
	

	// load the cached image

	$transient_name = 'uri_cams_' . $url;

	if ( false === ( $photo = get_site_transient( $transient_name ) ) ) {
		// It wasn't there, so regenerate the data and save the transient
		$photo = uri_cams_retrieve_engineering_image( $url );
		set_site_transient( $transient_name, $photo, 10 * MINUTE_IN_SECONDS );
	}
	
	$path = $photo['path'];
	$time = $photo['time'];
	
	$filename = uri_cams_get_name($url);
	$file = uri_cams_get_directory() . '/' . $filename;
	if( file_exists( $file ) ) {
		$path = uri_cams_get_path() . $filename;
		$time = filemtime($file);
	}
	
	if ( empty($path) || empty($time) ) {
		// we don't have a file we can use;  bail out.
		return '';
	}
	
	ob_start();
	include 'templates/uri-cams-shortcode.php';
	$html = ob_get_clean();
	return $html;



}
add_shortcode( 'uri-engineering-cams', 'uri_cams_engineering_shortcode' );

/**
 * Retrieve a remote engineering image
 * @param str the camera's host URL
 * @return mixed arr on success; false on failure
 */
function uri_cams_retrieve_engineering_image( $base_url ) {

	$now = new DateTime( 'now', new DateTimeZone( get_option('gmt_offset') ) );
	if( $now->format('H') < 6 ) {
		$t = new DateTime( 'yesterday', new DateTimeZone( get_option('gmt_offset') ) );
		$url = trailingslashit($base_url) . $t->format('Y-m-d') . '/12:00.jpg';
	} else if( $now->format('H') >= 18 ) {
		$t = new DateTime( 'now', new DateTimeZone( get_option('gmt_offset') ) );
		$url = trailingslashit($base_url) . $t->format('Y-m-d') . '/17:00.jpg';
	} else {
		$t = new DateTime( 'now', new DateTimeZone( get_option('gmt_offset') ) );
		$url = trailingslashit($base_url) . $t->format('Y-m-d/H:i') . '.jpg';
	}

	$timeout = 10;

	// set up curl
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );

	// return to variable
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

	// fetch remote contents
	if ( false === ( $response = curl_exec( $ch ) ) )	{
		// if we get an error, use that
		$error = curl_error( $ch );						
	}
	// close the resource
	curl_close( $ch );
		
	if( empty( $error ) ) {
		$destination = uri_cams_get_directory();
		$file = uri_cams_save_file( $destination, $response, $base_url );
		$path = uri_cams_get_path() . $file;
		return array(
			'path' => $path,
			'time' => strtotime('now')
		);
	} else {
		return FALSE;
	}
	
}


