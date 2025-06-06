<?php
/**
 * module sodium test functions
 * Php 7.2+
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

function test_41_01_sodium_string_num_int()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps;

	if (!function_exists('sodium_increment')) {
		return $emptyResult;
	}
	$count = $testsLoopLimits['41_01_sodium_string_num_int'];
	$time_start = get_microtime();
	$num=''.time();
	for ($i = 0; $i < $count; $i++) {
		sodium_increment($num);
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_41_02_sodium_string_num_float()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps;

	if (!function_exists('sodium_increment')) {
		return $emptyResult;
	}
	$count = $testsLoopLimits['41_02_sodium_string_num_float'];
	$time_start = get_microtime();
	$num=''.microtime(true);
	for ($i = 0; $i < $count; $i++) {
		sodium_increment($num);
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}
