<?php
/**
 * php test functions for compression by module bzip2 
 * Php 4.4+
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

function test_36_lz4_compress()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps, $stringTest;

	if (!function_exists('lz4_compress')) {
		return $emptyResult;
	}

	$count = $testsLoopLimits['36_bzip2_compress'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		$r = lz4_compress($stringTest);
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}


