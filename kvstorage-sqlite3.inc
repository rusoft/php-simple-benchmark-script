<?php
/**
 * mod-sqlite3
 * Php 5.2+
 */

class KVStorageSqlite3
{
    public $available = false;
    private $db=null;
    private $tableName='storage';
    public function __construct(){
        $this->available=true;
	if (!class_exists('SQLite3'))
	    $this->available=false;
	else
	    $this->db=new SQLite3(':memory:');
	$this->create();
    }

    public function open($full_path){
	if (!$this->available) return false;
	if ($this->db===null){
		$this->db=new SQLite3($full_path);
		return true;
	}
    }

    public function close(){
	if (!$this->available) return false;
	$this->db->close();
	$this->db=null;
	return true;
    }

    public function version(){
	if (!$this->available) return '-.-.-';
	$v=$this->db->version();
	if($v) return $v['versionString'];
    }

    public function set($key, $value, $timeout=60){
	if (!$this->available) return 0;
	if ($this->get($key))
		$this->db->exec("UPDATE {$this->tableName} SET val='$value' WHERE key=$key;");
	else
		$this->db->exec("INSERT INTO {$this->tableName} (key, val) VALUES ($key, '$value');");
	return $this->db->changes();
    }

    public function get($key, $default=null){
	if (!$this->available) return false;
	$row = $this->db->querySingle("SELECT * FROM {$this->tableName} WHERE key=$key",1);
	if ($row) {
		return $row['val'];
	}
	return false;
    }

    public function del($key){
	if (!$this->available) return 0;
	$this->db->exec("DELETE {$this->tableName} WHERE key=$key;");
	return $this->db->changes();
    }

    public function create(){
	return $this->db->exec("CREATE TABLE `{$this->tableName}`(key INTEGER, val TEXT); CREATE INDEX skey ON {$this->tableName}(key)");
    }
}
//$kvstorage=new KVStorageSqlite3();
