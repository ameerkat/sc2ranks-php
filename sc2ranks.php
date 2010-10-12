<?php
/**
 * This file contains a basic class that handles interaction when generating
 * requests and pulling data back from the sc2ranks.com API. Uses the json
 * interface and the built in PHP json deserializer.
 * @author Ameer Ayoub <ameer.ayoub@gmail.com>
 * @version 0.2
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
		 * Whether or not json errors are enabled, available in php 5.3.0
		 * or greater.
		 * @access private
		 * @var boolean
		 * @see is_json_last_error_enabled()
		 */
		private $json_errors_enabled = False;
		
		/**
		 * Constructor
		 * @param string $sitekey optional sitekey to use with request,
				defaults to the value of $_SERVER['SERVER_NAME'].
		 */
		function __construct($site_key = null){
			if($site_key){
				$this->request_site_key = rawurlencode($site_key);
			} else {
				$this->request_site_key = rawurlencode($_SERVER['SERVER_NAME']);
			}
			$version = explode('.', phpversion());
			if($version[0] >= 5 && $version[1] >= 3){
				$this->json_errors_enabled = True;
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
			$response = file_get_contents($request_url);
			$response_object = json_decode($response);
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
				if($this->last_response != null){
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
