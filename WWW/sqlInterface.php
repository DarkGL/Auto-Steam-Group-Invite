<?php

include_once( "configInterface.php" );

final class sqlInterface {
	
	const NO_SQL_RETURN		=	FALSE;
	const SQL_RETURN		=	TRUE;
	
	private static $objectInstance	=	FALSE;
	
	private $szHost ,
			$szUser ,
			$szPass ,
			$szDb;
	
	private $sqlHandle;
	
	private function __construct(){
		
		$configInstance		=	configClass::getInstance();
		
		$this -> szHost		=	$configInstance -> getSQLHost();
		$this -> szUser		=	$configInstance -> getSQLUser();
		$this -> szPass		=	$configInstance -> getSQLPass();
		$this -> szDb		=	$configInstance -> getSQLDb();
		
		$this -> sqlHandle	=	mysql_connect( $this -> szHost , $this -> szUser , $this -> szPass );
		
		self::isProperly( $this -> getSqlHandle() , "Can't connect to sql : " );
		
		$dbHandle	=	mysql_select_db( $this -> szDb , $this -> getSqlHandle( $this -> getSqlHandle() ) );
			
		self::isProperly( $dbHandle , "Can't choose database : " );
	}
	
	public static function getInstance(){
		
		if( !self::$objectInstance ){
			
			self::$objectInstance	=	new	sqlInterface();
			
		}
		
		return self::$objectInstance;
		
	}
	
	public function __destruct(){
		
		mysql_close( $this -> sqlHandle );
		
	}
	
	private function getSqlHandle(){
		
		return $this -> sqlHandle;
		
	}
	
	private function isProperly( $condition ){
		
		if( !$condition ){
			
			throw new Exception( $errorString . mysql_error( $this -> getSqlHandle() ) );
		}
		
	}
	
	public function escapeString( $szString ){
		
		return mysql_real_escape_string( $szString );
		
	}
	
	public function sqlQuery( $szQuery , $bReturn = self::NO_SQL_RETURN ){
		
		$arrayResult	=	mysql_query( $szQuery , $this -> getSqlHandle() );
		
		if( $arrayResult == FALSE ){
			
			print mysql_error( $this -> getSqlHandle() );
			
		}
		
		return $bReturn == self::SQL_RETURN ? $arrayResult : NULL;
	}
}

function getSQLInstance(){
	return sqlInterface::getInstance();
}

?>