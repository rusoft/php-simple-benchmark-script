<?php
/*
##########################################################################
#                      PHP Benchmark Performance Script                  #
#                           2010 Code24 BV                               #
#                           2015 Rusoft                                  #
#                                                                        #
#  Author      : Alessandro Torrisi                                      #
#  Author      : Sergey Dryabzhinsky                                     #
#  Company     : Code24 BV, The Netherlands                              #
#  Company     : Rusoft Ltd, Russia                                      #
#  Date        : Apr 6, 2017                                             #
#  version     : 1.0.8                                                   #
#  License     : Creative Commons CC-BY license                          #
#  Website     : http://www.php-benchmark-script.com                     #
#                                                                        #
##########################################################################
*/

$scriptVersion = '1.0.8';

$stringTest = "    the quick <b>brown</b> fox jumps <i>over</i> the lazy dog and eat <span>lorem ipsum</span> Valar morghulis  \n\rабыр\nвалар дохаэрис         ";
$regexPattern = '/[\s,]+/';

// Need alot of memory - more 1Gb
$doTestArrays = true;

set_time_limit(0);
ini_set('memory_limit', '512M');

$line = str_pad("-",78,"-");
$padHeader = 76;
$padInfo = 18;
$padLabel = 49;

$emptyResult = array(0, '-.---', '-.---');

function get_microtime()
{
	$time = microtime(true);
	if (is_string($time)) {
		list($f, $i) = explode(' ', $time);
		$time = intval($i) + floatval($f);
	}
	return $time;
}

function convert($size)
{
	$unit=array('b','kb','Mb','Gb','Tb','Pb','Eb');
	return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}

function prefix_si($size)
{
	$unit=array('','k','M','G','T','P','E');
	$i=floor(log($size,1000));
	return $unit[$i];
}

function convert_si($size)
{
	$i=floor(log($size,1000));
	return @round($size/pow(1000, $i),2);
}

/**
 * @return array((str)seconds, (str)operations/sec)
 */
function format_result_test($diffSeconds, $opCount)
{
	if ($diffSeconds) {
		$ops = $opCount / $diffSeconds;
		$s = convert_si($ops);
		$u = prefix_si($ops);
		return array($diffSeconds, number_format($diffSeconds, 3, '.', ''), number_format($s, 2, '.', '') . ' '.$u);
	} else {
		return array(0, '0.000', 'x.xxx ');
	}
}

function test_01_Math($count = 1400000) {
	$time_start = get_microtime();
	$mathFunctions = array('abs', 'acos', 'asin', 'atan', 'decbin', 'dechex', 'decoct', 'floor', 'exp', 'log1p', 'sin', 'tan', 'pi', 'is_finite', 'is_nan', 'sqrt', 'rad2deg');
	foreach ($mathFunctions as $key => $function) {
		if (!function_exists($function)) unset($mathFunctions[$key]);
	}
	for ($i=0; $i < $count; $i++) {
		foreach ($mathFunctions as $function) {
			$r = call_user_func_array($function, array($i));
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_02_String_Concat($count = 14000000) {
	$time_start = get_microtime();
	$s = '';
	for($i = 0; $i < $count; ++$i) {
		$s .= '- Valar moghulis' . PHP_EOL;
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_03_String_Simple_Functions($count = 1300000) {
	global $stringTest;
	$time_start = get_microtime();
	$stringFunctions = array('strtoupper', 'strtolower', 'strrev', 'strlen', 'str_rot13', 'ord', 'trim');
	foreach ($stringFunctions as $key => $function) {
		if (!function_exists($function)) unset($stringFunctions[$key]);
	}
	for ($i=0; $i < $count; $i++) {
		foreach ($stringFunctions as $function) {
			$r = call_user_func_array($function, array($stringTest));
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_04_String_Multibyte($count = 130000) {
	global $stringTest;

	if (!function_exists('mb_strlen')) return $emptyResult;

	$time_start = get_microtime();
	$stringFunctions = array('mb_strtoupper', 'mb_strtolower', 'mb_strlen', 'mb_strwidth');
	foreach ($stringFunctions as $key => $function) {
		if (!function_exists($function)) unset($stringFunctions[$key]);
	}
	for ($i=0; $i < $count; $i++) {
		foreach ($stringFunctions as $function) {
			$r = call_user_func_array($function, array($stringTest));
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_05_String_Manipulation($count = 1300000) {
	global $stringTest;
	$time_start = get_microtime();
	$stringFunctions = array('addslashes', 'chunk_split', 'metaphone', 'strip_tags', 'soundex', 'wordwrap');
	foreach ($stringFunctions as $key => $function) {
		if (!function_exists($function)) unset($stringFunctions[$key]);
	}
	for ($i=0; $i < $count; $i++) {
		foreach ($stringFunctions as $function) {
			$r = call_user_func_array($function, array($stringTest));
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_06_Regex($count = 1300000) {
	global $stringTest, $regexPattern;
	$time_start = get_microtime();
	$stringFunctions = array('preg_match', 'preg_split');
	foreach ($stringFunctions as $key => $function) {
		if (!function_exists($function)) unset($stringFunctions[$key]);
	}
	for ($i=0; $i < $count; $i++) {
		foreach ($stringFunctions as $function) {
			$r = call_user_func_array($function, array($regexPattern, $stringTest));
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_07_1_Hashing($count = 1300000) {
	global $stringTest;
	$time_start = get_microtime();
	$stringFunctions = array('crc32', 'md5', 'sha1');
	foreach ($stringFunctions as $key => $function) {
		if (!function_exists($function)) unset($stringFunctions[$key]);
	}
	for ($i=0; $i < $count; $i++) {
		foreach ($stringFunctions as $function) {
			$r = call_user_func_array($function, array($stringTest));
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_07_2_Crypt($count = 10000) {
	global $stringTest;
	$time_start = get_microtime();
	$stringFunctions = array('crypt');
	foreach ($stringFunctions as $key => $function) {
		if (!function_exists($function)) unset($stringFunctions[$key]);
	}
	for ($i=0; $i < $count; $i++) {
		foreach ($stringFunctions as $function) {
			$r = call_user_func_array($function, array($stringTest));
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_08_Json_Encode($count = 1300000) {
	global $stringTest;

	if (!function_exists('json_encode')) return $emptyResult;

	$time_start = get_microtime();
	$data = array(
		$stringTest,
		123456,
		123.456,
		array(123456),
		null,
		false,
	);
	for ($i=0; $i < $count; $i++) {
		foreach ($data as $value) {
			$r = json_encode($value);
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_09_Json_Decode($count = 1300000) {
	global $stringTest;

	if (!function_exists('json_decode')) return $emptyResult;

	$time_start = get_microtime();
	$data = array(
		$stringTest,
		123456,
		123.456,
		array(123456),
		null,
		false,
	);
	foreach ($data as $key => $value) {
		$data[ $key ] = json_encode($value);
	}
	for ($i=0; $i < $count; $i++) {
		foreach ($data as $value) {
			$r = json_decode($value);
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_10_Serialize($count = 1300000) {
	global $stringTest;

	if (!function_exists('serialize')) return $emptyResult;

	$time_start = get_microtime();
	$data = array(
		$stringTest,
		123456,
		123.456,
		array(123456),
		null,
		false,
	);
	for ($i=0; $i < $count; $i++) {
		foreach ($data as $value) {
			$r = serialize($value);
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_11_Unserialize($count = 1300000) {
	global $stringTest;

	if (!function_exists('unserialize')) return $emptyResult;

	$time_start = get_microtime();
	$data = array(
		$stringTest,
		123456,
		123.456,
		array(123456),
		null,
		false,
	);
	foreach ($data as $key => $value) {
		$data[ $key ] = serialize($value);
	}
	for ($i=0; $i < $count; $i++) {
		foreach ($data as $value) {
			$r = unserialize($value);
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_12_Array_Fill($count = 300) {
	global $doTestArrays;
	if (!$doTestArrays) return $emptyResult;

	$time_start = get_microtime();
	for($n = 0; $n < $count; ++$n) {
		for($i = 0; $i < $count; ++$i) {
			for($j = 0; $j < $count; ++$j) {
				$X[ $i ][ $j ] = $i * $j;
			}
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_13_Array_Unset($count = 300) {
	global $doTestArrays;
	if (!$doTestArrays) return $emptyResult;

	$time_start = get_microtime();
	for($n = 0; $n < $count; ++$n) {

		$X = range(0, $count);
		for($i = 0; $i < $count; ++$i) {
			$X[ $i ] = range(0, $count);
		}
		for($i = $count-1; $i >= 0; $i--) {
			for($j = 0; $j < $count; ++$j) {
				unset($X[ $i ][ $j ]);
			}
			unset($X[ $i ]);
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_14_Loops($count = 190000000) {
	$time_start = get_microtime();
	for($i = 0; $i < $count; ++$i);
	$i = 0; while($i++ < $count);
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_15_Loop_IfElse($count = 90000000) {
	$time_start = get_microtime();
	for ($i=0; $i < $count; $i++) {
		if ($i == -1) {
		} elseif ($i == -2) {
		} else if ($i == -3) {
		} else {
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_16_Loop_Ternary($count = 90000000) {
	$time_start = get_microtime();
	for ($i=0; $i < $count; $i++) {
		$r = ($i % 2 == 1)
			? ( ($i % 3 == 1)
				? ( ($i % 5 == 1)
					? 3
					: 2 )
				: 1 )
			: 0;
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_17_1_Loop_Defined_Access($count = 20000000) {
	$time_start = get_microtime();
	$a = array(0 => 1, 1 => 0);
	$r = 0;
	for ($i=0; $i < $count; $i++) {
		$r += $a[$i % 2];
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_17_2_Loop_Undefined_Access($count = 20000000) {
	$time_start = get_microtime();
	$a = array();
	$r = 0;
	for ($i=0; $i < $count; $i++) {
		$r += @$a[$i % 2] ? 0 : 1;
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

$version = explode('.', PHP_VERSION);
if ((int)$version[0] >= 5) {
	include_once 'php5.inc';
}

$total = 0;
$functions = get_defined_functions();
sort($functions['user']);
echo "<pre>\n$line\n|"
	.str_pad("PHP BENCHMARK SCRIPT", $padHeader," ",STR_PAD_BOTH)
	."|\n$line\n"
	.str_pad("Start:", $padInfo) . " : ". date("Y-m-d H:i:s") . "\n"
	.str_pad("Server:", $padInfo) . " : ".php_uname() . "\n"
	.str_pad("PHP version:", $padInfo) . " : " .PHP_VERSION . "\n"
	.str_pad("Benchmark version:", $padInfo) . " : ".$scriptVersion . "\n"
	.str_pad("Platform:", $padInfo) . " : " .PHP_OS . "\n"
	."$line\n";
foreach ($functions['user'] as $user) {
	if (preg_match('/^test_/', $user)) {
		$testName = str_replace('test_','',$user);
		echo str_pad($testName, $padLabel) . " :";
		list($resultSec, $resultSecFmt, $resultOps) = $user();
		$total += $resultSec;
		echo str_pad($resultSecFmt, 8, ' ', STR_PAD_LEFT)." sec |".str_pad($resultOps, 9, ' ', STR_PAD_LEFT)."Op/s\n";
	}
}
echo $line . "\n"
. str_pad("Total time:", $padLabel) . " : " . number_format($total, 3) ." sec.\n"
. str_pad("Current memory usage:", $padLabel) . " : " . convert(memory_get_usage()) .".\n"
. (function_exists('memory_get_peak_usage') ? str_pad("Peak memory usage:", $padLabel) . " : " . convert(memory_get_peak_usage())  .".\n" : '')
. "</pre>\n";
