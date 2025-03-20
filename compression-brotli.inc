<?php
/**
 * php test functions for compression by brotli
 * Php 5.2+
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

function test_36_brotli_compress()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps, $stringTest;

	if (!function_exists('brotli_compress')) {
		return $emptyResult;
	}

	$count = $testsLoopLimits['36_brotli_compress'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		$r = brotli_compress($stringTest, 1);
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}
