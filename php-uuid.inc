<?php
/**
 * php UUID test functions
 * Php 5.2+
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

include_once("UUID.php");
function test_38_01_php_uuid()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps;

//	print_r(get_declared_classes());
	if (!class_exists('UUID',false)) {
		print("Class don't exists!\n");
		return $emptyResult;
	}

	$count = $testsLoopLimits['38_01_php_uuid'];
//	print("Count runs: $count");
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		$uuid = UUID::v4();
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}
