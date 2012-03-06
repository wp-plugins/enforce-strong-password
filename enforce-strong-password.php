<?php
/*
Plugin Name: Enforce strong password
Plugin URI: http://wordpress.org/extend/plugins/enforce-strong-password
Description: Forces all users to have a strong password when they're changing it on their profile page.
Version: 1.2
Author: Zaantar
Author URI: http://zaantar.eu
License: GPL2
*/

/*
    Copyright 2010 Zaantar (email: zaantar@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/*****************************************************************************\
		I18N
\*****************************************************************************/


define( 'ESP_TEXTDOMAIN', 'enforce-strong-password' );


add_action( 'init', 'esp_load_textdomain' );


function esp_load_textdomain() {
	$plugin_dir = basename( dirname(__FILE__) );
	load_plugin_textdomain( ESP_TEXTDOMAIN, false, $plugin_dir.'/languages' );
}


/*****************************************************************************\
		PASSWORD STRENGTH ENFOCEMENT
\*****************************************************************************/


add_action( 'user_profile_update_errors', 'esp_strong_password_enforcement', 0, 3 );


/*source: http://sltaylor.co.uk/blog/enforce-strong-wordpress-passwords/ */
function esp_strong_password_enforcement( $errors ) {
	/*$enforce = true;
	$args = func_get_args();
	$userID = $args[2]->ID;*/
	
	if ( /*$enforce &&*/ !$errors->get_error_data("pass") 
		&& $_POST["pass1"] && esp_password_strength( $_POST["pass1"], $_POST["user_login"] ) != 4 ) {
		$errors->add( 'pass', __( 'Please enter a %sstrong%s password to ensure your and this blog\'s security.', ESP_TEXTDOMAIN ) );
	}
	return $errors;
}


// Check for password strength
// Copied from JS function in WP core: /wp-admin/js/password-strength-meter.js
function esp_password_strength( $i, $f ) {
	$h = 1; $e = 2; $b = 3; $a = 4; $d = 0; $g = null; $c = null;
	if ( strlen( $i ) < 4 )
		return $h;
	if ( strtolower( $i ) == strtolower( $f ) )
		return $e;
	if ( preg_match( "/[0-9]/", $i ) )
		$d += 10;
	if ( preg_match( "/[a-z]/", $i ) )
		$d += 26;
	if ( preg_match( "/[A-Z]/", $i ) )
		$d += 26;
	if ( preg_match( "/[^a-zA-Z0-9]/", $i ) )
		$d += 31;
	$g = log( pow( $d, strlen( $i ) ) );
	$c = $g / log( 2 );
	if ( $c < 40 )
		return $e;
	if ( $c < 56 )
		return $b;
	return $a;
}


?>
