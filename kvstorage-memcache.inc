<?php
/**
 * Php 5.2+
 */

class KVStorageMemcache
{
    public $available = false;
    private $object=null;
    public function __construct(){
        $this->available=true;
	if (!class_exists('Memcache'))
	    $this->available=false;
	if (!function_exists('get_memcache_object')) include('memcache.inc');
	$this->object=get_memcache_object();
    }

    public function set($key, $value, $timeout=60){
	return $this->object->set($key, $value,0, $timeout);
    }

    public function get($key, $default=null){
	return $this->object->get($key);
    }

    public function del($key){
	return $this->object->delete($key);
    }
}
$kvstorage=new KVStorageMemcache();
