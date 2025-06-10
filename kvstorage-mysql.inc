<?php
/**
 * mod-mysql
 * Php 4.0+
 */

class KVStorageMysql
{
    public $available = false;
    private $conn=null;
    private $tableName='storage';
    public $engineName='MyISAM';

    public function __construct(){
        $this->available=true;
	if (!function_exists('mysql_query'))
	    $this->available=false;
	else {
	    $this->open();
	}
	$this->create();
    }

    public function open(){
	if (!$this->available) return false;
	if ($this->conn===null){
	    include_once('mysql.inc');
	    $this->conn=get_mysql_conn();
	    return true;
	}
    }

    public function close(){
	if (!$this->available) return false;
	mysql_close($this->conn);
	$this->conn=null;
	return true;
    }

    public function version(){
	if (!$this->available) return '-.-.-';
	$v=mysql_get_server_info($this->conn);
	if($v) return $v;
    }

    public function set($key, $value, $timeout=60){
	if (!$this->available) return 0;
	if ($this->get($key))
		mysql_unbuffered_query("UPDATE {$this->tableName} SET val='$value' WHERE key=$key;", $this->conn);
	else
		mysql_unbuffered_query("INSERT INTO {$this->tableName} (key, val) VALUES ($key, '$value');", $this->conn);
	return mysql_affected_rows($this->conn);
    }

    public function get($key, $default=null){
	if (!$this->available) return false;
	$res = mysql_query("SELECT * FROM {$this->tableName} WHERE key=$key", $this->conn);
	$row=null;
	if ($res) $row = mysql_fetch_assoc($res);
	if ($row) {
		mysql_free_result($res);
		return $row['val'];
	}
	return false;
    }

    public function del($key){
	if (!$this->available) return 0;
	mysql_unbuffered_query("DELETE FROM {$this->tableName} WHERE key=$key;", $this->conn);
	return mysql_affected_rows($this->conn);
    }

    public function create(){
	return mysql_unbuffered_query("CREATE TABLE `{$this->tableName}`(key INTEGER, val CHAR(20)) engine {$this->engineName}; CREATE INDEX skey ON {$this->tableName}(key)", $this->conn);
    }

    public function drop(){
	return mysql_unbuffered_query("DROP TABLE `{$this->tableName}`;", $this->conn);
    }
}
//$kvstorage=new KVStorageSqlite3();
