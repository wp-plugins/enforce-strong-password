<?php
/*
Plugin Name: Enforce Strong Password
Plugin URI: http://wordpress.org/extend/plugins/enforce-strong-password
Description: Forces all users to have a strong password when they're changing it on their profile page.
Version: 1.3.2
Author: Zaantar
Author URI: http://zaantar.eu
Donate Link: http://zaantar.eu/financni-prispevek
License: GPL2
*/

/*
    Copyright (c) Zaantar (email: zaantar@gmail.com)

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

require_once plugin_dir_path( __FILE__ ).'includes/zan.php';


class EnforceStrongPassword {


	const slug = 'enforce-strong-password';
	const txd = self::slug;
	

	function __construct() {
		add_action( 'init', array( &$this, 'load_textdomain' ) );
		add_action( 'user_profile_update_errors', array( &$this, 'strong_password_enforcement' ), 0, 3 );
		add_action( "admin_menu", array( &$this, "admin_menu" ) );
		add_action( "network_admin_menu", array( &$this, "network_admin_menu" ) );
		add_action( "validate_password_reset", array(&$this, "validate_password_reset") );
	}
	
	
	function load_textdomain() {
		$plugin_dir = basename(dirname(__FILE__));
		load_plugin_textdomain( self::txd, false, $plugin_dir.'/languages' );
	}
	
	
	function admin_menu() {
		if( !is_multisite() ) {
			add_options_page(
				__( "Enforce Strong Password", self::txd ),
				__( "Enforce Strong Password", self::txd ),
				"manage_options",
				self::slug,
				array( &$this, "options_page" )
			);
		}
	}
	
	
	function network_admin_menu() {
		if( is_multisite() ) {
			add_submenu_page(
				"settings.php",
				__( "Enforce Strong Password", self::txd ),
				__( "Enforce Strong Password", self::txd ),
				"manage_network_options",
				self::slug,
				array( &$this, "options_page" )
			);
		
		}
	}
	
	
	function get_options() {
		$defaults = array(
			"minimal_required_strength" => 4,
			"hide_donation_button" => false
		);
		$options = get_site_option( self::slug, array() );
		return wp_parse_args( $options, $defaults );
	}
	
	
	function options_page() {
		echo "<div id=\"wrap\">";
		$action = isset( $_REQUEST["action"] ) ? $_REQUEST["action"] : "default";
		switch( $action ) {
		case 'update-options':
			$settings = $_REQUEST['settings'];
			$settings["hide_donation_button"] = isset( $_REQUEST['settings']["hide_donation_button"] );
			update_site_option( self::slug, $settings );
			z::nag( __( 'Options updated.', self::txd ) );
			$this->options_page_default();
			break;
		default:
			$this->options_page_default();
		}
		echo "</div>";
	}
	
	
	function options_page_default() {
		extract( $this->get_options() );
		?>
			<h2><?php _e( "Enforce Strong Password", self::txd ); ?></h2>
			<?php z::maybe_donation_button( $hide_donation_button, self::txd ); ?>
			<h3><?php _e( "Options", self::txd ); ?></h3>
			<form method="post">
				<input type="hidden" name="action" value="update-options" />
				<table class="form-table">
					<tr valign="top">
			        	<th>
			        		<label><?php _e( 'Minimal required password strength', self::txd ); ?></label><br />
			        	</th>
			        	<td>
			        		<select name="settings[minimal_required_strength]">
			        			<?php
			        				for( $i = 1; $i <= 4; ++$i ) {
			        					echo "<option value=\"$i\" ".selected( $i, $minimal_required_strength, false ).">$i</option>";
			        				}
			        			?>
			        		</select>
			        	</td>
			        	<td>
			        		<small>
			        			<?php
			        				printf( __( 'Larger value means stronger password. Recommended value is %s', self::txd ),
			        					4
			        				);
			        			?>
			        		</small>
			        	</td>
			        </tr>
					<tr valign="top">
			        	<th>
			        		<label><?php _e( 'Hide donation button', self::txd ); ?></label><br />
			        	</th>
			        	<td>
			        		<input type="checkbox" name="settings[hide_donation_button]"
			        			<?php checked( $hide_donation_button ); ?>
			        		/>
			        	</td>
			        	<td><small><?php _e( 'If you don\'t want to be bothered again...', self::txd ); ?></small></td>
			        </tr>
				</table>
				<?php submit_button(); ?>
			</form>
		<?php
	}
	
	
	/*source: http://sltaylor.co.uk/blog/enforce-strong-wordpress-passwords/ */
	function strong_password_enforcement( $errors ) {
		extract( $this->get_options() );
		get_currentuserinfo();
	
		if ( !$errors->get_error_data("pass")
		  && $_POST["pass1"]
		  && $this->get_password_strength( $_POST["pass1"], $current_user->user_login ) < $minimal_required_strength ) {
			$errors->add(
				'pass',
				sprintf(
					__( 'Please enter a %sstronger%s password to ensure your and this blog\'s security.', self::txd ),
					"<strong>",
					"</strong>"
				)
			);
		}
		return $errors;
	}

    
    function validate_password_reset( $errors, $user = NULL ) {
		$this->strong_password_enforcement( $errors );
	}


	// Check for password strength
	// Copied from JS function in WP core: /wp-admin/js/password-strength-meter.js
	function get_password_strength( $i, $f ) {
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

}


new EnforceStrongPassword();



?>
