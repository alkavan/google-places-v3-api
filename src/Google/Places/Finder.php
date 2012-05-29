<?php
namespace Google\Places;

/**
 * Search Google Places (experimental API)
 *
 * @todo Support XML format as well
 * @todo Create class for result
 *
 * @version 0.1.1
 * @author Igal Alkon
 */
class Finder
{
	const OUTPUT_TYPE_JSON = 'json';
	const OUTPUT_TYPE_XML  = 'xml';

	const STATUS_OK               = 'OK';
	const STATUS_ZERO_RESULTS     = 'ZERO_RESULTS';
	const STATUS_OVER_QUERY_LIMIT = 'OVER_QUERY_LIMIT';
	const STATUS_REQUEST_DENIED   = 'REQUEST_DENIED';
	const STATUS_INVALID_REQUEST  = 'INVALID_REQUEST';
	const STATUS_UNKNOWN_ERROR    = 'UNKNOWN_ERROR';

	/*
	 * API Actions
	 */
	const TYPE_SEARCH       = 'search';
	const TYPE_DETAILS      = 'details';
	const TYPE_AUTOCOMPLETE = 'autocomplete';

	/**
	 * Google Places server side API key
	 * @var string
	 */
	protected $_api_key  = '';

	protected $_api_output_type;
	protected $_api_request_type;

	/**
	 * Google Places API base url
	 * @var string
	 */
	protected $_api_url     = 'https://maps.googleapis.com/maps/api/place';
	protected $_api_status  = '';
	protected $_api_search_result  = array();
	protected $_api_details_result = array();
	protected $_api_autocomplete_result = array();
	protected $_api_html_attributions = array();

	/**
	 * Default constructor
	 *
	 * @param $output_type
	 * @param $api_key
	 * @throws Exception
	 */
	public function __construct($output_type, $api_key)
	{
		$output_types = $this->get_output_types();

		if ( ! isset($output_types[$output_type]))
		{
			throw new Exception("Output type: {$output_type}, valid option are: " . print_r($output_types, TRUE));
		}

		$this->_api_key         = $api_key;
		$this->_api_output_type = $output_type;
	}

	/**
	 * Build API call and forward to curl_call()
	 *
	 * @param $request_type array
	 * @param $query Query
	 * @throws Exception
	 */
	private function api_call($request_type, Query $query)
	{
		$request_url = $this->_api_url;

		switch($request_type)
		{
			case self::TYPE_SEARCH:
				$params = $query->build_search_params_array();
				break;
			case self::TYPE_DETAILS:
				$params = $query->build_details_params_array();
				break;
			case self::TYPE_AUTOCOMPLETE:
				$params = $query->build_autocomplete_params_array();
				break;
			default:
				$request_types = $this->get_request_types();
				throw new Exception("Request type: {$request_type}, valid option are: " . print_r($request_types, TRUE));
				break;
		}

		$base_url = $request_url.'/'.$request_type.'/'.$this->_api_output_type;

		$result = $this->curl_call($this->build_request_url($base_url, $params));

		$this->parse_response($result, $request_type);
	}

	/**
	 * Build request URL
	 *
	 * @param $base_url
	 * @param $params
	 * @return string
	 */
	private function build_request_url($base_url, $params)
	{
		return $base_url . '?' . http_build_query($params);
	}

	/**
	 * Parse response from Places API
	 *
	 * @param $res
	 * @param $request_type array
	 * @throws Exception
	 */
	private function parse_response($res, $request_type)
	{
		$res_arr  = json_decode($res, TRUE);
		$json_err = json_last_error();

		if($json_err)
		{
			throw new Exception("error in parsing JSON from web service response: {$json_err}");
		}

		switch($request_type)
		{
			case self::TYPE_SEARCH:
				$this->_api_search_result = $res_arr['results'];
				$this->_api_html_attributions = $res_arr['html_attributions'];
				break;
			case self::TYPE_DETAILS:
				$this->_api_details_result = $res_arr['result'];
				$this->_api_html_attributions = $res_arr['html_attributions'];
				break;
			case self::TYPE_AUTOCOMPLETE:
				$this->_api_autocomplete_result = $res_arr['predictions'];
				break;
			default:
				$request_types = $this->get_request_types();
				throw new Exception("Request type: {$request_type}, valid option are: " . print_r($request_types, TRUE));
				break;
		}

		$this->_api_status = $res_arr['status'];

		switch($this->_api_status)
		{
			case self::STATUS_REQUEST_DENIED:
			case self::STATUS_INVALID_REQUEST:
			case self::STATUS_UNKNOWN_ERROR:
				throw new Exception("Bad request to API, got response with status: {$this->_api_status}");
		}
	}

	/**
	 * Parse results array from from search action.
	 *
	 * @return array
	 */
	private function parse_search_result()
	{
		$results = array();

		if($this->_api_status == self::STATUS_OK AND ! empty($this->_api_search_result))
		{
			foreach($this->_api_search_result as $place)
			{
				$results[] = new Place($place);
			}
		}

		return $results;
	}

	/**
	 * Parse results array from from autocomplete action.
	 *
	 * @return array
	 */
	private function parse_autocomplete_result()
	{
		$results = array();
		if($this->_api_status == self::STATUS_OK AND ! empty($this->_api_autocomplete_result))
		{
			foreach($this->_api_autocomplete_result as $prediction)
			{
				$results[] = new Prediction($prediction);
			}
		}

		return $results;
	}

	/**
	 * Parse Google Places API details result
	 *
	 * @return PlaceDetails
	 * @throws Exception
	 */
	private function parse_details_result()
	{
		if($this->_api_status == self::STATUS_OK AND ! empty($this->_api_details_result))
		{
			return new PlaceDetails($this->_api_details_result);
		}
		throw new Exception("Could not parse details result, it might be empty, check Finder::_api_status ");
	}

	/**
	 * Array of output types for Places API
	 *
	 * @static
	 * @return array
	 */
	private static function get_output_types()
	{
		return array(
			self::OUTPUT_TYPE_JSON => self::OUTPUT_TYPE_JSON,
			self::OUTPUT_TYPE_XML  => self::OUTPUT_TYPE_XML,
		);
	}

	/**
	 * Array of actions for Places API
	 *
	 * @static
	 * @return array
	 */
	public static function get_request_types()
	{
		return array(
			self::TYPE_SEARCH       => self::TYPE_SEARCH,
			self::TYPE_DETAILS      => self::TYPE_DETAILS,
			self::TYPE_AUTOCOMPLETE => self::TYPE_AUTOCOMPLETE,
		);
	}

	/**
	 * CURL based call to API
	 *
	 * @param string $url
	 * @return string
	 */
	private function curl_call($url)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		$body = curl_exec($ch);
		curl_close($ch);

		return $body;
	}

	/**
	 * Search for location
	 *
	 * @param $query_string
	 * @param Location $location
	 * @param int $radius
	 * @return array Place|PlaceDetails
	 */
	public function search($query_string, Location $location, $radius = 500)
	{
		$query = new Query($this->_api_key);
		$query
			->set_name($query_string)
			->set_location($location)
			->set_radius($radius);

		$this->api_call(self::TYPE_SEARCH, $query);

		return $this->parse_search_result();
	}

	/**
	 * Get details about Place
	 *
	 * @param Place $place
	 * @return array|PlaceDetails
	 */
	public function details(Place $place)
	{
		$query = new Query($this->_api_key);
		$query->set_reference($place->reference);
		$this->api_call(self::TYPE_DETAILS, $query);

		return $this->parse_details_result();
	}
	/**
	* Search for location
	*
	* @param $query_string
	* @return array Place|PlaceDetails
	*/
	public function autocomplete($query_string)
	{
		$query = new Query($this->_api_key);
		$query->set_input($query_string);

		$this->api_call(self::TYPE_AUTOCOMPLETE, $query);

		return $this->parse_autocomplete_result();
	}

	public function append_details(array $gp_search_result)
	{
		$gp_search_details = array();

		/**
		 * @var $place \Google\Places\Place
		 */
		foreach($gp_search_result as $place)
		{
			$gp_search_details[] = $this->details($place);
		}

		return $gp_search_details;
	}
}