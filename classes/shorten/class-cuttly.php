<?php
/**
 * Cutt.ly API shortener class.
 *
 * @package UtmDotCodes
 */

namespace UtmDotCodes;

/**
 * Class Cutt.ly.
 */
class Cuttly implements \UtmDotCodes\Shorten {

	const API_URL = 'https://cutt.ly/api/api.php';
	const SOCIAL_ARR = array(
		'behance',
		'blogger',
		'digg',
		'discourse',
		'facebook',
		'flickr',
		'github',
		'goodreads',
		'hacker-news',
		'instagram',
		'linkedin',
		'medium',
		'meetup',
		'mix',
		'odnoklassniki',
		'pinterest',
		'reddit',
		'slack',
		'stack-exchange',
		'stack-overflow',
		'tumblr',
		'twitter',
		'vimeo',
		'vk',
		'weibo',
		'whatsapp',
		'xing',
		'yelp',
		'youtube',
	);

	/**
	 * API credentials for sample API.
	 *
	 * @var string|null The API key for the shortener.
	 */
	private $api_key;

	/**
	 * Response from API.
	 *
	 * @var object|null The response object from the shortener.
	 */
	private $response;

	/**
	 * Error message.
	 *
	 * @var object|null Error object with code and message properties.
	 */
	private $error_code;

	/**
	 * cutt.ly constructor.
	 *
	 * @param string $api_key Credentials for API.
	 */
	public function __construct( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Gets an append string based on the social media value.
	 *
	 * @param string $social A string, hopefully in the SOCIAL_ARR constant.
	 * 
	 * @return string
	 */
	private function get_url_append($social){
		$i = array_search($social, self::SOCIAL_ARR);
		if ($i == false){
			return strval($social);
		} else {
			return strval($i);
		}
	}

	/**
	 * Extracts the url path from the requested cutt.ly shortlink.
	 *
	 * @param string  $short_url The URL to be shortened.
	 *
	 * @return string
	 */
	private function get_url_name($short_url){
		$short_url_arr = explode("/", $short_url);
		$split = array_search("cutt.ly", $short_url_arr);
		$name_arr = array_slice($short_url_arr, $split + 1);
		$name = implode("/", $name_arr);
		return $name;
	}

	/**
	 * See interface for docblock.
	 *
	 * @inheritDoc
	 *
	 * @param array  $data See interface.
	 * @param string $query_string See interface.
	 *
	 * @return void
	 */
	public function shorten( $data, $query_string ) {
		if ( isset( $data['meta_input'] ) ) {
			$data = $data['meta_input'];
		}

		if (isset($_POST['utmdclink_batch'])){
			if ($_POST['utmdclink_batch'] == "on"){
				$url_append = $this->get_url_append($data['utmdclink_source']);
				$short_url = $_POST['utmdclink_shorturl'];
			}
		} else {
			$url_append = "";
			$short_url = $data['utmdclink_shorturl'];
		}
		echo "short_url: $short_url";
		if ($short_url != ""){
			$name = $this->get_url_name($short_url) . $url_append;
		} else {
			$name = "";
		}
		
		if (isset($data['utmdclink_url'])){
			$url_to_shorten = $data['utmdclink_url'] . $query_string;
		} else {
			$url_to_shorten = "";
		}

		if ( '' !== $this->api_key ) {

			echo "<br><b>".$data['utmdclink_source'] . "</b><br>";
			$response = $this->call_to_api($url_to_shorten, $name);
			$i = 0;
			if ($response==null){
				echo "-> SLOW DOWN! We're WAY too fast! The cutt.ly api only allows 6 API requests per minute with free accounts.<br>";
				echo "-> Please do not navigate away from this page. <b>This can take up to 60 seconds.</b><br>";
			}
			while ($response == null){
				echo "--- T=". $i * 10 . ". We need to wait another 10 seconds.<br>";
				$i++;
				ob_flush();
				flush();
				sleep(10);
				$response = $this->call_to_api($url_to_shorten, $name);
			}
			
			$status = $response['url']['status'];
			switch ($status){
				case "1":
					$this->error_code = 5001;
					echo "-> Error - The Cutt.ly API responded with an error: The shortened link comes from the domain that shortens the link, i.e. the link has already been shortened.";
					break;
				case "2":
					$this->error_code = 5002;
					echo "-> Error - The Cutt.ly API responded with an error: The entered link is not a link.";
					break;
				case "3":
					$this->error_code = 5003;
					echo "-> Error - The Cutt.ly API responded with an error: The preferred Short URL is already taken. Please select a different Short URL and try again.";
					break;
				case "4":
					$this->error_code = 5004;
					echo "-> Error - The Cutt.ly API responded with unauthorized error: API Key is invalid or rate limit exceeded.";
					break;
				case "5":
					$this->error_code = 5005;
					echo "-> Error - The Cutt.ly API responded with an error: The link has not passed the validation. It includes invalid characters.";
					break;
				case "6":
					$this->error_code = 5006;
					echo "-> Error - The Cutt.ly API responded with an error: The link provided is from a blocked domain.', 'utm-dot-codes";
					break;
				case "7":
					$this->response = $response['url']['shortLink'];
					echo "-> Success - the link has been shortened";
					break;
			}
		}

		$redirect = "/wp-admin/post.php?post=" . $_POST['ID'] . "&action=edit";
		?>
		<script>
			window.onload = function() {
				self.location = "<?php echo $redirect; ?>";
			};
		</script>
		<?php
		
	}

	/**
	 * Makes the call to the cutt.ly API.
	 *
	 *
	 * @param string $url_to_shorten This is the string of the url including params to be redirected to.
	 * @param string $name This is the name or path of the requested cuttly url.
	 *
	 * @return array The json decoded response array.
	 */
	public function call_to_api($url_to_shorten, $name){
		$url = urlencode($url_to_shorten);
		$api_url = self::API_URL . "?key=" . $this->api_key . "&short=" . $url . "&name=" . $name;
		
		$ch = curl_init();
		$timeout = 1;
		curl_setopt($ch, CURLOPT_URL, $api_url);
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$timeout);
		$json = curl_exec($ch);
		$response = json_decode ($json, true);
		
		curl_close($ch);
		return $response;
	}

	/**
	 * Get response from Sample API for the request.
	 *
	 * @inheritDoc
	 */
	public function get_response() {
		return $this->response;
	}

	/**
	 * Get error code/message returned by Sample API for the request.
	 *
	 * @inheritDoc
	 */
	public function get_error() {		
		return $this->error_code;
	}
}
