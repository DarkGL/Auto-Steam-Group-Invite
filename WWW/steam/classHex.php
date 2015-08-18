<?php

class classHex {
	
	public function hexEncode( $data ){
		
		$szResult	=	"";
		
		for( $i = 0 ; $i < strlen( $data ) ; $i++ ){
			
			$szResult	.=	dechex( ord( $data[ $i ] ) );
			
		}
		
		return $szResult;
	}
	
	public function hexDecode( $data ){
		$data	=	preg_replace( '/[^0-9abcdef]/' , "" , $data );
		$szResult	=	"";
			
		for( $i = 0 ; $i < strlen( $data ) ; $i += 2 ){
			
			$szResult	.=	chr( hexdec( $data[ $i ] . $data[ $i + 1 ] ) );
			
		}
		
		return $szResult;
	}
	
}

?>