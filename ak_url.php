<?php

/*

Developed by: Aram Kocharyan
Date: 14th Jan 2011
Email: akarmenia@gmail.com

Description: URL checking and standardisation functions.

*/

define('INVALID_URL', 0);
define('URL_SCHEME_ERROR', 0);
define('ALLOW_REDIRECT', TRUE);
define('DENY_REDIRECT', FALSE);

function read_post($post_string) {
	if ( isset($_POST[$post_string]) ) {
		return $_POST[$post_string];
	} else {
		return NULL;
	}
}

function add_url_http($url) {
    $new_url = urldecode($url);
    $temp_url = 'url'.$new_url;
    if (strrpos($temp_url, "http://") != 3) {
        $new_url = 'http://'.$new_url;
    }
    return $new_url;
}

function url_standardise($url) {
	$array = parse_url($url);

	if ($array['scheme'] == "") {
		$url = add_url_http($url);
		$array = parse_url($url);
	} else if ($array['scheme'] != "http") {
		return URL_SCHEME_ERROR;
	}
	$ret_url = $array['scheme'] . "://" . $array['host'] . $array['path'];
	
	// add a suffix forward slash if needed
	if ( strrpos($ret_url, '/') != (strlen($ret_url) - 1) ) {
		$ret_url .= '/';
	}
	
    return $ret_url;
}

function is_invalid_http_code($http_code) {
    return $http_code < 200 || $http_code >= 400;
}

function is_redirect_http_code($http_code) {
    return $http_code >= 300 && $http_code < 400;
}

function is_redirect_http_codes($http_codes) {
    // Find a redirect code in the codes    
    foreach ($http_codes as $http_code) {
        if ( is_redirect_http_code($http_code) ) {
            return TRUE;
        }
    }
}

function is_invalid_http_codes($http_codes) {
    // Find an invalid code in the codes    
    foreach ($http_codes as $http_code) {
        if ( is_invalid_http_code($http_code) ) {
            return TRUE;
        }
    }
}

function check_url($url) {
    // Array of urls and http codes
    $http_codes = array();
    $urls = array();
    while (TRUE) {
        // Initialise curl and get the header
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        $content = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Add urls and http codes to the arrays
        array_push($urls, $url);
        array_push($http_codes, $http_code);
        
        if ( is_redirect_http_code($http_code) ) {
            // Check for redirects, if found, follow the redirected URL
            if ( preg_match('/(?<=Location: )[^ \s]*/i', $content, $matches) ) {
                $url = $matches[0];
                continue;
            }
        }
        
        // We can't do anything else
        break;
    }
    
    // Contains all the http_codes and urls encountered
    $ret['http_codes'] = $http_codes;
    $ret['urls'] = $urls;
    // For easy access, contains the last http_code and url encountered
    $ret['http_code'] = $http_code;
    $ret['url'] = $url;
    return $ret;
}

function is_valid_url($url, $allow_redirects = TRUE) {
    // Duplicate URL
    $url_check = check_url($url);
    $http_codes = $url_check['http_codes'];
    $returned_url = $url_check['url'];
    
    // If we recieved an invalid URL, or if the returned URL has redirected and we did not allow it,
    // return FALSE, otherwise return TRUE.
    if ( is_invalid_http_code($http_codes[0])
         || !$allow_redirects && is_redirect_http_codes($http_codes) ) {
        return FALSE;
    } else {
        return TRUE;
    }
}

?>