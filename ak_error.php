<?php

/*

Error Functions
Developed by: Aram Kocharyan
Version: 0.2
Date: 6th Feb 2011
URL: http://ak.net84.net/
Email: akarmenia@gmail.com

DESCRIPTION:
    Functions used to handle and present errors.
    
*/

// For printing errors on screen
require_once('ak_formatting.php');
// For function stack offsets
require_once('ak_numeric.php');
// For the function stack
require_once('ak_array.php');
// Empty checks
require_once('ak_check.php');

// Global definitions used for the function stack
define('ERROR_CALLER', 1);
define('NO_OFFSET', 0);
define('OFFSET_BY_ONE', 1);
define('SEND_TO_CALLER', 1);

/* This function will return the name string of the function that called $function. To return the
    caller of your function, either call get_caller(), or get_caller(__FUNCTION__). */
function get_caller($function = NULL, $use_stack = NULL) {
    if ( is_array($use_stack) ) {
        // If a function stack has been provided, used that.
        $stack = $use_stack;
    } else {
        // Otherwise create a fresh one.
        $stack = debug_backtrace();
    }
    
    if ($function == NULL) {
        /* We need $function to be a function name to retrieve its caller. If it is omitted, then
           we need to first find what function called get_caller(), and substitute that as the
           default $function. Remember that invoking get_caller() recursively will add another
           instance of it to the function stack, so tell get_caller() to use the current stack. */
        $function = get_caller(__FUNCTION__, $stack);
    }
    
    if ($function != '') {
        for ($i = 0; $i < count($stack); $i++) {
            $curr_function = $stack[$i];
            if ( $curr_function["function"] == $function && ($i + 1) < count($stack) ) {
                return $stack[$i + 1]["function"];
            }
        }
    } else {
        // We can't help here
        return '';
    }
}

/*  Prints the debug trace/stack starting from $function, or if $reverse is TRUE, prints it up to 
    $function. */
function print_stack($function = NULL, $reverse = FALSE, $mode = M_PRINT) {
    
    $output = '';
    $stack = debug_backtrace();
    if ($reverse) {
        $stack = array_reverse($stack);
    }
    
    // Start printing details immediatedly if the given function is undefined or we are in reverse
    if ( !valid_string($function) || $reverse ) {
        $print = TRUE;
    } else if (!$reverse) {
        // Otherwise start printing once we encounter the given function
        $print = FALSE;
    }
    //foreach ($stack as $item) {
    for ($i = 0; $i < count($stack); $i++) {
        $item = $stack[$i];
        // Once we encounter the function we are detailing, toggle $print
        if (!$reverse && $item['function'] == $function) {
            $print = !$print;
        }
        if ($print) {
            // Print the function
            $output .= get_stack_function($item['file'], $item['line'], $item['function'], 
                       $item['args']);
            // Print a line after each function
            if ( count($stack) != 1 && $i != count($stack) - 1 ) {
                $output .= HR;
            }
        }
        // Ditto, remember we need to do it after we print the function when in reverse
        if ($reverse && $item['function'] == $function) {
            $print = !$print;
        }
    }
    
    if ($mode == M_RETURN) {
        return $output;
    } else {
        echo $output;
    }
}

function get_stack($function = NULL, $reverse = FALSE) {
    return print_stack($function, $reverse, M_RETURN);
}

function get_stack_function($errfile, $errline, $function = NULL, $args = array()) {
    if ( !is_string($errfile) || (!is_string($errline) && !is_numeric($errline)) ) {
        return 'Error printing details.' ;
    }
    // Error details
    $return = bold('File: ', BLACK) . $errfile . BR . bold('Line: ', BLACK) . $errline . BR;
    // Function name and arguments. Arguments use a custom function to print types, otherwise
    // Arrays appear simply as 'Array', numeric strings appear like numerics and booleans are 
    // either 1 for TRUE or nothing for FALSE.
    if ( valid_string($function) ) {
        $return .= bold('Function: ', BLACK) . bold($function) . '(' . array_to_string($args, SHOW_TYPES) . ');' . BR ;
    }
    return $return;
}

/* A custom error handler that prints out a pretty error message, and also the function stack */
function ak_error_handler($errno, $errstr, $errfile, $errline, $function = '', $args = array()) {
         
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }
    
    // Whether the print should print the stack
    $print_stack = ($errno == E_USER_ERROR || $errno == E_USER_WARNING);
    
    // Whether the script should exit after reporting
    $terminate = FALSE;
    
    // If the error string is blank or invalid, show it
    if ( !valid_string($errstr) ) {
        $errstr = 'Unknown Error';
    }
    
    // The strings
    $header = '';
    $message = '';
    
    // Different error cases
    $error_type = ($errno == E_USER_ERROR || $errno == E_ERROR);
    $warning_type = ($errno == E_USER_WARNING || $errno == E_WARNING);
    $notice_type = ($errno == E_USER_NOTICE || $errno == E_NOTICE);

    switch ($errno) {
        case $error_type:
            $header .= 'Fatal Error';
            $terminate = TRUE;
            break;
        case $warning_type:
            $header .= 'Warning';
            break;
        case $notice_type:
            $header .= 'Notice';
            break;
        default:
            $header .= "Error [Code $errno]";
            break;
    }
    
    // If a function to detail is provided, show it
    if ( valid_string($function) ) {
        $header .= " In $function()";
    }
    $header .= ': ' . italic($errstr);
    
    // Check if we should print out the stack, usually for errors and warnings only
    // If we don't need to print the stack, then we should just print the details of the error
    if ($print_stack) {
        $message .= get_stack($function);
    } else {
        $message .= get_stack_function($errfile, $errline, $function, $args);
    }
    
    // Print the actual error
    print_message($header, color($message, GRAY));
    
    // Used by fatal errors to terminate the script, after they print info
    if ($terminate) {
        exit(1);
    }
    
    // Don't execute PHP internal error handler
    return true;
}
// Set the Error Handler
$old_error_handler = set_error_handler("ak_error_handler");

/* Invokes the custom error handler with a custom error */
function error($errstr = '', $errno = E_USER_ERROR, $lookup_function = NULL) {
    // Look up the caller of error() if we aren't provided with a lookup function to detail
    if ( !valid_string($lookup_function) ) {
        $lookup_function = get_caller();
        // If the error was called from the main script, return the details of error() itself
        if ($lookup_function == '') {
            $lookup_function = __FUNCTION__;
        }
    }
        
    // Find the lookup function in the stack and report an error on its details
    $stack = debug_backtrace();
    foreach ($stack as $function) {
        if ($function["function"] == $lookup_function) {
            ak_error_handler($errno, $errstr, $function["file"], $function["line"], 
                $function["function"], $function["args"]);
        }
    }
}

?>
