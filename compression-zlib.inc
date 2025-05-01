<?php
/**
 * php test functions for compression by zlib
 * Php 4.4+
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

function test_36_01_zlib_deflate_compress()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps, $stringTest;

	if (!function_exists('gzdeflate')) {
		return $emptyResult;
	}

	$count = $testsLoopLimits['36_01_zlib_deflate_compress'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		$r = gzdeflate($stringTest, 1);
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_36_02_zlib_gzip_compress()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps, $stringTest;

	if (!function_exists('gzencode')) {
		return $emptyResult;
	}

	$count = $testsLoopLimits['36_02_zlib_gzip_compress'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		$r = gzencode($stringTest, 1);
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}


