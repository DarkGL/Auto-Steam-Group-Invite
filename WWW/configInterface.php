<?php

class configClass {
	
	private static $configFile		=	"config.ini";
	
	private static $objectInstance	=	FALSE;
	
	private $szHost ,
			$szUser ,
			$szPass ,
			$szDb;
		
	private $tableToInvite ,
			$tableWithIDS ,
			$tableLogs;
	
	private function __construct(){
		
		$resultArray	=	parse_ini_file( self::$configFile );
		
		$this -> szHost	=	$resultArray[ 'dbHost' ];
		$this -> szUser	=	$resultArray[ 'dbUser' ];
		$this -> szPass	=	$resultArray[ 'dbPass' ];
		$this -> szDb	=	$resultArray[ 'dbDataBase' ];
		
		$this -> tableToInvite	=	$resultArray[ 'tableToInvite' ];
		$this -> tableWithIDS	=	$resultArray[ 'tableWithIDS' ];
		
		$this -> tableLogs		=	$resultArray[ 'tableLogs' ];
	}
	
	public static function getInstance(){
		
		if( !self::$objectInstance ){
			
			self::$objectInstance	=	new	configClass();
			
		}
		
		return self::$objectInstance;
		
	}
	
	public function getSQLHost(){
		return $this -> szHost;
	}
	
	public function getSQLUser(){
		return $this -> szUser;
	}
	
	public function getSQLPass(){
		return $this -> szPass;
	}
	
	public function getSQLDb(){
		return $this -> szDb;
	}
	
	public function getSQLTableInvite(){
		return $this -> tableToInvite;
	}
	
	public function getSQLTableContainer(){
		return $this -> tableWithIDS;
	}
	
	public function getSQLTableLogs(){
		return $this -> tableLogs;
	}
}

function getConfigInstance(){
	return configClass::getInstance();
}

?>