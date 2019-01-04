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
 * Shortcode callback
 */
function uri_cams_shortcode($attributes, $content, $shortcode) {

	// Attributes
	extract( shortcode_atts(
		array(
			'ip' => '131.128.104.45',
			'username' => 'Viewer',
			'password' => 'bay campus',
			'alt' => '',
			'class' => ''
		), $attributes )
	);
	
	
	$transient_name = 'uri_cams_' . $ip;

	if ( false === ( $photo = get_site_transient( $transient_name ) ) ) {
		// It wasn't there, so regenerate the data and save the transient
		$photo = uri_cams_get_image($ip, $username, $password);
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

	// @todo what to do if $path isn't set and there's no old image?
	
	if( ! empty( $time ) ) {
		$alt .= ' (retrieved ' . Date('Y-m-d H:i:s', $time) . ')';
	}

	$classes = 'uri-cams';
	$classes .= ( ! empty( $class ) ) ? ' ' . $class : '';

	$output = '<figure class="' . $classes . '">';
	// $output .= strtotime('now');
	$output .= '<img src="' . $path . '?t=' . $time . '" alt="' . $alt . '" />';

	$output .= '</figure>';

	return $output;

}
add_shortcode( 'uri-cams', 'uri_cams_shortcode' );




function uri_cams_get_image( $ip, $username, $password ) {

	$url = 'http://' . $ip . '/media/cam0/still.jpg?res=max';
	$timeout = 10;

	// set up curl
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );

	// return to variable
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

	// (don't) verify host ssl cert
	// curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, $ssl_verifyhost );
	// (don't) verify peer ssl cert	
	// curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, $ssl_verifypeer );

	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
	// send a user:password
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
	}
	
}


function uri_cams_save_file( $destination, $response, $ip ) {
	$success = FALSE;
	if ( wp_mkdir_p( $destination ) ) {
		$filename =  uri_cams_get_name($ip);
		$success = file_put_contents(trailingslashit( $destination ) . $filename, $response);
	}
	if( $success ) {
		return $filename;
	} else {
		return FALSE;
	}
}


function uri_cams_get_directory() {
	return trailingslashit( WP_CONTENT_DIR ) . 'uploads/uri-cams';
}

function uri_cams_get_path() {
	return trailingslashit( WP_CONTENT_URL ) . 'uploads/uri-cams/';
}

function uri_cams_get_name($ip) {
	return 'uri-cams--' . $ip . '.jpg';
}