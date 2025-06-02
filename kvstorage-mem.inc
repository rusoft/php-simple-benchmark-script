<?php
/**
 * key-value storage in-memory / array
 * Php 5.2+
 */

class KVStorageInMemory
{
    private $storage=null;
    public function __construct(){
        $this->storage = array();
    }

    public function set($key, $value){
	$this->storage[$key]=$value;
	return true;
    }

    public function get($key, $default=null){
        if (!isset($this->storage[$key])){
	    return $default;
	} else {
	    return $this->storage[$key];
	}
    }

    public function del($key){
        if (!isset($this->storage[$key])){
	    return false;
	} else {
	    unset($this->storage[$key]);
	    return true;
	}
    }
}
$kvstorage=new KVStorageInMemory();
