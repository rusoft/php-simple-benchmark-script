<?php
/**
 * Php 5.2+
 */

class KVStorageXcache
{
    public $available = false;
    public function __construct(){
        $this->available=true;
	if (!function_exists('xcache_set'))
	    $this->available=false;
	if (!function_exists('xcache_get'))
	    $this->available=false;
	if (!function_exists('xcache_unset'))
	    $this->available=false;
    }

    public function set($key, $value, $timeout=60){
	return xcache_set($key, $value, $timeout);
    }

    public function get($key, $default=null){
	    return xcache_isset($key)
		? xcache_get($key)
		: $default;
    }

    public function del($key){
	return xcache_unset($key);
    }
}
$kvstorage=new KVStorageXcache();
