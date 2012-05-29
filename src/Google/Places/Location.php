<?php

namespace Google\Places;

class Location
{
	public $lat;
	public $lng;

	public function __construct($lat, $lon)
	{
		if(empty($lat) OR ! is_numeric($lat))
		{
			throw new Exception('<Location::lat> must not be empty and should be numeric');
		}

		$this->lat = $lat;

		if(empty($lon) OR ! is_numeric($lon))
		{
			throw new Exception('<Location::lon> must not be empty and should be numeric');
		}

		$this->lng = $lon;
	}

	public function get_position()
	{
		return $this->lat.','.$this->lng;
	}
}