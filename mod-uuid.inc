<?php
/**
 * module Uuuid test functions
 * Php 5.2+
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

function test_38_02_mod_uuid()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps;

//	print_r(get_defined_functions());
	if (!function_exists('uuid_create')) {
		print("Function don't exists!\n"); 
		return $emptyResult;
	}

	$count = $testsLoopLimits['38_02_mod_uuid'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		$uuid = uuid_create();
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}
