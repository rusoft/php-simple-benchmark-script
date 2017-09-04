<?php

/**
 * Special test only for php 5+.
 * php 4.x can't compile try construction.
 */
function test_21_0_Loop_Exception_None()
{
	global $testsLoopLimits;

	$count = $testsLoopLimits['21_loop_except'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		$a = $i;
	}
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_21_1_Loop_Exception_Try()
{
	global $testsLoopLimits;

	$count = $testsLoopLimits['21_loop_except'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		try {
			$a = $i;
		} catch (Exception $e) {
		}
	}
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_21_2_Loop_Exception_Catch()
{
	global $testsLoopLimits;

	$count = $testsLoopLimits['21_loop_except'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		try {
			$a = $i;
			throw new Exception($i);
		} catch (Exception $e) {
		}
	}
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}
