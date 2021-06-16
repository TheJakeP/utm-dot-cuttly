<?php
/**
 * Utm.codes with Cuttly activation class
 *
 * @package UtmDotCodesCuttly
 */

/**
 * Class UtmDotCodes_Activation
 *
 * Implements activation and deactivation hooks for the utm.codes plugin
 */

namespace UtmDotCodesCuttly; 
class check_utm_dot_codes_installed {

	/**
	 * UtmDotCodes_Activation constructor, adds (de)activation hooks for our plugin
	 *
	 * @since 1.0
	 */
	public function __construct() {
        
        $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
        $utm_dot_codes_installed = in_array('utm-dot-codes/utm-dot-codes.php', $active_plugins);
        if (!$utm_dot_codes_installed){
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            deactivate_plugins('utm-dot-cuttly/utm-dot-codes-cuttly.php') ;
        }
    }
}