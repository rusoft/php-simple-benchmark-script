<?php
/**
 * module ctype test functions
 * Php 4.0
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

function test_42_ctype_isdigit()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps;

	if (!function_exists('ctype_digit')) {
		return $emptyResult;
	}

	$count = $testsLoopLimits['42_ctype_isdigit'];
	$time_start = get_microtime();
	$s=''.time();
	$d=0;
	for ($i = 0; $i < $count; $i++) {
		if (ctype_digit($s)) $d++;
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}
