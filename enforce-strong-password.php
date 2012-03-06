<?php
/*
Plugin Name: Enforce strong password
Plugin URI: http://zaantar.eu/index.php?page=enforce-strong-password
Description: Po aktivaci vynutí dostatečně silné heslo při jeho změně v uživatelském profilu. <strong>Vyvinuto pro blogosphere.cz</strong>
Version: 1.1
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

/*zdroj: http://sltaylor.co.uk/blog/enforce-strong-wordpress-passwords/ */

// Enforce strong passwords
function esp_strong_password_enforcement( $errors ) {
	$enforce = true;
	$args = func_get_args();
	$userID = $args[2]->ID;
	
	/* možnost silného hesla jen pro adminy a editory */
/*	if ( $userID ) {
		// User ID specified - omit check for user levels below 5
		$userInfo = get_userdata( $userID );
		if ( $userInfo->user_level < 5 ) {
			$enforce = false;
		}
	} else {
		// No ID yet, adding new user - omit check for "weaker" roles
		if ( in_array( $_POST["role"], array( "subscriber", "author", "contributor" ) ) ) {
			$enforce = false;
		}
	}
*/
	if ( $enforce && !$errors->get_error_data("pass") && $_POST["pass1"] && esp_password_strength( $_POST["pass1"], $_POST["user_login"] ) != 4 ) {
			$errors->add( 'pass', 'CHYBA: Prosím zadejte <strong>silné</strong> heslo. Předejdete tím riziku narušení bezpečnosti Vašich osobních údajů.' );
	}
	return $errors;
}

add_action( 'user_profile_update_errors', 'esp_strong_password_enforcement', 0, 3 );

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
