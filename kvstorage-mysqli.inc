<?php
/**
 * mod-mysqli
 * Php 5.0+
 */

class KVStorageMysqli
{
    public $available = false;
    private $conn=null;
    private $tableName='storage';
    public $engineName='MyISAM';

    public function __construct(){
        $this->available=true;
	if (!function_exists('mysqli_query'))
	    $this->available=false;
	else {
	    $this->open();
	}
	$this->create();
    }

    public function open(){
	if (!$this->available) return false;
	if ($this->conn===null){
	    include_once('mysqli.inc');
	    $this->conn=get_mysqli_conn();
		return true;
	}
    }

    public function close(){
	if (!$this->available) return false;
	mysqli_close($this->conn);
	$this->conn=null;
	return true;
    }

    public function version(){
	if (!$this->available) return '-.-.-';
	$v=mysqli_get_server_info($this->conn);
	if($v) return $v;
    }

    public function set($key, $value, $timeout=60){
	if (!$this->available) return 0;
	if ($this->get($key))
		$res = mysqli_query($this->conn, "UPDATE {$this->tableName} SET v='$value' WHERE k=$key;");
	else
		$res = mysqli_query($this->conn, "INSERT INTO {$this->tableName} (k, v) VALUES ($key, '$value');");
	return mysqli_affected_rows($this->conn);
    }

    public function get($key, $default=null){
	if (!$this->available) return false;
	$res = mysqli_query($this->conn, "SELECT * FROM {$this->tableName} WHERE k=$key");
	$row=null;
	if ($res) $row = mysqli_fetch_assoc($res);
	if ($row) {
		mysqli_free_result($res);
		return $row['v'];
	}
	return false;
    }

    public function del($key){
	if (!$this->available) return 0;
	mysqli_query($this->conn, "DELETE FROM {$this->tableName} WHERE k=$key;");
	return mysqli_affected_rows($this->conn);
    }

    public function create(){
	mysqli_real_query($this->conn, "CREATE TABLE IF NOT EXISTS `{$this->tableName}`(k INTEGER, v CHAR(20)) engine {$this->engineName};");
	mysqli_real_query($this->conn, " CREATE INDEX IF NOT EXISTS skey ON {$this->tableName}(k)");
    }

    public function drop(){
	return mysqli_real_query($this->conn, "DROP TABLE IF EXISTS `{$this->tableName}`;");
    }
}
