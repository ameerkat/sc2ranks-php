# sc2ranks-php
## Description
A Simple PHP API for accessing data from [sc2ranks](http://www.sc2ranks.com/). Still needs some work, let me know if you have any contributions to this project.

## Usage
See example.php for sample script

1. Include sc2ranks.php
2. Create a new request. You can pass in your app key if you want. If not then
your app key will be set to the value of $_SERVER['server_name']
	
	$request = new sc2ranks_request( _"your site name"_);
	
3. Call the get_character_data method of the request object, passing in the
character name and character code.

	$request->get_character_data( _"character name"_, _character code_);
	
4. Call get_bracket_data() to get an object containing a particular bracket
record, defaulting to the 1v1 record of the last requested character.

	$request->get_bracket_data();

## TODO
* Add support for batch requests
* Add other helper functions for processing returned data
* Add documentation somewhere for the returned object structure
* Add link to API site, other API's
* Licensing
* Unit Tests w/phpUnit
