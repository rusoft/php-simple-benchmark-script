<?php
/**
 * php safe options - only tests array/class interfaces
 * Php 5.0+
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

// ------------------------- INTL tests -----------------------

/**
 * @since 5.0
 */
function test_39_01_kvstorage_memory()
{
	global $testsLoopLimits, $totalOps, $emptyResult;

	if (!is_file('kvstorage-mem.inc')){
		return $emptyResult;
	}

	include_once('kvstorage-mem.inc');
	$count = $testsLoopLimits['39_01_kvstorage_memory'];
	$time_start = get_microtime();

	for ($i = 0; $i < $count; $i++) {
		$num =number_format( $i / 100., 2, '.', '');
		$kvstorage->set($i, $num);
		$v=$kvstorage->get($i);
		$kvstorage->del($i);
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}
