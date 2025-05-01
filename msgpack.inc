<?php
/**
 * Common php test functions
 * Php 4.4+
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

function test_11_msgpack_pack()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps;

	if (!function_exists('msgpack_pack')) {
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

	$count = $testsLoopLimits['11_msgpack_pack'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		foreach ($data as $value) {
			$r = msgpack_pack($value);
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_12_msgpack_unpack()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps;

	if (!function_exists('msgpack_unpack')) {
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
		$data[$key] = msgpack_pack($value);
	}

	$count = $testsLoopLimits['12_msgpack_unpack'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		foreach ($data as $value) {
			$r = msgpack_unpack($value);
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}
