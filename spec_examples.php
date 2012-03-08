<?php

function get_examples(){
	$examples[] = array(
		'description' => 'rpc call with positional parameters #1',
		'request' => '{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}',
		'expected_response' => '{"jsonrpc": "2.0", "result": 19, "id": 1}'
	);
	$examples[] = array(
		'description' => 'rpc call with positional parameters #2',
		'request' => '{"jsonrpc": "2.0", "method": "subtract", "params": [23, 42], "id": 1}',
		'expected_response' => '{"jsonrpc": "2.0", "result": -19, "id": 1}'
	);
	
	$examples[] = array(
		'description' => 'rpc call with named parameters #1',
		'request' => '{"jsonrpc": "2.0", "method": "subtract", "params": {"subtrahend": 23, "minuend": 42}, "id": 3}',
		'expected_response' => '{"jsonrpc": "2.0", "result": 19, "id": 3}'
	);
	
	$examples[] = array(
		'description' => 'rpc call with named parameters #2',
		'request' => '{"jsonrpc": "2.0", "method": "subtract", "params": {"minuend": 42, "subtrahend": 23}, "id": 4}',
		'expected_response' => '{"jsonrpc": "2.0", "result": 19, "id": 4}'
	);
	
	$examples[] = array(
		'description' => 'notification #1',
		'request' => '{"jsonrpc": "2.0", "method": "update", "params": [1,2,3,4,5]}',
		'expected_response' => 'null'
	);
	
	$examples[] = array(
		'description' => 'notification #2',
		'request' => '{"jsonrpc": "2.0", "method": "foobar"}',
		'expected_response' => 'null'
	);
	
	$examples[] = array(
		'description' => 'rpc call of non-existent method',
		'request' => '{"jsonrpc": "2.0", "method": "foobar", "id": "1"}',
		'expected_response' => '{"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found."}, "id": "1"}'
	);
	
	
	$examples[] = array(
		'description' => 'rpc call with invalid JSON',
		'request' => '{"jsonrpc": "2.0", "method": "foobar, "params": "bar", "baz]',
		'expected_response' => '{"jsonrpc": "2.0", "error": {"code": -32700, "message": "Parse error."}, "id": null}'
	);
	
	$examples[] = array(
		'description' => 'rpc call with invalid JSON',
		'request' => '{"jsonrpc": "2.0", "method": 1, "params": "bar"}',
		'expected_response' => '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."}, "id": null}'
	);
	
	
	$examples[] = array(
		'description' => 'rpc call Batch, invalid JSON',
		'request' => '[ {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},{"jsonrpc": "2.0", "method" ]',
		'expected_response' => '{"jsonrpc": "2.0", "error": {"code": -32700, "message": "Parse error."}, "id": null}'
	);
	
	$examples[] = array(
		'description' => 'rpc call with an empty Array',
		'request' => '[]',
		'expected_response' => '{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."}, "id": null}'
	);
	
	$examples[] = array(
		'description' => 'rpc call with an invalid Batch (but not empty)',
		'request' => '[1]',
		'expected_response' => '[{"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."}, "id": null}]'
	);
	
	$examples[] = array(
		'description' => 'rpc call with invalid Batch',
		'request' => '[1,2,3]',
		'expected_response' => '[
	        {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."}, "id": null},
	        {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."}, "id": null},
	        {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."}, "id": null}
	    ]'
	);
	
	
	$examples[] = array(
		'description' => 'rpc call Batch',
		'request' => '[
	        {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
	        {"jsonrpc": "2.0", "method": "notify_hello", "params": [7]},
	        {"jsonrpc": "2.0", "method": "subtract", "params": [42,23], "id": "2"},
	        {"foo": "boo"},
	        {"jsonrpc": "2.0", "method": "foo.get", "params": {"name": "myself"}, "id": "5"},
	        {"jsonrpc": "2.0", "method": "get_data", "id": "9"} 
	    ]',
		'expected_response' => '[
	        {"jsonrpc": "2.0", "result": 7, "id": "1"},
	        {"jsonrpc": "2.0", "result": 19, "id": "2"},
	        {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid Request."}, "id": null},
	        {"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found."}, "id": "5"},
	        {"jsonrpc": "2.0", "result": ["hello", 5], "id": "9"}
	    ]'
	);
	
	$examples[] = array(
		'description' => 'rpc call Batch',
		'request' => '[
	        {"jsonrpc": "2.0", "method": "notify_sum", "params": [1,2,4]},
	        {"jsonrpc": "2.0", "method": "notify_hello", "params": [7]}
	    ]',
		'expected_response' => 'null'
	);
	
	return $examples;
}