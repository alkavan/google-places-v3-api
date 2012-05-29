<?php
namespace Google\Places;

/**
 * Prediction Class
 */
class Prediction
{
	public $id;
	public $reference;
	public $description;
	public $matched_substrings;
	public $terms;
	public $types;


	public function __construct(array $prediction)
	{
		$this->id                 = $prediction['id'];
		$this->reference          = $prediction['reference'];
		$this->description        = $prediction['description'];
		$this->matched_substrings = $prediction['matched_substrings'];
		$this->terms              = $prediction['terms'];
		$this->types              = $prediction['types'];
	}
}
