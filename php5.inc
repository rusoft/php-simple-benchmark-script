<?php

/**
 * Special test only for php 5+.
 * php 4.x can't compile try construction.
 */
function test_21_Loop_ExceptionTryCatch()
{
	global $testsLoopLimits;

	$count = $testsLoopLimits['21_loop_except'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		try {
			throw new Exception($i);
		} catch (Exception $e) {
		}
	}
	return format_result_test(get_microtime() - $time_start, $count, memory_get_usage(true));
}
