#include <sourcemod>
#include <regex>

public Plugin:myinfo = 
{
	name = "invite Plugin",
	author = "Cypis",
	description = "Opis plugina",
	version = "1.0",
	url = "www.amxx.pl"
}

#define HARDCODED

#if defined HARDCODED

new String:accLogin[] = "login";
new String:accPass[] = "pass";

#else

new Handle:pCvarAccLogin, Handle:pCvarAccPass;
new String:accLogin[64];
new String:accPass[64];

#endif

new String:szGroup[64];
new Handle:g_hDb = INVALID_HANDLE;

public OnPluginStart()
{
	#if !defined HARDCODED
	
	pCvarAccLogin	=	CreateConVar("invite_acc_login", "login", "login do konta steam", FCVAR_PROTECTED);
	pCvarAccPass	=	CreateConVar("invite_acc_pass", "pass", "haslo do konta steam", FCVAR_PROTECTED);
	
	#endif
	
	new Handle:pCvarGroup = CreateConVar("invite_group_link", "grouplink", "link do grupy steam", FCVAR_PROTECTED);
	AutoExecConfig(true, "invite");
	
	#if !defined HARDCODED
	
	GetConVarString(pCvarAccLogin, accLogin, sizeof(accLogin));
	GetConVarString(pCvarAccPass, accPass, sizeof(accPass));
	
	#endif
	
	GetConVarString(pCvarGroup, szGroup, sizeof(szGroup));
	
	new Handle:regexHandle = CompileRegex("((http|https)*(://)*)steamcommunity.com/groups/.*/*");
	if(MatchRegex(regexHandle, szGroup) == -1)
	{
		LogError("RegEx Invite");
		SetFailState("Sprawdzanie linku grupy sie nie powiodlo");
	}
	
	if(!SQL_CheckConfig("invite"))
		return;
	
	new String:Error[255];
	g_hDb = SQL_Connect("invite", true, Error, 255);
	if(g_hDb == INVALID_HANDLE)		
	{
		LogError("Nie mozna sie polaczyc z baza (invite): %s", Error);
		return;
	}
}

public OnClientPostAdminCheck(client)
{
	if(g_hDb == INVALID_HANDLE)	
		return;

	if(IsFakeClient(client))
		return;
	
	decl String:szSteamID[60], String:szQuery[256];
	GetClientAuthString(client, szSteamID, sizeof(szSteamID));
	
	Format(szQuery, sizeof(szQuery), "SELECT * FROM `steaminv_tableToInvite` WHERE `steamid` = '%s' AND `group` = '%s'", szSteamID, szGroup);
	SQL_TQuery(g_hDb, checkClientHandle, szQuery, GetClientUserId(client));
}

public checkClientHandle(Handle:owner, Handle:hndl, const String:error[], any:data)
{
	new client;
	if((client = GetClientOfUserId(data)) == 0)
		return;
	
	if(hndl == INVALID_HANDLE)
	{
		LogError("Error on checkClientHandle query: %s", error);
		return;
	}
	
	if(SQL_FetchRow(hndl))
		return;
	
	decl String:szSteamID[60], String:szQuery[256];
	GetClientAuthString(client, szSteamID, sizeof(szSteamID));
	
	Format(szQuery, sizeof(szQuery), "INSERT INTO `tableToInvite` VALUES('%s', '%s', '%s', '%s')", szSteamID, szGroup, accLogin, accPass);
	SQL_TQuery(g_hDb, insertClientHandle, szQuery);
}

public insertClientHandle(Handle:owner, Handle:hndl, const String:error[], any:data)
{
	if(!StrEqual("", error))
	{
		LogError("Error on insertClientHandle query: %s", error);
		return;
	}
}