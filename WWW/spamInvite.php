<?php

include( "steam/loginClass.php" );
include( "configInterface.php" );
include( "sqlInterface.php" );

function checkGet(){
	isset( $_GET[ 'login' ] ) && isset( $_GET[ 'pass' ] ) && isset( $_GET[ 'group' ] ) && isset( $_GET[ 'page' ] ) or die( "Nic nicosc kompletna pustosc. Check GET" );
}

checkGet();

$recordsAmount	=	200;
$login			=	$_GET[ 'login' ];
$pass			=	$_GET[ 'pass' ];
$groupUrl		=	$_GET[ 'group' ];
$iPage			=	$_GET[ 'page' ];

$queryRand		=	"SELECT `steamid64` FROM `" . getConfigInstance() -> getSQLTableContainer() . "` LIMIT " . $iPage * $recordsAmount . "," . $recordsAmount . "200"; // I know it isn't very effective

$arrayResult	=	getSqlInstance() -> sqlQuery( $queryRand , sqlInterface::SQL_RETURN );

$objectLogin	=	new loginClass( $login , $pass );
$objectLogin	->	doLogin();

if( $objectLogin -> isNTLogged() ){
	
	die( "Wrong login or pass" );
	
}

$objectLogin	-> calculateGroupID( $groupUrl );

while( $arrayResult = mysql_fetch_array( $arrayGet , MYSQL_ASSOC ) ){
	
	$objectLogin -> inviteToGroup( mysql_escape_string( $arrayResult[ 'steamid64' ] ) , "" , loginClass::NOT_ID_64 , loginClass::IS_CALCULATED );
	
}

$objectLogin	->	logout();

?>