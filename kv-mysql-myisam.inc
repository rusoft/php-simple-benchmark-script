<?php
/**
 * php safe options - only tests mod mysql
 * Php 4.0+
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

// ------------------------- INTL tests -----------------------

/**
 * @since 4.0
 */
function test_45_01_kvs_mysql_myisam()
{
	global $testsLoopLimits, $totalOps, $emptyResult;
	global $sqlite3_dbpath, $debugMode;

	if (!is_file('kvstorage-mysql.inc')){
		print("storage no file");
		return $emptyResult;
	}
	if (!function_exists('mysql_query')){
		ptint("no func");
		return $emptyResult;
	}

	include_once('kvstorage-mysql.inc');
	$kvstorage=new KVStorageMysql();
	$kvstorage->engineName='MyISAM';
	$kvstorage->open();
	$kvstorage->drop();
	$kvstorage->create();
	if (!$kvstorage->available){
		print("storage noavail");
		return $emptyResult;
	}

	$count = $testsLoopLimits['45_01_kvs_mysql_myisam'];
	$time_start = get_microtime();

	for ($i = 0; $i < $count; $i++) {
		$num = $i / 100.;
		$kvstorage->set($i, $num);
		$v=$kvstorage->get($i);
		$kvstorage->del($i);
	}
	$kvstorage->close();
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}
