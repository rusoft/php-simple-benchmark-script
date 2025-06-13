<?php
/**
 * php -mod-mysql test/generic functions
 */

/** ---------------------------------- Tests functions -------------------------------------------- */
static $myconn=null;

function get_mysql_conn()
{
    global $myconn;
    $cfg = array(
	'host'=>'localhost',
	'port'=>3306,
	'database'=>'test',
	'username'=>'root',
	'password'=>'root',
    );
    if (is_file('mysql-conn.ini')){
	$cfg=parse_ini_file('mysql-conn.ini');
//	print_r($cfg);
    }
//	print_r($cfg);
	$myconn = @mysql_connect($cfg['host'].':'.$cfg['port'], $cfg['username'], $cfg['password'], true);
	if ($myconn)
		mysql_select_db($cfg['database'], $myconn);
    
    return $myconn;
}

function get_mysql_version()
{
    $c = get_mysql_conn();
    if ($c){
        $infos = mysql_get_server_info($c);
        $infoc = mysql_get_client_info();
//	print_r($info);
	return 'server: '. $infos.'; client: '.$infoc;
    }
    return false;
}
