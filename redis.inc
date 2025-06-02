<?php
/**
 * php -mod-memcache test/generic functions
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

function get_redis_object()
{
    $cfg = array(
	'host'=>'localhost',
	'port'=>6379,
	'database'=>2,
    );
    if (is_file('redis-conn.ini')){
	$cfg=parse_ini_file('redis-conn.ini');
    }
    static $r=null;
    if ($r===null)
        $r = new Redis;
    if ($r->connect($cfg['host'], $cfg['port'])){
	$r->swapdb(0, (int)$cfg['database']);
	return $r;
    }
    return false;
}

function get_redis_version()
{
    $r = get_redis_object();
    if ($r){
        $info = $r->info('server');
//	print_r($info);
	return $info['redis_version'];
    }
    return false;
}
