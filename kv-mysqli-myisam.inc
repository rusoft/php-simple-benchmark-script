<?php
/**
 * php safe options - only tests mod mysqli
 * Php 4.0+
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

// ------------------------- INTL tests -----------------------

/**
 * @since 4.0
 */
function test_46_01_kvs_mysqli_myisam()
{
	global $testsLoopLimits, $totalOps, $emptyResult;
	global $sqlite3_dbpath, $debugMode;

	if (!is_file('kvstorage-mysqli.inc')){
		print("storage no file");
		return $emptyResult;
	}
	if (!function_exists('mysqli_query')){
		ptint("no func");
		return $emptyResult;
	}

	include_once('kvstorage-mysqli.inc');
	$kvstorage=new KVStorageMysqli();
	$kvstorage->engineName='MyISAM';
	$kvstorage->open();
	$kvstorage->drop();
	$kvstorage->create();
	if (!$kvstorage->available){
		print("storage noavail");
		return $emptyResult;
	}

	$count = $testsLoopLimits['46_01_kvs_mysqli_myisam'];
	$time_start = get_microtime();

	for ($i = 1; $i < $count; $i++) {
		$num = number_format($i / 100., 2, '.', '');
		$kvstorage->set($i, $num);
		$v=$kvstorage->get($i);
		$kvstorage->del($i);
	}
	$kvstorage->close();
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}
