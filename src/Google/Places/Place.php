<?php

namespace Google\Places;

class Place
{
	/**
	 * @var bool Indicates if current object is of type PlaceDetails
	 */
	public $is_details = FALSE;

	public $icon;
	public $id;
	public $name;
	public $rating;
	public $reference;
	public $types;
	public $vicinity;
	public $static_maps_url;


	public function __construct(array $place)
	{
		$this->geometry = new Location(
			$place['geometry']['location']['lat'],
			$place['geometry']['location']['lng']
		);

		$this->icon      = $place['icon'];
		$this->id        = $place['id'];
		$this->name      = $place['name'];
		$this->rating    = isset($place['rating']) ? $place['rating'] : NULL;
		$this->reference = $place['reference'];
		$this->types     = $place['types'];
		$this->vicinity  = isset($place['vicinity']) ? $place['vicinity'] : NULL;

		$this->create_static_maps_url();
	}

	protected function create_static_maps_url()
	{
		// Map types <roadmap/terrain>
		$position = $this->geometry->get_position();
		$this->static_maps_url = 'http://maps.googleapis.com/maps/api/staticmap?'
				."center={$position}&zoom=15&size=300x300&maptype=terrain&sensor=false"
				."&markers=color:blue|Clabel:S|{$position}";
	}
}