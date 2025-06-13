<?php
/**
 * php -mod-pgsql test/generic functions
 */

/** ---------------------------------- Tests functions -------------------------------------------- */
static $pgconn=null;

function get_pgsql_conn()
{
    global $ppgconn;
    $cfg = array(
	'host'=>'localhost',
	'port'=>5432,
	'dbname'=>'sbtest',
	'user'=>'sbtest',
	'password'=>'123',
    );
    if (is_file('pgsql-conn.ini')){
	$cfg=parse_ini_file('pgsql-conn.ini');
//	print_r($cfg);
    }
//	print_r($cfg);
	$conn = "host=${cfg['host']}"
	." port=${cfg['port']}"
	." dbname=${cfg['dbname']}"
	." user=${cfg['user']}"
	." password=${cfg['password']}";
//	print("pgconn:$conn\n");
	$pgconn = pg_connect($conn);
    
    return $pgconn;
}

function get_pgsql_version()
{
    $c = get_pgsql_conn();
    if ($c){
	if (PHP_VERSION<'5.0')return '-.-.-';
        $info = pg_version($c);
//	print_r($info);
	if (isset($info["server"]))
		return 'server: '.$info["server"].'; client: '.$info['client'];
	return 'client: '.$info["client"];
    }
    return false;
}
