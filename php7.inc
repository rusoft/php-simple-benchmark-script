<?php
/**
 * PHP 7.x only functions tests
 */

function test_22_Loop_Null_Op()
{
	global $testsLoopLimits, $totalOps;

	$a = array(0 => 0, 2 => 2, 4 => 4);

	$count = $testsLoopLimits['22_loop_nullop'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		$r = $a[$i % 2] ?? 0;
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_23_Loop_Spaceship_Op()
{
	global $testsLoopLimits, $totalOps;

	$count = $testsLoopLimits['23_loop_spaceship'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		$r = $i % 5 <=> 2;
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}
