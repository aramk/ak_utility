<?php

/*

Array Functions
Developed by: Aram Kocharyan
Version: 0.3
Date: 11th Feb 2011
URL: http://ak.net84.net/
Email: akarmenia@gmail.com

DESCRIPTION:
    Functions used for array manipulation.
    
*/

// ARRAYS MISC

require_once('ak_check.php');

define('APPEND_ALL_KEYS', 1);

function max_index($array) {
    if ( valid_array($array) ) {
        return count($array) - 1;
    } else {
        return 0;
    }
}

function last_component($filepath) {
	if ( is_string($filepath) ) {
		$array = preg_split("#[\\\\/]#", $filepath);
		return end($array);
	}
}

/*  Sorts a key => value array based on the $keys array, anything left is sorted alphabetically.
    All keys are considered strings, values can be mixed values. */
function array_sort_keys($array, $keys) {
    if ( valid_array($array) ) {
        if ( !valid_array($keys) ) {
            // $keys are invalid, we will just sort with ksort()
            return ksort($array);
        }
    } else {
        // $array is not a valid array
        return $array;
    }
    $sorted_array = array();

    foreach ($keys as $key) {
        // We can only sort the array by string keys
        if ( is_string($key) && array_key_exists($key, $array) ) {
            // Sort key => value
            $sorted_array[$key] = $array[$key];
        }
    }
    
    // What is left unsorted is sorted alphabetically and added behind the sorted array
    ksort($array);
    foreach ($array as $key=>$value) {
        if ( !array_key_exists($key, $sorted_array) ) {
            // If a key has not been added, add it now
            $sorted_array[$key] = $array[$key];
        }
    }
    
    return $sorted_array;
}

// Checks if all elements in $array are arrays themselves
function all_elements_are_arrays($array) {
    if ( valid_array($array) ) {
        foreach ($array as $argument) {
            if ( !is_array($argument) ) {
                // If we encounter a non-array element, return FALSE;
                return FALSE;
            }
        }
        // At this point, we have verified all the elements are arrays
        return TRUE;
    } else {
        // $array is not a non-empty array
        return FALSE;
    }
}

/*  Returns an array of elements from a delimiter seperated string, removing all whitespace except
    within the elements. */
function explode_clean($str, $delimiter = ',') {
	if ( valid_string($str) ) {
		// Ensure that the given delimiter does not contain whitespace
		$glue = trim($delimiter);
		// May contain traces of whitespace
		$array = explode($delimiter, $str);
		// Trim all the elements
		for ($i = 0; $i < count($array); $i++) {
		    $array[$i] = trim($array[$i]);
		}
		return $array;
	} else {
		return $str;
	}
}

/*  Merges several arrays while appending given key => value string pairs rather than replacing 
    them.
    
    USAGE:
        array_merge_append( $array1, $array2, $array3, ..., [$keys_to_append] [$glue] );
    
    Uses array_merge_append_main() but in a shorter syntax. You can specify which keys should be
    appended by providing them as a commma seperated string, and override the default glue. This 
    just provides a cleaner syntax to merge-append arrays without having to put them into an array and specify the keys to append in another array.
*/
// TODO: more testing
function array_merge_append() {
    $arguments = func_get_args();
    $arg_num = func_num_args();
    
    // Default parameters
    $glue = ' ';
    $keys_to_append = APPEND_ALL_KEYS;
    
    // Argument number of greater than 2 needed for at least one optional argument
    if ($arg_num > 2) {
        // Which optional arguments are given
        $last_optional = is_string($arguments[$arg_num-1]);
        $second_last_optional = is_string($arguments[$arg_num-2]);
        if ( $last_optional ) {
            if ( $second_last_optional ) {
                $glue = end($arguments);
                array_pop($arguments);
            } else {
                $glue = ' ';
            }
            $keys_to_append = explode_clean( end($arguments) );
            array_pop($arguments);
        }
    } else if ( $arg_num == 1 && is_array($arguments[0]) ) {
        // Only a single array given, can't do anything
        return $arguments[0];
    } else if ($arg_num == 0) {
        // No arguments given, just return an empty array
        return array();
    }
    
    // If all the remaining arguments given are arrays, then merge-append them as usual
    if ( all_elements_are_arrays($arguments) ) {
        return array_merge_append_main($arguments, $keys_to_append, $glue);
    } else {
        // Otherwise we can't merge the arguments, just return the first argument if it's an array
        // or just an empty array
        if ( is_array($arguments[0]) ) {
            return $arguments[0];
        } else {
            return array();
        }
    }
}

/*  Merges several arrays while appending given key => value string pairs rather than replacing 
    them.
    $arrays = Array of arrays containing numeric, string, and key => value pairs.
    $append_keys = Array of strings representing keys in $arrays whose values should be
                   appended/concatenated.
    $glue = The string to place in between appended value strings. */
function array_merge_append_main($arrays, $keys_to_append = APPEND_ALL_KEYS, $glue = ' ') {
	// Merge all the arrays recursively
	if ( valid_array($arrays) ) {	
		$first_array = NULL;
		foreach ($arrays as $array) {
			if ( $first_array == NULL && is_array($array) ) {
				// Find the first array to merge all the others into
				$first_array = $array;
				continue;
			} else if ( $first_array != NULL && valid_array($array) ) {
				$first_array = array_merge_recursive($first_array, $array);
			}
		}
	}
	
	// If $keys_to_append is given as a string, put it into an array
	if ( is_string($keys_to_append) ) {
		$keys_to_append = array($keys_to_append);
	}
	
	// Either append the key values or overwrite them with the value in the last last array
	// (just like merge)
	$keys = array_keys($first_array);
	for ($i = 0; $i < count($keys); $i++) {
	//foreach ($first_array as $key=>$value) {
		$key = $keys[$i];
		$value = $first_array[$key];
		if ( valid_array($value) ) {
			if ( $keys_to_append == APPEND_ALL_KEYS || in_array($key, $keys_to_append) ) {
				// Append the values
				$first_array[$key] = implode($glue, $value);
			} else {
				// Set the value as the value in the last array, same as array_merge()
				$first_array[$key] = end($value);
			}
		}
	}
	
	return $first_array;
}

//$array1 = array(1, 2, 3, "hello" => "1");
//$array2 = array(1, 2, 3, "hello" => "2", 'hellow' => 2);
//$array3 = array(1, 2, 3, "hellow" => "3");

//print_r( array_merge_append($array1, $array2, $array3, 'hello,hellow', ' - ') );

//print_r( array_merge_append_main(array($array1, $array2, $array3) , APPEND_ALL_KEYS, " _ " ) );

?>
