<?php
/**
 * This file contains a basic class that handles interaction when generating
 * requests and pulling data back from the sc2ranks.com API. Uses the json
 * interface and the built in PHP json deserializer.
 * @author Ameer Ayoub <ameer.ayoub@gmail.com>
 * @version 0.3.1
 * @package sc2ranks
 */
 
	/**
	 * The main class that wraps the requests, all interaction with the API
	 * is done through this class, see the example for an example implementation
	 * of how to use this class.
	 * @package sc2ranks
	 */
	class sc2ranks_request {
		/**
		 * The site request key specified in the request url with the GET
		 * variable appKey, set in the constructor, if none provided then
		 * defaults to the server name as defined in $_SERVER['SERVER_NAME']
		 * @access private
		 * @var string
		 */
		private $request_site_key;
		/**
		 * The last error that came from json parsing, gotten from 
		 * json_last_error(), only available in php 5.3.0 or greater.
		 * @link http://www.php.net/manual/en/function.json-last-error.php 
		 *		json_last_error documentation
		 * @access private
		 * @var int
		 * @see json_last_error()
		 */
		private $last_json_error = null;
		/**
		 * Temporary variable to store the deserialized response object from
		 * the last request.
		 * @acess private
		 * @var object
		 * @see last_response()
		 */
		private $last_response = null;
		/**
		 * Temporary variable to store the request url string, from the last
		 * request.
		 * @access private
		 * @var string
		 * @see last_request()
		 */
		private $last_request = null;
		/**
		 * The base address for all requests. Currently
		 * http://sc2ranks.com/api/base/teams/
		 * @access private
		 * @var string
		 */
		private $site_address = "http://sc2ranks.com/api/base/teams/";
		
		/**
		 * The base address for profile requests. Currently
		 * http://sc2ranks.com/api/psearch/
		 * @access private
		 * @var string
		 */
		private $profile_search_address = "http://sc2ranks.com/api/psearch/";
		
		/**
		 * The base address for map requests
		 * http://http://sc2ranks.com/api/map/
		 * @access private
		 * @var string
		 */
		private $map_request_address = "http://sc2ranks.com/api/map/";
		
		/**
		 * Whether or not json errors are enabled, available in php 5.3.0
		 * or greater.
		 * @access private
		 * @var boolean
		 * @see is_json_last_error_enabled()
		 */
		private $json_errors_enabled = False;
		
		/**
		 * Method of url grabbing
		 * fopen or curl, defaults to fopen
		 */
		private $request_method = "fopen";
		private $curl_inst = null;
		private $curl_timeout = 5;
		
		/**
		 * Constructor
		 * @param string $sitekey optional sitekey to use with request,
		 *		defaults to the value of $_SERVER['SERVER_NAME'].
		 * @param string $method the method to grab url contents, eiher
		 * 		curl or fopen (for file_get_contents).
		 */
		function __construct($site_key = null, $method = "fopen"){
			if($site_key){
				$this->request_site_key = rawurlencode($site_key);
			} else {
				$this->request_site_key = rawurlencode($_SERVER['SERVER_NAME']);
			}
			$this->request_method = $method;
			$version = explode('.', phpversion());
			if($version[0] >= 5 && $version[1] >= 3){
				$this->json_errors_enabled = True;
			}
		}
		
		/**
		 * Destructor
		 * Just cleans up the cURL session if we used it
		 */
		function __destruct(){
			if($this->curl_inst)
				curl_close($this->curl_inst);
		}
		
		/**
		 * Generic get contents method to use either curl or file_get_contents
		 * @param string $request_url url of the file to get contents of
		 * @return string the string contents of the file/url given as param
		 */
		private function get_contents($request_url){
			if ($this->request_method == "fopen"){
				return file_get_contents($request_url);
			} else if ($this->request_method = "curl"){
				if(!$this->curl_inst){
					$this->curl_inst = curl_init();
					curl_setopt ($this->curl_inst, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt ($this->curl_inst, CURLOPT_HEADER, 0);
					curl_setopt ($this->curl_inst, CURLOPT_CONNECTTIMEOUT, $this->curl_timeout);
				}
				curl_setopt ($this->curl_inst, CURLOPT_URL, $request_url);
				return curl_exec($this->curl_inst);
			} else {
				// Invalid request method
				trigger_error("Request method must be one of fopen or curl");
				return null;
			}
		}
		
		/**
		 * Helper function to eliminate reptition of setting the last response
		 * and the json_error and returning the object
		 * @param object $response_object the thing to set as the last response
		 * @return object either the response object or null if error
		 */
		private function set_and_ret($response_object){
			if ($this->json_errors_enabled){
				$json_error = json_last_error();
				if ($json_error == JSON_ERROR_NONE){
					$this->last_json_error = $json_error;
					$this->last_response = $response_object;
					return $response_object;
				} else {
					$this->last_json_error = $json_error;
					$this->last_response = null;
					return null;
				}
			}
			else {
				if ($response_object == null){
					$this->last_response = null;
					return null;
				} else {
					$this->last_response = $response_object;
					return $response_object;
				}
			}
		}
		
		/**
		 * Returns the deserialized character data object from the info
		 * provided to the function.
		 * @param string $name character name
		 * @param string|int $code 3 digit character code
		 * @param string $region optional region information, defaults to US
		 * @return object the deserialized character data
		 */
		public function get_character_data($name, $code, $region = "us"){
			$request_url = $this->site_address.
							rawurlencode($region)."/".
							rawurlencode($name)."$".
							rawurlencode($code).
							".json?appKey=".$this->request_site_key;
			$this->last_request = $request_url;
			$response = $this->get_contents($request_url);
			$response_object = json_decode($response);
			return $this->set_and_ret($response_object);
		}
		
		/**
		 * Gets map usage data from sc2ranks.com
		 * @param int $map_id the map id to ge data for
		 * @return object deserialized map usage info
		 */
		public function get_map_data($map_id){
			$request_url = $this->map_request_address.
				rawurlencode($map_id).
				".json?appKey=".$this->request_site_key;
			$this->last_request = $request_url;
			$response = $this->get_contents($request_url);
			$response_object = json_decode($response);
			return $this->set_and_ret($response_object);
		}
		
		/**
		 * Returns the latest map usage statistics
		 * @param object $response_object optional response object, defaults
				to the last valid response
		 * @return $object the deserialized team ranking object
		 */
		public function get_latest_map_usage($response_object = null){
			if($response_object == null){
				if($this->last_response != null && isset($this->last_response->teams)){
					// We shouldn't have team for map data so this is wrong
					return null;
				}
				else {
					$response_object = $this->last_response;
				}
			}
			if (isset($response_object->teams))
				return null;
			$latest_date = null;
			$latest_date_value = 0;
			foreach ($response_object as $date => $value){
				if($date > $latest_date){
					$latest_date = $date;
					$latest_date_value = $value;
				}
			}
			return array("date" => $latest_date, "value" => $latest_date_value);
		}
		
		/**
		 * Returns the deserialized character data object from the info
		 * provided to the function.
		 * @param string $name character name
		 * @param string $subtype the subtype of the profile search; division, points, etc.
		 * @param string $value the value corresponding to the subtype; Thor Xi, 1700, etc.
		 * @param string $type optional type information, defaults to 1v1 team
		 * @param string $region optional region information, defaults to US
		 * @return object the deserialized character data
		 */
		public function get_character_data_by_profile($name, $subtype, $value, $type = "1t", $region = "us"){
			$request_url = $this->profile_search_address.
							rawurlencode($region)."/".
							rawurlencode($name)."/".
							rawurlencode($type)."/".
							rawurlencode($subtype)."/".
							rawurlencode($value).
							".json?appKey=".$this->request_site_key;
			$this->last_request = $request_url;
			$response = $this->get_contents($request_url);
			$response_object = json_decode($response);
			return $this->set_and_ret($response_object);
		}
		
		/**
		 * Returns the deserialized character data object from the info
		 * provided to the function.
		 * @param string $name character name
		 * @param string|int $bnet bnet ID code
		 * @param string $region optional region information, defaults to US
		 * @return object the deserialized character data
		 */
		public function get_character_data_by_bnet($name, $bnet, $region = "us"){
			$request_url = $this->site_address.
							rawurlencode($region)."/".
							rawurlencode($name)."!".
							rawurlencode($bnet).
							".json?appKey=".$this->request_site_key;
			$this->last_request = $request_url;
			$response = $this->get_contents($request_url);
			$response_object = json_decode($response);
			return $this->set_and_ret($response_object);
		}
		
		/**
		 * Returns the team ranking object for a particular bracket.
		 * @param object $response_object optional response object, defaults
				to the last valid response
		 * @param int $bracket optional bracket number, defaults to 1, a bracket
				of 1 will return 1v1 team record, 2 will return 2v2, etc.
		 * @return $object the deserialized team ranking object
		 */
		public function get_bracket_data($response_object = null, $bracket = 1){
			$returnArray = null;
			if($response_object == null){
				if($this->last_response != null && isset($this->last_response->teams)){
					$response_object = $this->last_response;
				}
				else {
					return null;
				}
			}
			if (!isset($response_object->teams))
				return null;
			foreach ($response_object->teams as $team){
				if ($team->bracket == $bracket){
					$returnArray[] = $team;
				}
			}
			return $returnArray;
		}
		
		/**
		 * Returns the last json error if supported, otherwise returns null
		 * @return int|object the last_json_error status code
		 * @link http://www.php.net/manual/en/function.json-last-error.php 
		 *		json_last_error documentation
		 */
		public function json_last_error(){
			if($this->json_errors_enabled){
				return $this->last_json_error;
			} else {
				return null;
			}
		}
		
		/**
		 * Returns the last request url string
		 * @return string the last requested url
		 */
		public function last_request(){
			return $this->last_request;
		}
		
		/**
		 * Returns the last repsonse object
		 * @return object the last deserialized response object
		 */
		public function last_response(){
			return $this->last_response;
		}
		
		/**
		 * Returns whether or not json_last_error is enabled
		 * @return boolean
		 */
		public function is_json_last_error_enabled(){
			return $this->json_errors_enabled;
		}
	}
?>
