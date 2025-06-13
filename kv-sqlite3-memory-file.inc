<?php
/**
 * php safe options - only tests mod sqlite3
 * Php 5.3+
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

// ------------------------- INTL tests -----------------------

/**
 * @since 5.0
 */
function test_39_09_kvs_sqlite3_memory_file()
{
	global $testsLoopLimits, $totalOps, $emptyResult;
	global $sqlite3_dbpath, $debugMode;

	if (!is_file('kvstorage-sqlite3.inc')){
		print("storage no file");
		return $emptyResult;
	}
	if (!class_exists('SQLite3')){
		ptint("no class");
		return $emptyResult;
	}

	$sqlite3_dbpath=':memory:';
	if ($debugMode) print("now db path is: $sqlite3_dbpath");
	include_once('kvstorage-sqlite3.inc');
	$kvstorage=new KVStorageSqlite3();
	$kvstorage->open($sqlite3_dbpath);
	if (!$kvstorage->available){
		print("storage noavail");
		return $emptyResult;
	}

	$count = $testsLoopLimits['39_09_kvs_sqlite3_memory_file'];
	$time_start = get_microtime();

	for ($i = 0; $i < $count; $i++) {
		$num = number_format( $i / 100., 2, '.', '');
		$kvstorage->set($i, $num);
		$v=$kvstorage->get($i);
		$kvstorage->del($i);
	}
	$kvstorage->close();
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}
