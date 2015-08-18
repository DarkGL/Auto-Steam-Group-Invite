<?php
class GroupID {
	
	private $groupUrl;
	private $groupID;
	
	public function __construct( $szUrl ){
	 	
		$this -> groupUrl	=	$szUrl;
		
		$this -> groupID	=	$this -> calculateID();
		
	}
	
	private function calculateID(){
		
		$curlObject	=	curl_init( $this -> groupUrl );
		
		curl_setopt( $curlObject , CURLOPT_RETURNTRANSFER, 1);
		curl_setopt( $curlObject , CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; Linux i686; pl; rv:1.8.0.3) Gecko/20060426 Firefox/1.5.0.3');
		curl_setopt( $curlObject , CURLOPT_SSL_VERIFYPEER , FALSE );
		curl_setopt( $curlObject , CURLOPT_SSL_VERIFYHOST , 2 );
		curl_setopt( $curlObject , CURLOPT_FOLLOWLOCATION, 1 ); 
		curl_setopt( $curlObject , CURLOPT_COOKIEJAR , "cookie.txt" );
		curl_setopt( $curlObject , CURLOPT_COOKIEFILE , "cookie.txt" );
		curl_setopt( $curlObject , CURLOPT_POST , FALSE );
		
		$resultArray	=	curl_exec( $curlObject );
		
		preg_match( '/steam:\/\/friends\/joinchat\/.*\'/', $resultArray , $matches );
		
		return isset( $matches[ 0 ] ) ? substr( $matches[ 0 ] , strrpos( $matches[ 0 ] , '/' ) + 1 , -1 ) : "";
	}
	
	public function getGroupID(){
		
		return $this -> groupID;
		
	}
	
}

?>