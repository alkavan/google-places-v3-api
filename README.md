GooglePlacesAPI_V3
==================

Google Places API Version 3 Client

## Usage:
	// Init
	$gp = new Finder(Finder::OUTPUT_TYPE_JSON, 'your server side key');

	// Search and get details
	$gp->search($query, new Location(32.0758383, 34.7815936), 5000);
	$gp->details($place);
	$gp->autocomplete($query);
	
## TODO:
+	Support XML format as well
+	Create class for result
