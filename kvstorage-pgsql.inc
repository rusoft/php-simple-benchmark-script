<?php
/**
 * mod-pgsql
 * Php 5.0+
 */

class KVStoragePgsql
{
    public $available = false;
    private $conn=null;
    private $tableName='storage';

    public function __construct(){
        $this->available=true;
	if (!function_exists('pg_query'))
	    $this->available=false;
	else {
	    $this->open();
	}
	$this->create();
    }

    public function open(){
	if (!$this->available) return false;
	if ($this->conn===null){
	    include_once('pgsql.inc');
	    $this->conn=get_pgsql_conn();
	    return true;
	}
    }

    public function close(){
	if (!$this->available) return false;
	pg_close($this->conn);
	$this->conn=null;
	return true;
    }

    public function version(){
	if (!$this->available) return '-.-.-';
	$v=pg_version($this->conn);
	if($v) return $v['server'];
    }

    public function set($key, $value, $timeout=60){
	if (!$this->available) return 0;
	if ($this->get($key))
		$res = pg_query($this->conn, "UPDATE {$this->tableName} SET v='$value' WHERE k=$key;");
	else
		$res = pg_query($this->conn, "INSERT INTO {$this->tableName} (k, v) VALUES ($key, '$value');");
	$cnt = pg_affected_rows($res);
	pg_free_result($res);
	return $cnt;
    }

    public function get($key, $default=null){
	if (!$this->available) return false;
	$res = pg_query($this->conn, "SELECT * FROM {$this->tableName} WHERE k=$key");
	$row=null;
	if ($res) $row = pg_fetch_assoc($res);
	if ($row) {
		pg_free_result($res);
		return $row['v'];
	}
	return false;
    }

    public function del($key){
	if (!$this->available) return 0;
	$res = pg_query($this->conn, "DELETE FROM {$this->tableName} WHERE k=$key;");
	$cnt = pg_affected_rows($res);
	pg_free_result($res);
	return $cnt;
    }

    public function create(){
	pg_query($this->conn, "CREATE TABLE IF NOT EXISTS {$this->tableName}(k INTEGER, v CHAR(20));");
	pg_query($this->conn, " CREATE INDEX IF NOT EXISTS skey ON {$this->tableName}(k)");
    }

    public function drop(){
	return pg_query($this->conn, "DROP TABLE IF EXISTS {$this->tableName};");
    }
}
//$kvstorage=new KVStorageSqlite3();
