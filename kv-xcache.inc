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
function test_39_02_kvstorage_xcache()
{
	global $testsLoopLimits, $totalOps, $emptyResult;

	if (!is_file('kvstorage-xcache.inc')){
		return $emptyResult;
	}

	include_once('kvstorage-xcache.inc');
	if (!$kvstorage->available){
		return $emptyResult;
	}

	$count = $testsLoopLimits['39_02_kvstorage_xcache'];
	$time_start = get_microtime();

	for ($i = 0; $i < $count; $i++) {
		$num = $i / 100.;
		$kvstorage->set($i, $num);
		$v=$kvstorage->get($i);
		if ($v===$num) $kvstorage->del($i);
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}
