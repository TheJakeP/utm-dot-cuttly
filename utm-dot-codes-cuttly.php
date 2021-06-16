<?php
/**
 * Utm.codes with cutt.ly - A plugin that extends UtmDotCodes to use the cutt.ly link shortening API.
 *
 * @package UtmDotCodesCuttly
 * @license http://www.gnu.org/licenses/gpl-2.0.html
 * @link https://utm.codes
 *
 * @wordpress-plugin
 * Plugin Name: utm.codes with cutt.ly
 * Plugin URI: https://utm.codes
 * Description: This plugin extends the native ability of the utm.codes plugin allowing it to integrate with <a href="https://cutt.ly/api-documentation/cuttly-links-api" target="_blank">cutt.ly links API</a>. If you don't already have an account, you will need to <a href="https://cutt.ly/register" target="_blank">register for a cutt.ly account</a>. Next, find your <a href="https://cutt.ly/edit" target="_blank">cutt.ly API Key</a>. Finally, enter your cutt.ly api key in the <a href="/wp-admin/options-general.php?page=utm-dot-codes-cuttly">utm.codes - cutt.ly settings page</a>.
 * Version: 0.0.1
 * Author: Jacob Phelps
 * Author URI: https://jacoblphelps.com
 * License: GPL v2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: utm-dot-codes-cuttly
 */


namespace UtmDotCodesCuttly {
  /**
   * Plugins shouldn't be called directly.
   */
  if ( ! function_exists( 'add_action' ) ) {
    die( '-1' );
  }
  include_once 'classes/class-CheckUtmDotCodesActivated.php';
  $var = new check_utm_dot_codes_installed();

 namespace UtmDotCodes { 
  define( 'UTMDC_CUTTLY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
  define( 'UTMDC_CUTTLY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
  define( 'UTMDC_CUTTLY_PLUGIN_FILE', plugin_basename( __FILE__ ) );

  $in_wp_admin   = is_admin();
  $running_tests = ( defined( 'UTMDC_IS_TEST' ) && UTMDC_IS_TEST );
  $should_load   = ( ! class_exists( 'UtmDotCodesCuttly' ) );
  if ( ( $in_wp_admin || $running_tests ) && $should_load ) {
    require_once 'classes/class-UtmDotCodesCuttly.php';
    new UtmDotCodesCuttly();
  }
}
