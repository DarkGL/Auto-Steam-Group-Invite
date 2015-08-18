<?php

include_once( "configInterface.php" );
include_once( "sqlInterface.php" );

$toInviteTable	=	"CREATE TABLE IF NOT EXISTS `" . getConfigInstance() -> getSQLTableInvite() . "` (  `steamid` VARCHAR( 40 ) , `group` VARCHAR( 256 ) , `login` VARCHAR( 40 ) , `pass` VARCHAR( 40 ) , UNIQUE ( `steamid` , `group` ) )";
$steamIDSTable	=	"CREATE TABLE IF NOT EXISTS `" . getConfigInstance() -> getSQLTableContainer() . "` ( `steamid64` VARCHAR( 80 ) UNIQUE )";
$logsTable		=	"CREATE TABLE IF NOT EXISTS `" . getConfigInstance() -> getSQLTableLogs() . "` ( `steamid64inviter` VARCHAR( 80 ) , `steamid64invited` VARCHAR( 80 )  , `groupid` VARCHAR( 80 ) )";

$objectSql	=	getSqlInstance();

$objectSql	-> sqlQuery( $toInviteTable );
$objectSql	-> sqlQuery( $steamIDSTable );
$objectSql	-> sqlQuery( $logsTable );

?>