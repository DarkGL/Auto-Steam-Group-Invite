<?php

include( "bigInteger.php" );
include( "classHex.php" );

class rsaPublicKey {
	
	public $modulus;
	public $encryptionExp;
	
	public function __construct( $modulusHex , $exponentHex ){
		
		$this -> modulus		=	new bigInteger( $modulusHex , 16 );
		$this -> encryptionExp	=	new bigInteger( $exponentHex , 16 );
		
	}
}

class rsaClass {
	
	public function getPublicKey( $modulusHex , $exponentHex ){
		
		return new rsaPublicKey( $modulusHex , $exponentHex );
		
	}
	
	public function encrypt( $data , $pubKey ){
		
		if( !$pubKey ){
			return FALSE;
		}
		
		$data	=	$this -> pkcs1pad2( $data , ( $pubKey -> modulus -> bitLength() + 7 ) >> 3 );
		
		if( !$data ){
			return FALSE;
		}
		
		$data = $data -> modPowInt( $pubKey -> encryptionExp , $pubKey -> modulus );
       	
       	if( !$data ){
			return FALSE;
		}
		
        $data	=	$data -> toString( 16 );
		
		$hexObject	=	new classHex();
		
		return base64_encode( $hexObject -> hexDecode( $data ) );
	}
	
	public function pkcs1pad2( $data , $keySize ){
		
		if( $keySize < strlen( $data ) + 11 ){
			return NULL;
		}
		
		$buffer	=	array();
		
		$i = strlen( $data ) - 1;
        
        while( $i >= 0 && $keySize > 0 ){
        	
        	$buffer[ --$keySize ]	=	ord( $data[ $i-- ] );
			
		}
       
	    $buffer[ --$keySize ]	=	0;
		
        while( $keySize > 2 ){
            $buffer[ --$keySize ]	=	rand( 1 , 255 );
			
		}
		
		$buffer[ --$keySize ]	=	2;
		$buffer[ --$keySize ]	=	0;
		
        return new bigInteger( $buffer );
	}
}

?>