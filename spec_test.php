<HTML>
<BODY>
<PRE>
<?php

// load the shared functions into the global scope
// these happen to be the functions related to
require_once('spec_functions.php');

// load the base class
require_once('jsonrpc2.class.php');

// load the examples to use
require_once('spec_examples.php');

// create a instance of the transport layer
$server = new jsonrpc2(
	array(
		'subtract' 		=> 'subtract',
		'sum' 			=> 'sum',
		'notify_hello' 	=> 'notify_hello',
		'notify_sum' 	=> 'notify_sum',
		'get_data' 		=> 'get_data'
	)
);

// generate example list
$examples = get_examples();

// process all of the examples
foreach( $examples as &$example){	
	$example['response'] = json_encode( $server->dispatch( $example['request'] ) );	
	if( is_null($example['response'])){
		$example['response'] = '';
	}
}

// generate an output to display the test results
$output = array();
$output[] = 'Examples from the JSON-RPC 2 Specification';

foreach( $examples as &$example){
	$output[] = '';
	$output[] = $example['description'];
	$output[] = '-------------------------------';
	$output[] = 'request:'.$example['request'];
	// $diff = array_diff_assoc( json_decode( $example['response'], TRUE) , json_decode( $example['expected_response'], TRUE) );
	$output[] = 'expected:'. $example['expected_response'];
	$output[] = 'response:'.$example['response'];
	// $output[] = print_r( $diff, true );
}

echo implode("\n", $output);

?>
</PRE>
</BODY>
</HTML>