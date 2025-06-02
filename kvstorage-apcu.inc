<?php
/**
 * Php 5.2+
 */

class KVStorageApcu
{
    public $available = false;
    public function __construct(){
        $this->available=true;
	if (!function_exists('apcu_store'))
	    $this->available=false;
	if (!function_exists('apcu_fetch'))
	    $this->available=false;
	if (!function_exists('apcu_delete'))
	    $this->available=false;
    }

    public function set($key, $value, $timeout=60){
	return apcu_store($key, $value, $timeout);
    }

    public function get($key, $default=null){
	    return apcu_exists($key)
		? apcu_fetch($key)
		: $default;
    }

    public function del($key){
	return apcu_delete($key);
    }
}
$kvstorage=new KVStorageXcache();
