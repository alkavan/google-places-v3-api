<?php

namespace Google\Places;
/**
 * Google Places API Query Class
 *
 * Autocomplete Types
 *
 * Place Types:
 * Tou may restrict results from a Place Autocomplete request to be of a certain type by passing a types parameter.
 * Currently, two types and two type collections are supported:
 * <geocode> instructs the Place service to return only geocoding (address) results.
 * 		Generally, you use this request to disambiguate results where the location specified may be indeterminate.
 * <establishment> instructs the Place service to return only business results.
 *
 * the (<regions>) type collection instructs the Place service to return any result matching the following types:
 * 		<locality>
 * 		<sublocality>
 * 		<postal_code>
 * 		<country>
 * 		<administrative_area1>
 * 		<administrative_area2>
 *
 * the (<cities>) type collection instructs the Place service to return results that match
 * either <locality> or <administrative_area3>.
 */
class Query
{
	/**
	 * Your application's API key. This key identifies your application for purposes of quota management and so that
	 * Places added from your application are made immediately available to your app.
	 * Visit the APIs Console to create an API Project and obtain your key.
	 */
	const PARAM_KEY = 'key';

	/**
	 * The latitude/longitude around which to retrieve Place information.
	 * This must be specified as <latitude,longitude>.
	 */
	const PARAM_LOCATION = 'location';

	/**
	 * Defines the distance (in meters) within which to return Place results.
	 * The maximum allowed radius is 50 000 meters.
	 * Note that radius must not be included if
	 * 'rankby=distance' (described under Optional parameters below) is specified.
	 */
	const PARAM_RADIUS     = 'radius';
	const PARAM_RADIUS_MAX = 50000;

	/**
	 * Indicates whether or not the Place request came from a device using a location sensor (e.g. a GPS)
	 * to determine the location sent in this request. This value must be either <true> or <false>.
	 */
	const PARAM_SENSOR  = 'sensor';

	const PARAM_SENSOR_TRUE  = 'true';
	const PARAM_SENSOR_FALSE = 'false';

	/**
	 * A term to be matched against all content that Google has indexed for this Place,
	 * including but not limited to name, type, and address,
	 * as well as customer reviews and other third-party content.
	 */
	const PARAM_KEYWORD  = 'keyword';

	/**
	 * The language code, indicating in which language the results should be returned,
	 * if possible. See the <list of supported languages> and their codes.
	 * Note that we often update supported languages so this list may not be exhaustive.
	 *
	 * @link http://spreadsheets.google.com/pub?key=p9pdwsai2hDMsLkXsoM05KQ&gid=1
	 */
	const PARAM_LANGUAGE = 'language';

	/**
	 * A term to be matched against the names of Places.
	 * Results will be restricted to those containing the passed name value.
	 */
	const PARAM_NAME = 'name';

	/**
	 * Specifies the order in which results are listed. Possible values are:
	 *
	 * <prominence> (default)
	 * This option sorts results based on their importance.
	 * Ranking will favor prominent places within the specified area.
	 * Prominence can be affected by a Place's ranking in Google's index,
	 * the number of check-ins from your application, global popularity, and other factors.
	 *
	 * <distance>
	 * This option sorts results in ascending order by their distance from the specified location.
	 * A radius should not be supplied, and bounds is not supported.
	 * One or more of keyword, name, or types is required.
	 */
	const PARAM_RANKBY = 'rankby';

	// PARAM_RANKBY Options:
	const PARAM_RANKBY_PROMINENCE = 'prominence';
	const PARAM_RANKBY_DISTANCE   = 'distance';

	/**
	 * Restricts the results to Places matching at least one of the specified types.
	 * Types should be separated with a pipe symbol (type1|type2|etc).
	 * See the list of supported types.
	 *
	 * @link https://developers.google.com/maps/documentation/places/supported_types
	 */
	const PARAM_TYPES = 'types';

	/**
	 * Details parameters
	 */
	const PARAM_REFERENCE = 'reference';

	/**
	 * The character position in the input term at which the service uses text for predictions.
	 * For example, if the input is 'Googl' and the completion point is 3, the service will match on 'Goo'.
	 * The offset should generally be set to the position of the text caret.
	 * If no offset is supplied, the service will use the entire term.
	 */
	const PARAM_OFFSET = 'offset';

	/**
	 * Autocomplete parameters
	 */

	/**
	 * The text string on which to search.
	 * The Place service will return candidate matches based
	 * on this string and order results based on their perceived relevance.
	 */
	const PARAM_INPUT = 'input';

	/**
	 * A grouping of places to which you would like to restrict your results.
	 * Currently, you can use components to filter by country.
	 * The country must be passed as a two character, ISO 3166-1 Alpha-2 compatible country code.
	 * For example: components=country:fr would restrict your results to places within France.
	 */
	const PARAM_COMPONENTS = 'components';

	private $_api_key = '';

	/**
	 * @var Location
	 */
	protected $location;

	protected $radius;
	protected $keyword;
	protected $language;
	protected $name;
	protected $rankby;
	protected $reference;
	protected $sensor;
	protected $input;

	/**
	 * @see Finder::_api_key
	 * @param $api_key
	 */
	public function __construct($api_key)
	{
		$this->_api_key = $api_key;
	}

	/**
	 * Build url parameters for search action
	 *
	 * @return array
	 * @throws Exception
	 */
	public function build_search_params_array()
	{
		$location = $this->get_location();

		if( ! $location)
		{
			throw new Exception("You cannot create search request without setting location.");
		}

		$required_params = array(
			Query::PARAM_KEY      => $this->_api_key,
			Query::PARAM_LOCATION => $location->get_position(),
			Query::PARAM_RADIUS   => $this->get_radius(),
			Query::PARAM_SENSOR   => $this->get_sensor(),
		);

		$optional_params = array();
		$optional_params[Query::PARAM_KEYWORD]  = $this->get_keyword();
		$optional_params[Query::PARAM_LANGUAGE] = $this->get_language();
		$optional_params[Query::PARAM_NAME]     = $this->get_name();
		$optional_params[Query::PARAM_RANKBY]   = $this->get_rankby();

		$params = array_merge($required_params, $optional_params);

		return $params;
	}
	/**
	 * Build url parameters for details action
	 *
	 * @return array
	 * @throws Exception
	 */
	public function build_details_params_array()
	{
		$required_params = array(
			Query::PARAM_KEY       => $this->_api_key,
			Query::PARAM_REFERENCE => $this->get_reference(),
			Query::PARAM_SENSOR    => $this->get_sensor(),
		);

		$optional_params = array();
		$optional_params[Query::PARAM_LANGUAGE] = $this->get_language();

		$params = array_merge($required_params, $optional_params);

		return $params;
	}
	/**
	 * Build url parameters for details action
	 *
	 * @return array
	 */
	public function build_autocomplete_params_array()
	{
		$required_params = array(
			Query::PARAM_KEY      => $this->_api_key,
			Query::PARAM_SENSOR   => $this->get_sensor(),
			Query::PARAM_INPUT    => $this->get_input(),
		);

		$optional_params = array();
		$optional_params[Query::PARAM_KEYWORD]  = $this->get_keyword();
		$optional_params[Query::PARAM_LANGUAGE] = $this->get_language();
		$optional_params[Query::PARAM_NAME]     = $this->get_name();
		$optional_params[Query::PARAM_RANKBY]   = $this->get_rankby();

		$params = array_merge($required_params, $optional_params);

		return $params;
	}

	/**
	 * Set 'keyword' parameter
	 * used to search by keywords.
	 *
	 * @param $keyword
	 * @return Query
	 */
	public function set_keyword($keyword)
	{
		$this->keyword = $keyword;
		return $this;
	}

	/**
	 * Set 'language' parameter
	 * @param $language
	 * @return Query
	 */
	public function set_language($language)
	{
		$this->language = $language;
		return $this;
	}

	/**
	 * Set 'location' parameter
	 * center of search radius
	 *
	 * @param Location $location
	 * @return Query
	 */
	public function set_location(Location $location)
	{
		$this->location = $location;
		return $this;
	}

	/**
	 * Set 'name' parameter
	 * used to search by place name.
	 *
	 * @param $name
	 * @return Query
	 */
	public function set_name($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Set 'radius' parameter
	 *
	 * @param $radius
	 * @throws Exception
	 * @return Query
	 */
	public function set_radius($radius)
	{
		if($radius >= 0 AND $radius <= self::PARAM_RADIUS_MAX)
		{
			$this->radius = $radius;
			return $this;
		}
		else
		{
			$max_radius = self::PARAM_RADIUS_MAX;
			throw new Exception("radius can be between 0 and {$max_radius}");
		}
	}

	/**
	 * Set 'rankby' parameter
	 *
	 * @param $rankby
	 * @return Query
	 */
	public function set_rankby($rankby)
	{
		$this->rankby = $rankby;
		return $this;
	}

	/**
	 * Set 'reference' paremeter (required for details action)
	 *
	 * @param $reference
	 * @return Query
	 */
	public function set_reference($reference)
	{
		$this->reference = $reference;
		return $this;
	}

	/**
	 * Set 'sensor' paremeter (required for all actions
	 * )
	 * @param $sensor
	 * @throws Exception
	 * @return Query
	 */
	public function set_sensor($sensor)
	{
		if($sensor != self::PARAM_SENSOR_TRUE && $sensor != self::PARAM_SENSOR_TRUE)
		{
			throw new Exception("you cannot set 'sensor' parameter with value '{$sensor}'");
		}
		$this->sensor = $sensor;
		return $this;
	}

	/**
	 * Set 'input' paremeter (required for autocomplete action)
	 *
	 * @param $input
	 * @return Query
	 */
	public function set_input($input)
	{
		$this->input = $input;
		return $this;
	}

	public function get_keyword()
	{
		return $this->keyword;
	}

	public function get_language()
	{
		return $this->language;
	}

	/**
	 * @return \Google\Places\Location
	 */
	public function get_location()
	{
		return $this->location;
	}

	public function get_name()
	{
		return $this->name;
	}

	public function get_radius()
	{
		return $this->radius;
	}

	public function get_rankby()
	{
		return $this->rankby;
	}

	public function get_reference()
	{
		return $this->reference;
	}

	public function get_sensor()
	{
		return ($this->sensor) ? $this->sensor : self::PARAM_SENSOR_FALSE;
	}

	public function get_input()
	{
		return (string)$this->input;
	}
}
