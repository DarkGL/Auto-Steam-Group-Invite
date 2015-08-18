<?php
class informationContainter {
	
	private $dataUsers;
	
	public function __construct(){
		
		$this -> dataUsers	=	array();
		
	}
	
	public function sortInformation( $szLogin , $szPass , $groupURL , $steamid ){
		
		$this -> checkUser( $szLogin, $szPass );
		$this -> getUser( $szLogin ) -> checkGroup( $groupURL );
		$this -> getUser( $szLogin ) -> getGroup( $groupURL ) -> addSteamID( $steamid );
		
	}
	
	private function getUser( $login ){
		return isset( $this -> dataUsers[ $login ] ) ? $this -> dataUsers[ $login ] : NULL;
	}
	
	public function getUsers(){
		return $this -> dataUsers;
	}
	
	private function checkUser( $szLogin , $szPass ){
		
		if( !isset( $this -> dataUsers [ $szLogin ] ) ){
			$this -> createUser( $szLogin, $szPass );
		}
		
	}
	
	private function createUser( $szLogin , $szPass ){
		
		$this -> dataUsers[ $szLogin ]	=	new userClass( $szLogin , $szPass );
		
	}
	
}

class userClass {
	
	private $login , $pass;
	
	private $dataGroup;
	
	public function __construct( $szLogin , $szPass ){
		
		$this -> login	=	$szLogin;
		$this -> pass	=	$szPass;
		
		$this -> dataGroup		=	array();
		
	}
	
	public function getGroup( $group ){
		return isset( $this -> dataGroup[ $group ] ) ? $this -> dataGroup[ $group ] : NULL;
	}
	
	public function getGroups(){
		return $this -> dataGroup;
	}
	
	public function getLogin(){
		return $this -> login;	
	}
	
	public function getPass(){
		return $this -> pass;
	}
	
	public function checkGroup( $groupURL ){
		
		if( !isset( $this -> dataGroup[ $groupURL ] ) ){
			
			$this -> createGroup( $groupURL );
			
		}
		
	}
	
	private function createGroup( $groupURL ){
		
		$this -> dataGroup[ $groupURL ]	=	new groupClass( $groupURL );
		
	}
	
}

class groupClass {
	
	private $groupLink ,
			$steamIDS;
	
	public function __construct( $URL ){
		
		$this -> groupLink	=	$URL;
		$this -> steamIDS	=	array();
		
	}
	
	public function getGroupLink(){
		
		return $this -> groupLink;
		
	}
	
	public function addSteamID( $steamID ){
		array_push( $this -> steamIDS , $steamID );
	}
	
	public function getIDS(){
		return $this -> steamIDS;
	}
	
}
?>