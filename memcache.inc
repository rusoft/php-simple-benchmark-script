<?php
/**
 * php -mod-memcache test/generic functions
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

function get_memcache_object()
{
    $cfg = array(
	'host'=>'localhost',
	'port'=>11211
    );
    if (is_file('memcached-conn.ini')){
	$cfg=parse_ini_file('memcached-conn.ini');
    }
    static $m=null;
    if ($m===null)
        $m = new Memcache;
    if ($m->connect($cfg['host'], $cfg['port']))
	return $m;
    return false;
}

function get_memcached_version()
{
    $m = get_memcache_object();
    if ($m)
        return $m->getVersion();
    return false;
}
