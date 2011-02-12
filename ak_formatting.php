<?php

/*

Formatting Functions
Developed by: Aram Kocharyan
Version: 0.3
Date: 10th Feb 2011
URL: http://ak.net84.net/
Email: akarmenia@gmail.com

DESCRIPTION:
    Functions used to format strings for screen output.
    
*/

require_once('ak_array.php');
require_once('ak_numeric.php');

// General Formatting
define('DASH', '======================================================================');
define('LINE', '______________________________________________________________________');
define('BR', "<br />\n");
define('NL', "\n");
define('HR', '<hr style="border: 1px #CCC; border-style: solid none none none;" />');
define('BOLD_OPEN', "<strong>");
define('BOLD_CLOSE', "</strong>");
define('ITALIC_OPEN', "<em>");
define('ITALIC_CLOSE', "</em>");

// Functional
define('M_RETURN', TRUE); // Return output from function
define('M_PRINT', FALSE); // Print output from function
define('COMMA_DELIMITER', ', ');
define('SHOW_TYPES', TRUE);

// HTML Hex Color Codes
define('NO_COLOR', '');
define('BLACK', '#000');
define('WHITE', '#FFF');
define('RED', '#F00');
define('GREEN', '#0F0');
define('BLUE', '#00F');
define('YELLOW', '#FF0');
define('DARK_GRAY', '#333');
define('GRAY', '#666');
define('LIGHT_GRAY', '#CCC');

/*  Returns a string containing the elements in $array seperated by $glue. If $show_types = TRUE,
    the returned string shows type information for the elements in $array (strings will be in 
    quotations, arrays will be marked "Array ()").
    Similar to implode(), except that it works with numbers, arrays and strings.
*/
// TODO: Regex
function array_to_string($array, $show_types = FALSE, $glue = COMMA_DELIMITER) {
    if ( !valid_array($array) ) {
        return '';
    }

    // If $show_types is TRUE, we must add information about types
    if ($show_types) {
        $string_delimiter = '"';
        $key_value_delimiter = '" => ';
        $array_delimiter_start = 'Array(';
        $array_delimiter_end = ')';
    } else {
        $string_delimiter = $key_value_delimiter = $array_delimiter_start = $array_delimiter_end = '';
    }

    $str = "";
    $keys = array_keys($array);
    for($i = 0; $i < count($keys); $i++) {
        $key = $keys[$i];
        $value = $array[$key];
        
        // For key => value pair, add the ["key" => ] part ONLY if we are showing type info,
        // otherwise we only add the value.
        if ( $show_types && is_string($key) ) {
            $str .= $string_delimiter . $key . $key_value_delimiter;
        }
        // For both numeric and key => value pairs, add the value
        if ( is_numeric($key) || is_string($key) ) {
            if ( is_strict_numeric($value) ) {
                $str .= $value;
            } else if ( is_string($value) ) {
                $str .= $string_delimiter . $value . $string_delimiter;
            } else if ( is_array($value) ) {
                $str .= $array_delimiter_start .
                        array_to_string($value, $show_types, $glue) . 
                        $array_delimiter_end;
            } else if ( is_bool($value) ) {
                // Don't forget boolean values, they normally get treated as integers, but TRUE
                // is printed as 1 and FALSE is not printed at all
                $str .= bool_to_str($value);
            }
        }
        
        if ($i != max_index($keys)) {
            // Append $glue for all but the last item in $array
            $str .= $glue;
        }
    }
    return $str;
}

function yes_or_no($result) {
    return ( ($result) ? "Yes" : "No" );
}

function clean_commas($str) {
	if ( valid_string($str) ) {
		return str_replace(array(",",", "), ", ", $str);
	} else {
		return $str;
	}
}

function bold_search($str, $array) {
	if (empty($str) || !valid_array($array)) {
		return $str;
	}

    foreach ($array as $item) {
        if ( !valid_string($item) ) {
            continue;
        }
        
        $item_array = explode(' ', $item);
        foreach ($item_array as $block) {
            $str = preg_replace('/(' . $block . ')/i', bold_italic('$1'), $str);
        }
    }
    
    return $str;
}

function numeric_superscript($num_str) {
	if (is_numeric($num_str)) {
        $num_str = strval($num_str);
    } else if (is_string($num_str)) {
        // Remove everything that isn't a number
        $num_str = preg_replace('/[^\d]/', '', $num_str);
        if (empty($num_str)) {
            // Guaranteed not to consider empty strings as valid, or 'hello' as '' and then 0
            return;
        }
    } else {
        // Do not accept any other types
        return;
    }
    
    $end_digit = substr($num_str, strlen($num_str) - 1 , 1);
    $end_digit = intval($end_digit);
    
	switch ($end_digit) {
		case 1:
			$num_str_sup = $num_str . superscript("st");
			break;
		case 2:
			$num_str_sup = $num_str . superscript("nd");
			break;
		case 3:
			$num_str_sup = $num_str . superscript("rd");
			break;
		default :
			$num_str_sup = $num_str . superscript("th");
			break;
	}
	return $num_str_sup;
}

function superscript($str) {
	return "<sup>$str</sup>";
}

function list_with_commas($array_of_str) {
	if ( valid_array($array_of_str) ) {
		if ( count($array_of_str) == 1 ) {
			// if only one item
			return $array_of_str[0];
		} else if ( count($array_of_str) == 2 ) {
			// if two items
			return $array_of_str[0] . " and " . $array_of_str[1];
		} else {
			// more than two items
			$str = "";
			// commas for all but last item
			for ($i = 0; $i < count($array_of_str) - 1; $i++) {
				$str .= $array_of_str[$i] . ", ";
			}
			// last item has and
			$str .= " and " . $array_of_str[$i];
			return $str;
		}
	}
}

function bold($str, $color = NO_COLOR) {
    return color(BOLD_OPEN . $str . BOLD_CLOSE, $color);
}

function italic($str) {
    return ITALIC_OPEN . $str . ITALIC_CLOSE;
}

function bold_italic($str) {
	return bold(italic($str));
}

function color($str, $color) {
    if ( is_string($color) ) {
    	return '<span style="color: ' . $color . '">' . $str . '</span>';
    } else {
        return $str;
    }
}

function print_message($header, $body) {
    $message_style = '
        font-family: arial, sans-serif;
        font-size: 12px;
        background: #EEE;
        padding: 5px;
        border: 1px solid #CCC;
        border-radius: 5px;
        -moz-border-radius: 5px;
        float: left;';
    $header_style = '
        font-weight: bold;
        background: #99CCFF;
        color: #1967b4;
        margin:-5px -5px 5px;
        padding: 5px;
        border: 1px solid;
        border-color: #ddeeff #79afe4 #79afe4 #79afe4;
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
        -moz-border-radius-topleft: 5px;
        -moz-border-radius-topright: 5px;';
    
    echo '<div style="' , $message_style , '">' ,
         '<div style="' , $header_style , '">' , $header , "</div>" ,
         $body ,
         '</div>';
}

?>
