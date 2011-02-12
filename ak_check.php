<?php

/*

Check Functions
Developed by: Aram Kocharyan
Version: 0.3
Date: 10th Feb 2011
URL: http://ak.net84.net/
Email: akarmenia@gmail.com

DESCRIPTION:
    Functions used to check validity and emptiness. Ability to check booleans and create errors
    using ak_error.php.
    
*/

require_once('ak_error.php');

/*  Checks if the boolean keys given in $args evaluate to TRUE, if not, an error is created with the
    value in $args as the error string. This is useful for checking input arguments for any 
    function. */
function check_args($args, $errno = E_USER_ERROR, $lookup_function = NULL) {
    // If we are not given valid input
    if ( !valid_array($args) ) {
        return FALSE;
    }
    // If we are not given a lookup function, use the caller of check_args
    if ( !valid_string($lookup_function) ) {
	    $lookup_function = get_caller();
	}
    foreach ($args as $key=>$value) {
        // Check each boolean key, which are evaluated as 0 or 1 when creating the $args array
        if ( is_numeric($key) ) {
            // If a check fails, return FALSE, otherwise continue checking the other keys
            if ( !check((bool) $key, $value, $errno, $lookup_function) ) {
                return FALSE;
            }
        }
    }
    // At this point, all the keys are satisfied as TRUE
    return TRUE;
}

/* Checks if $boolean is TRUE, if not, an error is created with the given details. */
function check($boolean, $errstr = '', $errno = E_USER_ERROR, $lookup_function = NULL) {
    // Only allows booleans to be checked
    if ( !is_bool($boolean) ) {
        error('Check must be invoked on a boolean');
        return FALSE;
    }
    // If we are not given a lookup function, use the caller of check
	if ( !valid_string($lookup_function) ) {
	    $lookup_function = get_caller();
	}
	// Create an error when the check fails
	if ($boolean == FALSE) {
		error($errstr, $errno, $lookup_function);
	}
	return $boolean;
}

// Derived functions for specific use of check_args()

function check_args_error($args) {
    return check_args($args, E_USER_ERROR, get_caller());
}

function check_args_warning($args) {
    return check_args($args, E_USER_WARNING, get_caller());
}

function check_args_notice($args) {
    return check_args($args, E_USER_NOTICE, get_caller());
}

// Derived functions for specific use of check()

function check_error($boolean, $errstr = '') {
	return check($boolean, $errstr, E_USER_ERROR, get_caller());
}

function check_warning($boolean, $errstr = '') {
	return check($boolean, $errstr, E_USER_WARNING, get_caller());
}

function check_notice($boolean, $errstr = '') {
	return check($boolean, $errstr, E_USER_NOTICE, get_caller());
}

// Empty checks for primative types

function empty_array($array) {
	return is_array($array) && empty($array);
}

function empty_string($string) {
	return is_string($string) && empty($string);
}

function non_empty_array($array) {
	return is_array($array) && !empty($array);
}

function non_empty_string($string) {
	return is_string($string) && !empty($string);
}

// Aliases

function valid_array($array) {
	return non_empty_array($array);
}

function valid_string($string) {
	return non_empty_string($string);
}

?>
