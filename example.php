<?php
	// Turn on error reporting
	ini_set('display_errors',1);
	error_reporting(E_ALL);
	
	include("sc2ranks.php");
	
	echo "<pre>\n";
	
	// Create a new request object
	// If we want to use curl
	//$request = new sc2ranks_request("github.com/ameerkat/sc2ranks-php", "curl");
	$request = new sc2ranks_request("github.com/ameerkat/sc2ranks-php");
	// Request character data for a sample player
	echo "===\nPlayer Data (meerkat#678): \n===\n";
	print_r($request->get_character_data("meerkat", 678));
	// Get the bracket data for 1v1 for the last player queried
	$bracket = $request->get_bracket_data();
	echo "\n===\n1v1 Record: \n===\n";
	print_r($bracket[0]);
	// Get a particular attribute of the class
	echo "\n===\nRatio: \n===\n";
	print_r($bracket[0]->ratio);
	// Get a map's data
	echo "\n\n===\nMap Data (17525): \n===\n";
	print_r($request->get_map_data(17525));
	echo "\n===\nGet Latest Map Usage Stats: \n===\n";
	print_r($request->get_latest_map_usage());
	//echo "\n\n===\nSorted Map Data: \n===\n";
	//print_r($request->get_sorted_map_data(17525));
	
	echo "</pre>";
?>
