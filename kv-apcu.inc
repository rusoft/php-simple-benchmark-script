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
function test_39_03_kvstorage_apcu()
{
	global $testsLoopLimits, $totalOps, $emptyResult;
	global $debugMode;

	if (!is_file('kvstorage-apcu.inc')){
		return $emptyResult;
	}

	include_once('kvstorage-apcu.inc');
	if (!$kvstorage->available){
		return $emptyResult;
	}

	$count = $testsLoopLimits['39_03_kvstorage_apcu'];
	$time_start = get_microtime();

	if ($debugMode) {
		var_dump($count);
		var_dump($kvstorage);
	}

	for ($i = 0; $i < $count; $i++) {
		$num = number_format($i / 100., 2, '.', '');
		$kvstorage->set($i, $num);
		$v=$kvstorage->get($i);
		$kvstorage->del($i);
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}
