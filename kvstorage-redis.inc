<?php
/**
 * Php 5.2+
 */

class KVStorageRedis
{
    public $available = false;
    private $object=null;
    public function __construct(){
        $this->available=true;
	if (!class_exists('Redis'))
	    $this->available=false;
	include('redis.inc');
	$this->object=get_redis_object();
    }

    public function set($key, $value, $timeout=60){
	return $this->object->set($key, $value,0, $timeout);
    }

    public function get($key, $default=null){
	return $this->object->get($key);
    }

    public function del($key){
	return $this->object->expire($key,0);
    }
}
$kvstorage=new KVStorageRedis();
