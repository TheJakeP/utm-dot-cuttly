<?php 

/**
 * Utm.codes plugin class
 *
 * @package UtmDotCodesCuttly
 */

/**
 * Class UtmDotCodesCuttly
 */
namespace UtmDotCodes;

class UtmDotCodesCuttly {

	const POST_TYPE        = 'utmdc_cuttly_link';
	const NONCE_LABEL      = 'UTMDC_cuttly__nonce';
	const REST_NONCE_LABEL = 'UTMDC_cuttly__REST_nonce';
	const SETTINGS_PAGE    = 'utm-dot-codes-cuttly';
	const SETTINGS_GROUP   = 'UTMDC_cuttly_settings_group';

    public function __construct() {
	
		add_action( 'admin_menu', array( &$this, 'add_cuttly_settings_page' ) );
		add_action( 'admin_init', array( &$this, 'register_cuttly_plugin_api_field' ) );
		
		add_filter( 'utmdc_shorten_object', array( &$this, 'register_cuttly_shortener' ) );
		add_filter( 'utmdc_error_message', array( &$this, 'augment_error_messages'), 10, 2);
	}
    
	
    /**
	 * Register's the cutt.ly shortener class with the utmdotcodes plugin.
	 *
	 * @since 0.0.1
	 */
	public function register_cuttly_shortener ( $shortener ){
		include_once 'shorten/class-cuttly.php';
		$api_key = get_option(self::POST_TYPE . '_apikey');
		return new Cuttly($api_key);
	}

    /**
	 * Augment the Error Messages to include cutt.ly specific API errors.
	 *
	 * @since 0.0.1
	 */
	public function augment_error_messages ( $error_message, $error_code){
		if ($error_code == 5001){
			$error_message = array(
				'style'   => 'notice-error',
				'message' => esc_html__( 'The Cutt.ly API responded with an error: The shortened link comes from the domain that shortens the link, i.e. the link has already been shortened.', 'utm-dot-codes' ),
			);
		} else if ($error_code == 5002){
			$error_message = array(
				'style'   => 'notice-error',
				'message' => esc_html__( 'The Cutt.ly API responded with an error: The entered link is not a link.', 'utm-dot-codes' ),
			);
		} else if ($error_code == 5003){
			$error_message = array(
				'style'   => 'notice-error',
				'message' => esc_html__( 'The Cutt.ly API responded with an error: The preferred Short URL is already taken. Please select a different Short URL and try again.', 'utm-dot-codes' ),
			);
		} else if ($error_code == 5004){
			$error_message = array(
				'style'   => 'notice-error',
				'message' => esc_html__( 'The Cutt.ly API responded with unauthorized error: API Key is invalid or rate limit exceeded.', 'utm-dot-codes' ),
			);
		} else if ($error_code == 5005){
			$error_message = array(
				'style'   => 'notice-error',
				'message' => esc_html__( 'The Cutt.ly API responded with an error: The link has not passed the validation. It includes invalid characters.', 'utm-dot-codes' ),
			);
		} else if ($error_code == 5006){
			$error_message = array(
				'style'   => 'notice-error',
				'message' => esc_html__( 'The Cutt.ly API responded with an error: The link provided is from a blocked domain.', 'utm-dot-codes' ),
			);
		}
		return $error_message;
	  }
	


   	/**
	 * Register links plugin settings page.
	 *
	 * @since 0.0.1
	 */
	public function add_cuttly_settings_page() {
		add_options_page(
			esc_html__( 'utm.codes - cutt.ly API Settings', 'utm-dot-codes-cuttly' ),
			esc_html__( 'utm.codes - cuttly', 'utm-dot-codes-cuttly' ),
			'manage_options',
			self::SETTINGS_PAGE,
			array( &$this, 'render_settings_options' )
		);
	}

	/**
	 * Register plugin settings.
	 *
	 * @since 0.0.1
	 */
	public function register_cuttly_plugin_api_field() {
		register_setting( self::SETTINGS_GROUP, self::POST_TYPE . '_apikey' );
	}

    /**
	 * Generate and output links settings page options.
	 *
	 * @since 0.0.1
	 */
    public function render_settings_options(){
        ?>
		<div class="wrap">
			<form method="post" action="options.php">
				<h1>
					<img src="<?php echo esc_url( UTMDC_PLUGIN_URL ); ?>img/utm-dot-codes-logo.png" id="utm_dot_codes_logo" alt="utm.codes Settings" title="Configure your utm.codes plugin here.">
				</h1>
				<h1 class="title">
					<img src="<?php echo esc_url( UTMDC_CUTTLY_PLUGIN_URL ); ?>img/cuttly-icon.png" id="utm_dot_codes_logo" alt="utm.codes cuttly Settings" title="Configure your cutt.ly API plugin here.">
				</h1>
				<p>
					<?php esc_html_e( 'Setup api access to enable link shortening with cutt.ly.', 'utm-dot-codes-cuttly' ); ?>
				</p>
				<table class="form-table">
				<?php
					$active_shortener = 'cuttly';
				?>
					<tr valign="top" id="utmdclinks_shortener_api_row" class="<?php echo ( 'none' === $active_shortener ) ? 'hidden' : ''; ?>">
						<th scope="row">
							<?php esc_html_e( 'Cutt.ly API Key:', 'utm-dot-codes-cuttly' ); ?>
						</th>
						<td>
							<?php
							settings_fields(self::SETTINGS_GROUP );
							printf(
								'<input type="text" name="%s" value="%s" size="40">',
								esc_html( self::POST_TYPE . '_apikey' ),
								esc_html( get_option( self::POST_TYPE . '_apikey' ) )
							);
							?>
						</td>
					</tr>
				</table>
				<?php submit_button($text="Save API"); ?>
			</form>
		</div>
        <?php
    }
	

}