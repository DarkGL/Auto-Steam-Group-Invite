<?php

include( "configInterface.php" );
include( "sqlInterface.php" );
include( "containerClass.php" );
include( "steam/loginClass.php" );

$arrayIntiveInf	=	array();

$queryGet			=	"SELECT * FROM `" . getConfigInstance() -> getSQLTableInvite() . "`";
$queryClear			=	"TRUNCATE TABLE `" . getConfigInstance() -> getSQLTableInvite() . "`";
$queryInsertID		=	"INSERT INTO `" . getConfigInstance() -> getSQLTableContainer() . "` VALUES( '%s' )";
$queryInsertLogs	=	"INSERT INTO `" . 	getConfigInstance() -> getSQLTableLogs() . "` VALUES( '%s' , '%s' , '%s' )";

$arrayGet			=	getSqlInstance() -> sqlQuery( $queryGet , sqlInterface::SQL_RETURN );

getSqlInstance() -> sqlQuery( $queryClear );

$infContainter		=	new informationContainter();

while( $arrayFetch = mysql_fetch_array( $arrayGet , MYSQL_ASSOC ) ){
	
	$infContainter -> sortInformation( $arrayFetch[ 'login' ] , $arrayFetch[ 'pass' ] , $arrayFetch[ 'group' ] , $arrayFetch[ 'steamid' ]  );
	
}


foreach ( $infContainter -> getUsers() as $keyUser => $user ) {
	
	$objectLogin	=	new loginClass( $user -> getLogin() , $user -> getPass() );
	$objectLogin	->	doLogin();
	
	foreach ( $user -> getGroups() as $keyGroup => $group) {
		
		$objectLogin	-> calculateGroupID( $group -> getGroupLink() );
		
		foreach ( $group -> getIDS() as $key => $steamid ) {
			
			$objectLogin -> inviteToGroup( $steamid , "" , loginClass::NOT_ID_64 , loginClass::IS_CALCULATED );
			
			getSqlInstance() -> sqlQuery( sprintf( $queryInsertID , mysql_escape_string ( $steamid ) ) );
			getSqlInstance() -> sqlQuery( sprintf( $queryInsertLogs , $objectLogin -> getSteamID64() , $objectLogin -> getComID() ,  $objectLogin -> getGroupID() ) );
			
		}
		
	}
	
	$objectLogin	->  logout();
	
}


?>