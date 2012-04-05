<?php
header('Content-type: application/json; charset=utf-8');

// load the shared functions into the global scope
// these happen to be the functions related to specification examples
// but normally you would load your custom functions here
require_once('spec_functions.php');

// load the base jsonrpc2 processing class
require_once('jsonrpc2.class.php');

// load the transport processing layer, extends base class
require_once('transport_http.class.php');

// create a instance of the transport layer, with method maps
$server = new transport_http(
  array(
    'subtract'  => 'subtract',
    'sum'  => 'sum',
    'notify_hello' => 'notify_hello',
    'notify_sum' => 'notify_sum',
    'get_data' => 'get_data'
  )
);

// add additional query string keys to check, if so desired
$server->keys[] = 'msg';

print $server->process();