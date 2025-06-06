<?php
/**
 * php -mod-sqlite test/generic functions
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

function get_sqlite3_object()
{
    global $sqlite3_dbpat, $debugMode;
    if ($debugMode) var_dump($sqlite3_dbpath);
    if (!$sqlite3_dbpath) $sqlite3_dbpath=':memory:';
    static $s=null;
    if ($s!==null){$s->close(); $s=null;}
    if ($s===null)
        $s = new SQLite3($sqlite3_dbpath);
	return $s;
    return false;
}

function get_sqlite3_version()
{
    $s = get_sqlite3_object();
    if ($s){
        $info = $s->version();
//	print_r($info);
	return $info['versionString'];
    }
    return false;
}
