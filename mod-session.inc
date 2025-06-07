<?php
/**
 * module session test functions
 * Php 4.0
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

function test_44_session_time()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps;

	if (!function_exists('session_start')) {
		return $emptyResult;
	}
	if (!function_exists('session_destroy')) {
		return $emptyResult;
	}

	$count = $testsLoopLimits['44_session_time'];
	$time_start = get_microtime();
	setlocale(LC_CTYPE, 'POSIX');
	for ($i = 0; $i < $count; $i++) {
		if (@session_start()){
			$SESSION['time']=get_microtime();
			session_destroy();
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}
