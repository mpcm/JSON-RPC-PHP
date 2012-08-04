<?php
class transport_http extends jsonrpc2{
	
    // keys we check for non-parameterized call
    public $keys = array('jsonrpc');

    public function process(){
        try{

            // try to extract the values from matching keys in the $_REQUEST
            $request = $this->resolve_from_keys();
        	
            // next, try a blob search
            if( !isset($request) || $request === FALSE){
            	$request = $this->resolve_from_blob();
            }
            
            // check if we extracted a structured value
            if(is_object($request) || is_array($request)){
            	return json_encode( $this->dispatch($request) );
            }

            // we could not detect anything, throw an error
            throw new Exception(-32600); 
        }
        catch(Exception $e){
            $error = $e->getMessage();
        }
        if( !isset( $error ) ){
        	$error = -32603;
        }
        return json_encode( parent::error( $error ) );
    }

    
    private function resolve_from_keys(){
    	
        if( isset( $_REQUEST['method'] ) ){
            $request = (object) array('method'=>null,'params'=>array(),'id'=>null,'jsonrpc'=>'2.0');
            foreach( $request as $k=>$v){
                if( isset( $_REQUEST[$k] ) ){                	
                    $i = json_decode( $_REQUEST[$k], TRUE );
                    $request->$k = ( $i ) ? $i : $_REQUEST[$k];
                }
            }
        }        
        return isset($request) ? $request : FALSE;
    }
    
    private function resolve_from_blob(){
    	
    	// check our keys for a blob that decodes
    	foreach( $this->keys as $key){
        	if( isset( $_REQUEST[$key] ) ){
        		$request = json_decode( $_REQUEST[$key] );
        	}
    	}

    	// if we have a structured value, return it
    	if( is_array($request) || is_object($request) ) return $request;
    	
    	// if not, attempt other methods
        if( !is_array($request) && !is_object($request) && isset( $_SERVER['QUERY_STRING'] ) ) $request = json_decode( rawurldecode( $_SERVER['QUERY_STRING'] ), TRUE );
        if( !is_array($request) && !is_object($request) ) $request = json_decode( file_get_contents( 'php://input' ), TRUE );        
        if( !is_array($request) && !is_object($request) ) throw new Exception(-32700);
        return $request;
    }
    
    private function map_into($i){
        $r = array('method'=>null,'params'=>array(),'id'=>null,'jsonrpc'=>'2.0');
        foreach($r as $k=>$v){
            if( isset( $i[$k] ) ){
	       $r[$k] = $i[$k];
            }    
        }
        return (object) $r;
    }
}