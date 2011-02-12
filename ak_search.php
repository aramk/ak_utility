<?php

/*

Search Functions
Developed by: Aram Kocharyan
Version: 0.1
Date: 22th Jan 2011
URL: http://ak.net84.net/
Email: akarmenia@gmail.com

DESCRIPTION:
    Functions used to search for occurrences in primitive types.
    
*/

// SEARCH IN ARRAYS

function occurrences_func($str, $array) {
	if (!valid_string($str)) {
		return;
	} else if (!valid_array($array)) {
		return 0;
	}
	// case insensitive
	$str_lower = strtolower($str);

	$occurrences = 0;	
	// for each item in array
	foreach ($array as $item) {
		$item = strtolower($item);
		$occurrences += substr_count($str_lower, $item);
	}
	return $occurrences;
}

function occurrences($str, $array) {
	if ( valid_string($str) && valid_array($array) ) {
		$occurrences = occurrences_func($str, $array);
		return "<br/>(" . $occurrences . " Occurrence". (($occurrences == 1) ? "" : "s") .")";
	}
}

// SEARCH IN STRINGS

function str_in_str($haystack, $needle) {
	return (strpos($haystack, $needle) !== FALSE);
}

?>
