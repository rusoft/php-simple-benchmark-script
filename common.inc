<?php
/**
 * Common php test functions
 * Php 4.4+
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

function test_01_Math()
{
	global $testsLoopLimits, $totalOps;

	$mathFunctions = array('abs', 'acos', 'asin', 'atan', 'decbin', 'dechex', 'decoct', 'floor', 'exp', 'log1p', 'sin', 'tan', 'is_finite', 'is_nan', 'sqrt', 'rad2deg');
	foreach ($mathFunctions as $key => $function) {
		if (!function_exists($function)) {
			unset($mathFunctions[$key]);
		}
	}

	$count = $testsLoopLimits['01_math'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		foreach ($mathFunctions as $function) {
			$r = call_user_func_array($function, array($i));
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_02_String_Concat()
{
	global $testsLoopLimits, $stringConcatLoopRepeat, $totalOps;

	$count = $testsLoopLimits['02_string_concat'];
	$time_start = get_microtime();
	for ($r = 0; $r < $stringConcatLoopRepeat; ++$r) {
		$s = '';
		for ($i = 0; $i < $count; ++$i) {
			$s .= ' - Valar dohaeris';
			// Work-around for opCache 8.0+ code elimination, maybe
			$s .= ($i % 111) ? PHP_EOL : '!';
		}
	}
	$totalOps += $count * $stringConcatLoopRepeat;
	$memory = mymemory_usage();
	unset($s);
	return format_result_test(get_microtime() - $time_start, $count * $stringConcatLoopRepeat, $memory);
}

function test_03_1_String_Number_Concat()
{
	global $testsLoopLimits, $totalOps;

	$count = $testsLoopLimits['03_1_string_number_concat'];
	$time_start = get_microtime();
	$s = '';
	for ($i = 0; $i < $count; ++$i) {
		$f = $i * 1.0;
		$s = 'This is number ' . $i . ' string concat. Число: ' . $f . PHP_EOL;
		// Work-around for opCache 8.0+ code elimination, maybe
		$s .= (($i % 333 == 0) ? '!' : '');
	}
	$totalOps += $count;
	$memory = mymemory_usage();
	unset($s);
	return format_result_test(get_microtime() - $time_start, $count, $memory);
}

function test_03_2_String_Number_Format()
{
	global $testsLoopLimits, $totalOps;

	$count = $testsLoopLimits['03_2_string_number_format'];
	$time_start = get_microtime();
	$s = '';
	for ($i = 0; $i < $count; ++$i) {
		$f = $i * 1.0;
		$s = "This is number {$i} string format. Число: {$f}\n";
		// Work-around for opCache 8.0+ code elimination, maybe
		$s .= (($i % 333 == 0) ? '!' : '');
	}
	$totalOps += $count;
	$memory = mymemory_usage();
	unset($s);
	return format_result_test(get_microtime() - $time_start, $count, $memory);
}

function test_04_String_Simple_Functions()
{
	global $stringTest, $testsLoopLimits, $totalOps;

	$stringFunctions = array('strtoupper', 'strtolower', 'strrev', 'strlen', 'str_rot13', 'ord', 'trim');
	foreach ($stringFunctions as $key => $function) {
		if (!function_exists($function)) {
			unset($stringFunctions[$key]);
		}
	}

	$count = $testsLoopLimits['04_string_simple'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		foreach ($stringFunctions as $function) {
			$r = call_user_func_array($function, array($stringTest));
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_05_String_Multibyte()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps;

	if (!function_exists('mb_strlen')) {
		return $emptyResult;
	}

	$stringFunctions = array('mb_strtoupper', 'mb_strtolower', 'mb_strlen', 'mb_strwidth');
	foreach ($stringFunctions as $key => $function) {
		if (!function_exists($function)) {
			unset($stringFunctions[$key]);
		}
	}

	$count = $testsLoopLimits['05_string_mb'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		foreach ($stringFunctions as $function) {
			$r = call_user_func_array($function, array($stringTest));
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_06_String_Manipulation()
{
	global $stringTest, $testsLoopLimits, $totalOps;

	$stringFunctions = array('addslashes', 'chunk_split', 'metaphone', 'strip_tags', 'soundex', 'wordwrap');
	foreach ($stringFunctions as $key => $function) {
		if (!function_exists($function)) {
			unset($stringFunctions[$key]);
		}
	}

	$count = $testsLoopLimits['06_string_manip'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		foreach ($stringFunctions as $function) {
			$r = call_user_func_array($function, array($stringTest));
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_07_Regex()
{
	global $stringTest, $regexPattern, $testsLoopLimits, $totalOps;

	$count = $testsLoopLimits['07_regex'];
	$time_start = get_microtime();
	$stringFunctions = array('preg_match', 'preg_split');
	foreach ($stringFunctions as $key => $function) {
		if (!function_exists($function)) {
			unset($stringFunctions[$key]);
		}
	}
	for ($i = 0; $i < $count; $i++) {
		foreach ($stringFunctions as $function) {
			$r = call_user_func_array($function, array($regexPattern, $stringTest));
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_08_1_Hashing()
{
	global $stringTest, $testsLoopLimits, $totalOps;

	$stringFunctions = array('crc32', 'md5', 'sha1');
	foreach ($stringFunctions as $key => $function) {
		if (!function_exists($function)) {
			unset($stringFunctions[$key]);
		}
	}

	$count = $testsLoopLimits['08_1_hashing'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		foreach ($stringFunctions as $function) {
			$r = call_user_func_array($function, array($stringTest));
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_08_2_Crypt()
{
	global $stringTest, $cryptSalt, $testsLoopLimits, $totalOps;

	$stringFunctions = array('crypt');
	foreach ($stringFunctions as $key => $function) {
		if (!function_exists($function)) {
			unset($stringFunctions[$key]);
		}
	}

	$count = $testsLoopLimits['08_2_crypt'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		foreach ($stringFunctions as $function) {
			$r = call_user_func_array($function, array($stringTest, $cryptSalt));
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_09_Json_Encode()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps;

	if (!function_exists('json_encode')) {
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

	$count = $testsLoopLimits['09_json_encode'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		foreach ($data as $value) {
			$r = json_encode($value);
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_10_Json_Decode()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps;

	if (!function_exists('json_decode')) {
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
		$data[$key] = json_encode($value);
	}

	$count = $testsLoopLimits['10_json_decode'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		foreach ($data as $value) {
			$r = json_decode($value);
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_11_Serialize()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps;

	if (!function_exists('serialize')) {
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

	$count = $testsLoopLimits['11_serialize'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		foreach ($data as $value) {
			$r = serialize($value);
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_12_Unserialize()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps;

	if (!function_exists('unserialize')) {
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
		$data[$key] = serialize($value);
	}

	$count = $testsLoopLimits['12_unserialize'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		foreach ($data as $value) {
			$r = unserialize($value);
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_13_Array_Fill()
{
	global $testsLoopLimits, $arrayDimensionLimit, $totalOps;

	$arrayTestLoopLimit = $testsLoopLimits['13_array_loop'];
	$time_start = get_microtime();
	$memory = 0;
	for ($n = 0; $n < $arrayTestLoopLimit; ++$n) {
		$x = array();
		for ($i = 0; $i < $arrayDimensionLimit; ++$i) {
			for ($j = 0; $j < $arrayDimensionLimit; ++$j) {
				$x[$i][$j] = $i * $j;
			}
		}
		if (!$memory) $memory = mymemory_usage();
		unset($x);
	}
	$totalOps += pow($arrayDimensionLimit, 2) * $arrayTestLoopLimit;
	return format_result_test(get_microtime() - $time_start, pow($arrayDimensionLimit, 2) * $arrayTestLoopLimit, $memory);
}

function test_14_Array_Range()
{
	global $testsLoopLimits, $arrayDimensionLimit, $totalOps;

	$arrayTestLoopLimit = $testsLoopLimits['14_array_loop'];
	$time_start = get_microtime();
	$memory = 0;
	for ($n = 0; $n < $arrayTestLoopLimit; ++$n) {
		$x = array()+range(0, $arrayDimensionLimit);
		for ($i = 0; $i < $arrayDimensionLimit; $i++) {
			$x[$i] = array()+range(0, $arrayDimensionLimit);
		}
		if (!$memory) $memory = mymemory_usage();
		unset($x);
	}
	$totalOps += $arrayDimensionLimit * $arrayTestLoopLimit;
	return format_result_test(get_microtime() - $time_start, $arrayDimensionLimit * $arrayTestLoopLimit, $memory);
}

function test_14_Array_Unset()
{
	global $testsLoopLimits, $arrayDimensionLimit, $totalOps;

	$xx = range(0, $arrayDimensionLimit);
	for ($i = 0; $i < $arrayDimensionLimit; $i++) {
		$xx[$i] = array()+range(0, $arrayDimensionLimit);
	}

	$arrayTestLoopLimit = $testsLoopLimits['14_array_loop'];
	$time_start = get_microtime();
	$memory = 0;
	for ($n = 0; $n < $arrayTestLoopLimit; ++$n) {
		$x = array()+$xx;
		if (!$memory) $memory = mymemory_usage();
		for ($i = $arrayDimensionLimit-1; $i >= 0; $i--) {
			for ($j = 0; $j < $arrayDimensionLimit; $j++) {
				unset($x[$i][$j]);
			}
			unset($x[$i]);
		}
		unset($x);
	}
	unset($xx);
	$totalOps += pow($arrayDimensionLimit, 2) * $arrayTestLoopLimit;
	return format_result_test(get_microtime() - $time_start, pow($arrayDimensionLimit, 2) * $arrayTestLoopLimit, $memory);
}

function test_15_Clean_Loops()
{
	global $testsLoopLimits, $totalOps;

	$count = $testsLoopLimits['15_clean_loops'];
	$time_start = get_microtime();

	// @warning Since 8.0 may be eliminated as dead-code if opcache loaded and enabled
	for ($i = 0; $i < $count; ++$i) ;

	$i = 0;
	// @warning Since 8.0 may be eliminated as dead-code if opcache loaded and enabled
	while ($i++ < $count) ;

	$totalOps += $count * 2;
	return format_result_test(get_microtime() - $time_start, $count * 2, mymemory_usage());
}

function test_16_Loop_IfElse()
{
	global $testsLoopLimits, $totalOps;

	$count = $testsLoopLimits['16_loop_ifelse'];
	$time_start = get_microtime();
	$a = 0;
	for ($i = 0; $i < $count; $i++) {
		$d = $i % 5;
		if ($d == 1) {
			$a++;
		} elseif ($d == 2) {
			$a--;
		} else if ($d == 3) {
			$a++;
		} else {
			$a--;
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_17_Loop_Ternary()
{
	global $testsLoopLimits, $totalOps;

	$count = $testsLoopLimits['17_loop_ternary'];
	$time_start = get_microtime();
	$a = 0;
	for ($i = 0; $i < $count; $i++) {
		$r = ($i % 2 == 1)
			? (($i % 3 == 1)
				? (($i % 5 == 1)
					? 3
					: 2)
				: 1)
			: 0;
		$a += $r;
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_18_1_Loop_Defined_Access()
{
	global $testsLoopLimits, $totalOps;

	$a = array(0 => 1, 1 => 0);
	$r = 0;

	$count = $testsLoopLimits['18_1_loop_def'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		$r += $a[$i % 2];
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_18_2_Loop_Undefined_Access()
{
	global $testsLoopLimits, $totalOps;

	$a = array(1 => 1, 3 => 1);
	$r = 0;

	$count = $testsLoopLimits['18_2_loop_undef'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		$r += @$a[$i % 5] ? 0 : 1;
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_19_Type_Functions()
{
	global $testsLoopLimits, $totalOps;

	$ia = array('123456', '0.000001', '0x123', '0644');
	$fa = array('123456.7890', '123.456e7', '3E-12', '0.0000001');

	$count = $testsLoopLimits['20_type_conv'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		foreach ($ia as $n) {
			$r = intval($n);
		}
		foreach ($fa as $n) {
			$r = floatval($n);
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_20_Type_Conversion()
{
	global $testsLoopLimits, $totalOps;

	$ia = array('123456', '0.000001', '0x123', '0644');
	$fa = array('123456.7890', '123.456e7', '3E-12', '0.0000001');

	$count = $testsLoopLimits['20_type_conv'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		foreach ($ia as $n) {
			$r = (int)$n;
		}
		foreach ($fa as $n) {
			$r = (float)$n;
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}
