<?php
/**
 * Php 5.2+
 */

class KVStorageShmop
{
    public $available = false;
    public function __construct(){
        $this->available=true;
	if (!function_exists('shmop_open'))
	    $this->available=false;
	if (!function_exists('shmop_write'))
	    $this->available=false;
	if (!function_exists('shmop_read'))
	    $this->available=false;
	if (!function_exists('shmop_size'))
	    $this->available=false;
	if (!function_exists('shmop_close'))
	    $this->available=false;
    }

    public function set($key, $value, $timeout=60){
	$id=shmop_open($key, "a", 0, 0);
	$ret=shmop_write($id, $value, 0);
	shmop_close($id);
	return $ret;
    }

    public function get($key, $default=null){
	$id=shmop_open($key, "a", 0, 0);
	$size=shmop_size($id);
	$ret=shmop_read($id,0,$size);
	shmop_close($id);
	return $ret;
    }

    public function del($key){
	$id=shmop_open($key, "a", 0, 0);
	$ret=shmop_delete($id);
	shmop_close($id);
	return $ret;
    }
}
$kvstorage=new KVStorageXcache();
