<?php

/**
 * 
 * This class handles the processing of json-rpc request
 * @author Matt Morley (MPCM)
 *
 */
class jsonrpc2 {
	
	static function error( $code, $version = '2.0', $id = null ) {
		// declare the specification error codes
		$errors = array (
			-32600 => 'Invalid Request', 
			-32601 => 'Method not found', 
			-32602 => 'Invalid params', 
			-32603 => 'Internal error', 
			-32700 => 'Parse error'
		);
		
		return ( object ) array (
			'id'=>$id,
			'jsonrpc'=> $version,
			'error' => ( object ) array ('code' => $code, 'message'=>$errors[$code] )
		);
	}	
	
	// an array of method routes, set in the constructor
	private $method_map = array ();
	
	/**
	 * create an instance of the jsonrpc2 class, and map in the allowed method strings
	 * @param array $method_map
	 */
	public function __construct( array $method_map) {
		$this->method_map = $method_map;
	}
	
	public function isValidRequestObect( $request ){

		// per the 2.0 specification
		// a request object must:
		// be an object
		if( !is_object($request) ) return false;
		
		// contain a jsonrpc member that is a string
		if( !isset($request->jsonrpc) || $request->jsonrpc !== '2.0' ) return false;

		// contain a method member that is a string
		if( !isset($request->method) || !is_string($request->method) ) return false;
		
		// if it contains a params member
		//    that member must be an array or an object
		if( isset($request->params) ){
			if(!is_array($request->params) && !is_object($request->params) ){
				return false;
			}
		} 
		
		// if it contains an id member
		//    that member must be a string, number, or null
		if( isset( $request->id ) ){
			if( !is_string($request->id) && !is_numeric($request->id) && !is_null($request->id) ){
				return false;	
			}
		}

		// it passes the tests
		return true;
	}
	
	
	public function dispatch( $request ) {
		
		try {

			// decode the string, if passed as a string
			if( is_string($request) ){
				$request = json_decode( $request );
			}
			
			if ( is_null($request) || is_string( $request ) === TRUE ){
				return jsonrpc2::error( -32700 );
			}
			
			// if we are passed an array of requests
			if (is_array ( $request ) ) {
				
				// make sure it is a numeric array
				$is_assoc = array_keys ( $request ) != range ( 0, count ( $request ) - 1 );
				
				if( $is_assoc ){
					// this array, but not numeric
					return jsonrpc2::error( -32600 );
				}
				
				//shuttlfe the request order, to simulate non-sequential processing that *could* occur on other servers
				// disabled for ease of output comparison testing
				// shuffle ( $request );
				
				//create a holder for all the responses
				$return = array ();
				
				//for each request as a request object
				foreach ( $request as $request_object ) {
					
					//process each request object
					$return[] = $this->dispatch_single( $request_object );

					// remove the last request if somehow it is not an object
					if( is_object(end($return)) === FALSE){
						array_pop($return);
					}
					
				}
				
				// if there are no results (all notifications)
				if( count($return) == 0){
					return null;
				}
				
				//return the array of results
				return $return;
			}
			
			//process the request
			return $this->dispatch_single ( $request );
			
		} catch (Exception $e) {
			return jsonrpc2::error( -32603 );
		}

	}
	
	/**
	 * process a single request object
	 * @param request object
	 */
	public function dispatch_single($request) {
		
		// check that the object passes some basic protocal shape tests
		if( $this->isValidRequestObect( $request ) === false ){
			// invalid request object			
			return jsonrpc2::error( -32600 );
		}

		// if the request object does not specify a jsonrpc verison
		if (! isset( $request->jsonrpc ) ) {
			// assume it is a 1.0 request
			$request->jsonrpc = '1.0';
		}
		
		// if the request is 2.0 and and no params were sent
			// create an empty params entry,
			// as 2.0 requests do not need to send an empty array
			// later code can now assume that this field will exist
		if ($request->jsonrpc == '2.0' && !isset ( $request->params )) {
			$request->params = array();
		}
		
		// invoke the request object, and store it in the reponse
		$response = $this->invoke ( $request );
		
		// if the request id is not set, or if it is null
		if (! isset ( $request->id ) || is_null ( $request->id )) {
			// return null instead of a response object
			return null;
		}
		
		// copy the request id into the response object
		$response->id = $request->id;
		
		// if it is a 2.0 request
		if($request->jsonrpc === '2.0') {
			
			// set the response to 2.0
			$response->jsonrpc = $request->jsonrpc;
		
		} else {
			// assume it is a 1.0 requrest
			

			// if there is no result in the response
			if (! isset ( $response->result )) {
				
				// add one
				$response->result = null;
			}
			
			// if there is no error in the response
			if (! isset ( $response->error )) {
				
				// add one
				$response->error = null;
			
			}
		
		}

		// return the response object
		return $response;
	}
	
	// take a more complete request, after processing, and invoke it after checking the parameters align
	// extend this function if you need to provide more automatic actions related to methods in classes/instanes
	private function invoke( $request ) {

		// if the method requested is available
		if( isset( $this->method_map[$request->method] ) ) {
			try{
				// reflect the global function
				$reflection = new ReflectionFunction ( $this->method_map[$request->method] );

				// check the parameters in the reflection against what was sent in the request
				$params = $this->checkParams( $reflection->getParameters(), $request->params );
				
				// return the result as an invoked call
				return (object) array ('result' => $reflection->invokeArgs ( $params ) );
				
			} catch ( Exception $e ) {				
				// if anything abnormal happened, capture the error code thrown
				$error = $e->getMessage ();
			}
		}		
		// by this point, all we have is errors
		return jsonrpc2::error( isset($error) ? $error : -32601 );
	}
	
	private function checkParams($real, $sent) {
				
		// was the param list that was provided an object
		$is_obj = is_object( $sent );
		
		// if not an object, check if it is an associative array
		if(!$is_obj){
			// check if the param list is an array
			$is_assoc = array_keys ( $sent ) !== range ( 0, count ( $sent ) - 1 );
		}

		// create the param list we are going to use to invoke the object
		$new = array ();
		
		// check every parameter, a
		foreach ( $real as $i => $param ) {
			
			$name = $param->getName ();
			
			if ($is_obj && isset( $sent->{$name} )) {
				$new[$i] = $sent->{$name};
			} elseif ($is_assoc && $sent[$name]) {
				$new[$i] = $sent[$name];
			} elseif (isset( $sent [$i] )) {
				$new[$i] = $sent [$i];
			} elseif (! $param->isOptional()) {
				throw new Exception( -32602 );
			}
		}

		// return the list of matching params
		return $new;
	}
}
