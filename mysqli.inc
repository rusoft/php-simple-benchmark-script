<?php
/**
 * php -mod-mysqli test/generic functions
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

function get_mysqli_conn()
{
    $cfg = array(
	'host'=>'localhost',
	'port'=>3306,
	'database'=>'test',
	'username'=>'root',
	'password'=>'root',
    );
    if (is_file('mysqli-conn.ini')){
	$cfg=parse_ini_file('mysqli-conn.ini');
//	print_r($cfg);
    }
//	print_r($cfg);
	try{
	$conn = @mysqli_connect($cfg['host'], $cfg['username'], $cfg['password'], $cfg['database'], $cfg['port']);
	} catch (Exception $e) {
	$conn = false;
	}
    return $conn;
}

function get_mysqli_version()
{
    $c = get_mysqli_conn();
    if ($c){
        $infos = mysqli_get_server_info($c);
        $infoc = mysqli_get_client_info($c);
//	print_r($info);
	return 'server: '.$infos.'; client: '.$infoc;
    }
    return false;
}
