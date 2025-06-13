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
	if (!$this->available) return false;
	return apcu_store('k'.$key, $value, $timeout);
    }

    public function get($key, $default=null){
	if (!$this->available) return false;
	return apcu_exists('k'.$key)
		? apcu_fetch('k'.â$key)
		: $default;
    }

    public function del($key){
	if (!$this->available) return false;
	return apcu_delete('k'.$key);
    }
}
$kvstorage=new KVStorageApcu();
