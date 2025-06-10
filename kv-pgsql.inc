<?php
/**
 * php safe options - only tests mod pgsql
 * Php 4.0+
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

// ------------------------- INTL tests -----------------------

/**
 * @since 4.0
 */
function test_47_kvs_pgsql()
{
	global $testsLoopLimits, $totalOps, $emptyResult;
	global $sqlite3_dbpath, $debugMode;

	if (!is_file('kvstorage-pgsql.inc')){
		print("storage no file");
		return $emptyResult;
	}
	if (!function_exists('pg_query')){
		ptint("no func");
		return $emptyResult;
	}

	include_once('kvstorage-pgsql.inc');
	$kvstorage=new KVStoragePgsql();
	$kvstorage->open();
	$kvstorage->drop();
	$kvstorage->create();
	if (!$kvstorage->available){
		print("storage noavail");
		return $emptyResult;
	}

	$count = $testsLoopLimits['47_kvs_pgsql'];
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
