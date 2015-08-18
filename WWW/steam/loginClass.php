<?php
//TO DO
//wychodzenie z grupy
//zapraszanie do znajomych

include( "rsaClass.php" );
include( "steamid.php" );
include( "groupid.php" );

class curlLogin {
	
	private $curlPointer;
	
	public function __construct ( $szLink , $bNoPost = FALSE ){
		
		$this -> curlPointer	=	curl_init( $szLink );
		
		curl_setopt( $this -> curlPointer , CURLOPT_RETURNTRANSFER, 1);
		curl_setopt( $this -> curlPointer , CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; Linux i686; pl; rv:1.8.0.3) Gecko/20060426 Firefox/1.5.0.3');
		curl_setopt( $this -> curlPointer , CURLOPT_SSL_VERIFYPEER , FALSE );
		curl_setopt( $this -> curlPointer , CURLOPT_SSL_VERIFYHOST , 2 );
		curl_setopt( $this -> curlPointer , CURLOPT_FOLLOWLOCATION, 1 ); 
		curl_setopt( $this -> curlPointer , CURLOPT_COOKIEJAR , "cookie.txt" );
		curl_setopt( $this -> curlPointer , CURLOPT_COOKIEFILE , "cookie.txt" );
		curl_setopt( $this -> curlPointer , CURLOPT_POST , $bNoPost ? FALSE : TRUE );
	
	}
	
	public function __destruct (){
		
		curl_close( $this -> curlPointer );
		
	}
	
	public function setPost ( $arrayPost ){
			
		curl_setopt( $this -> curlPointer , CURLOPT_POSTFIELDS , $arrayPost );
		
	}
	
	public function execute (  $bNoReturn = FALSE ){
		
		if( !$bNoReturn ){
			return curl_exec( $this -> curlPointer );
		}
	}
}

class loginClass {
	
	const ID_64			=	TRUE;
	const NOT_ID_64		=	FALSE;
	
	const CALCULATE_GROUP	=	TRUE;
	const IS_CALCULATED		=	FALSE;
	
	private $userLogin;
	private $userPass;
	
	private $publicKeyMod;
	private $publicKeyExp;
	private $timeStamp;
	
	private $isLogged;
	
	private $sessionID;
	private $steamID64;
	private $groupID;
	
	private $steamComID;
	
	public function __construct ( $szLogin , $szPass ){
		
		$this -> userLogin	=	$szLogin;
		$this -> userPass	=	$szPass;
		
		$this -> isLogged	=	FALSE;
	}
	
	
	public function doLogin (){
		
		$pCurl	=	new curlLogin( "https://steamcommunity.com/login/getrsakey/" );
		
		$pCurl 	->	setPost( array( "username" => $this -> userLogin ) );
		
		$arrayResult	=	$pCurl	->	execute();
		
		if( !$arrayResult ){
			echo "Can't execute getrsakey";
			
			return ;
		}
		
		$arrayResult	=	json_decode( $arrayResult , TRUE );
		
		if( !$arrayResult[ 'success' ] ){
			
			return ;
			
		}
		
		$this -> publicKeyExp	=	$arrayResult[ 'publickey_exp' ];
		$this -> publicKeyMod	=	$arrayResult[ 'publickey_mod' ];
		$this -> timeStamp		=	$arrayResult[ 'timestamp' ];
		
		$this -> rsaKeyResponse();
	}
	
	private function rsaKeyResponse ( ){
		
		$rsa			=	new	rsaClass();
		
		$pubKey			=	$rsa -> getPublicKey( $this -> publicKeyMod , $this -> publicKeyExp );
		
		$encryptPass	=	$rsa -> encrypt( $this -> userPass , $pubKey );
		
		$curlObject		=	new curlLogin( "https://steamcommunity.com/login/dologin/" );
		$curlObject		->	setPost( array( "password" 			=> $encryptPass ,
											"username" 			=> $this -> userLogin ,
											"emailauth"			=> '' ,
											"captchagid"		=> -1 ,
											"captcha_text"		=>	'' ,
											"emailsteamid"		=>	'' ,
											"rsatimestamp"		=>	$this -> timeStamp ,
											"remember_login"	=>  false ) );
		
		$arrayResult	=	$curlObject		-> execute();
		
		if( !$arrayResult ){
			echo "Can't execute rsakeyresponse";
			
			return ;
		}
		
		$arrayResult	=	json_decode( $arrayResult , TRUE );
		
		if( !$arrayResult[ 'success' ] || !$arrayResult[ 'login_complete' ] || !$arrayResult[ 'transfer_url' ] || !$arrayResult[ 'transfer_parameters' ]){
			
			return ;
			
		}
		
		$this -> loginTransfer( $arrayResult );
		
	}

	private function loginTransfer( $arrayResult ){
		
		$postArray		=	array();
		
		foreach ( $arrayResult[ 'transfer_parameters' ] as $key => $value ) {
			
			$postArray[ $key ]	=	$value;
			
		}
		
		$this -> steamID64	=	$arrayResult[ 'transfer_parameters' ][ 'steamid' ];
		
		$curlObject		=	new curlLogin( $arrayResult[ 'transfer_url' ] );
		$curlObject		->	setPost( $postArray );
		
		$arrayResult	=	$curlObject		->	execute();
		
		$this -> setLogged( TRUE );
		
		$this -> redirFunc();
		
	}
	
	private function redirFunc(){
		
		if( $this -> isNTLogged() ){
			return ;
		}
		
		$curlObject		=	new curlLogin( "http://steamcommunity.com/apps" , TRUE );
		
		$arrayResult	=	$curlObject		->	execute();
		
		preg_match( '/g_sessionID = ".*"/', $arrayResult , $matches );
		
		$this -> sessionID	=	isset( $matches[ 0 ] ) ? substr( $matches[ 0 ] , strpos( $matches[ 0 ] , '"' ) + 1 , -1 ) : "";
		
	}
	

	public function logout(){
		
		if( $this -> isNTLogged() ){
			return ;
		}
			
		$curlObject			=	new curlLogin( "https://steamcommunity.com/login/logout" , TRUE );
		
		$curlObject			->	execute( TRUE );
	}
	
	public function joinGroup( $urlGroup ){
		
		if( $this -> isNTLogged() ){
			return ;
		}
		
		$curlObject			=	new curlLogin( $urlGroup );
		$curlObject			->	setPost( array( "action" => "join" ,
												"sessionID" => $this -> sessionID ) );
		$curlObject			->	execute( );
	}
	
	public function inviteToGroup( $steamIDInvite , $groupURL , $bID64 = self::NOT_ID_64 , $groupCalculate = self::CALCULATE_GROUP ){
		
		if( $groupCalculate == self::CALCULATE_GROUP ){
			$this -> calculateGroupID( $groupURL );
		}
		
		$steamComID			=	$steamIDInvite;
		
		if( $bID64 == self::NOT_ID_64 ){
			$steamID				=	new SteamID( $steamIDInvite );
			$this -> steamComID		=	$steamID -> getSteamComID();
		}
		
		$curlObject		=	new curlLogin( "http://steamcommunity.com/actions/GroupInvite" );
		
		$curlObject		->	setPost( array( "xml" => "1" ,
											"type" => "groupInvite" ,
											"inviter" => $this -> steamID64,
											"invitee" => $this -> steamComID ,
											"group" => $this -> groupID ,
											"sessionID" => $this -> sessionID ) );
		
		$curlObject		->	execute( );
	}
	
	public function calculateGroupID( $groupURL ){
		$groupClass			=	new GroupID( $groupURL ) ;
		$this -> groupID	=	$groupClass	-> getGroupID();
	}
	
	public function isNTLogged(){
		return !( $this -> isLogged );
	}
	
	private function setLogged( $bLogged ){
		$this -> isLogged	=	$bLogged;
	}
	
	public function getSteamID64(){
		return $this -> steamID64;
	}
	
	public function getGroupID(){
		return $this -> groupID;
	}
	
	public function getComID(){
		return $this -> steamComID;
	}
}

?>