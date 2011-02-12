<?php

/*

Numeric Functions
Developed by: Aram Kocharyan
Version: 0.1
Date: 22th Jan 2011
URL: http://ak.net84.net/
Email: akarmenia@gmail.com

DESCRIPTION:
    Functions used for number manipulation.
    
*/

function bool_to_str($var) {
    if ( is_bool($var) ) {
        return ( ($var) ? "TRUE" : "FALSE" );
    } else {
        return "";
    }
}

function is_strict_numeric($var) {
    return is_int($var) || is_float($var);
}

function disallow_negative($x) {
    if ( is_numeric($x) && $x < 0 ) {
        return 0;
    } else {
        return $x;
    }
}

function disallow_positive($x) {
    if ( is_numeric($x) && $x > 0 ) {
        return 0;
    } else {
        return $x;
    }
}

function percentage($a, $b) {
    return ( ($b == 0) ? 0 : round($a/$b * 100, 2) );
}

?>
