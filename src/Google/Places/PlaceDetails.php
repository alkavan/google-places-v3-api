<?php

namespace Google\Places;

class PlaceDetails extends Place
{
	/**
	 * @see Place
	 */
	public $is_details = TRUE;

	public $address_components;
	public $events;
	public $formatted_address;
	public $formatted_phone_number;
	public $international_phone_number;
	public $url;

	public function __construct(array $place_details)
	{
		parent::__construct($place_details);

		$this->url    = isset($place_details['events']) ? $place_details['url'] : NULL;
		$this->events = isset($place_details['events']) ? $place_details['events'] : NULL;

		$this->address_components = isset($place_details['address_components'])
				? $place_details['address_components'] : NULL;

		$this->formatted_address = isset($place_details['formatted_address'])
				? $place_details['formatted_address'] : NULL;

		$this->formatted_phone_number = isset($place_details['formatted_phone_number'])
				? $place_details['formatted_phone_number'] : NULL;

		$this->international_phone_number = isset($place_details['international_phone_number'])
				? $place_details['international_phone_number'] : NULL;
	}
}