<?php
/**
 * Common php test functions
 * Php 4.4+
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

function test_11_IGB_serialize()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps;

	if (!function_exists('igbinary_serialize')) {
		return $emptyResult;
	}

	$data = array(
		$stringTest,
		123456,
		123.456,
		array(123456),
		null,
		false,
	);
	$obj = new stdClass();
	$obj->fieldStr = 'value';
	$obj->fieldInt = 123456;
	$obj->fieldFloat = 123.456;
	$obj->fieldArray = array(123456);
	$obj->fieldNull = null;
	$obj->fieldBool = false;
	$data[] = $obj;

	$count = $testsLoopLimits['11_igb_serialize'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		foreach ($data as $value) {
			$r = igbinary_serialize($value);
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_12_IGB_Unserialize()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps;

	if (!function_exists('igbinary_unserialize')) {
		return $emptyResult;
	}

	$data = array(
		$stringTest,
		123456,
		123.456,
		array(123456),
		null,
		false,
	);
	$obj = new stdClass();
	$obj->fieldStr = 'value';
	$obj->fieldInt = 123456;
	$obj->fieldFloat = 123.456;
	$obj->fieldArray = array(123456);
	$obj->fieldNull = null;
	$obj->fieldBool = false;
	$data[] = $obj;

	foreach ($data as $key => $value) {
		$data[$key] = igbinary_serialize($value);
	}

	$count = $testsLoopLimits['12_igb_unserialize'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		foreach ($data as $value) {
			$r = igbinary_unserialize($value);
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}
