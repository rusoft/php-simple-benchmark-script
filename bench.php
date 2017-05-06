<?php
/*
################################################################################
#                      PHP Benchmark Performance Script                        #
#                           2010      Code24 BV                                #
#                           2015-2017 Rusoft                                   #
#                                                                              #
#  Author      : Alessandro Torrisi                                            #
#  Company     : Code24 BV, The Netherlands                                    #
#  Author      : Sergey Dryabzhinsky                                           #
#  Company     : Rusoft Ltd, Russia                                            #
#  Date        : Apr 20, 2017                                                  #
#  version     : 1.0.13                                                        #
#  License     : Creative Commons CC-BY license                                #
#  Website     : https://github.com/rusoft/php-simple-benchmark-script         #
#  Website     : https://git.rusoft.ru/open-source/php-simple-benchmark-script #
#                                                                              #
################################################################################
*/

$scriptVersion = '1.0.13';

$stringTest = "    the quick <b>brown</b> fox jumps <i>over</i> the lazy dog and eat <span>lorem ipsum</span><br/> Valar morghulis  <br/>\n\rабыр\nвалар дохаэрис         ";
$regexPattern = '/[\s,]+/';

set_time_limit(0);
ini_set('memory_limit', '512M');

$line = str_pad("-", 78, "-");
$padHeader = 76;
$padInfo = 18;
$padLabel = 31;

$emptyResult = array(0, '-.---', '-.--', '-.--');

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
	$unit = array('b', 'kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb');
	return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

function prefix_si($size)
{
	$unit = array(' ', 'k', 'M', 'G', 'T', 'P', 'E', -3 => 'm', -6 => 'u');
	$i = floor(log($size, 1000));
	if ($i < 0) {
		if ($i <= -6) {
			$i = -6;
		} elseif ($i <= -3) {
			$i = -3;
		} else {
			$i = 0;
		}
	}
	return $unit[$i];
}

function convert_si($size)
{
	$i = floor(log($size, 1000));
	if ($i < 0) {
		if ($i <= -6) {
			$i = -6;
		} elseif ($i <= -3) {
			$i = -3;
		} else {
			$i = 0;
		}
	}
	return @round($size / pow(1000, $i), 2);
}

/**
 * Read /proc/cpuinfo, fetch some data
 */
function getCpuInfo($fireUpCpu = false)
{
	$cpu = array(
		'model' => '',
		'cores' => 0,
		'mhz' => 0.0,
		'mips' => 0.0
	);

	if (!is_readable('/proc/cpuinfo')) {
		$cpu['model'] = 'Unknown';
		$cpu['cores'] = 1;
		return $cpu;
	}

	// Code from https://github.com/jrgp/linfo/blob/master/src/Linfo/OS/Linux.php
	// Adopted
	if ($fireUpCpu) {
		// Fire up CPU
		$i = 100000000;
		while ($i--) ;
	}

	$cpuData = explode("\n", file_get_contents('/proc/cpuinfo'));
	foreach ($cpuData as $line) {
		$line = explode(':', $line, 2);

		if (!array_key_exists(1, $line)) {
			continue;
		}

		$key = trim($line[0]);
		$value = trim($line[1]);

		// What we want are bogomips, MHz, processor, and Model.
		switch ($key) {
			// CPU model
			case 'model name':
			case 'cpu':
			case 'Processor':
				if (empty($cpu['model'])) {
					$cpu['model'] = $value;
				}
				break;
			// Speed in MHz
			case 'cpu MHz':
				if (empty($cpu['mhz']) || $cpu['mhz'] < (float)$value) {
					$cpu['mhz'] = (float)$value;
				}
				break;
			case 'Cpu0ClkTck': // Old sun boxes
				if (empty($cpu['mhz'])) {
					$cpu['mhz'] = (int)hexdec($value) / 1000000.0;
				}
				break;
			case 'bogomips': // twice of MHz usualy
				if (empty($cpu['mhz'])) {
					$cpu['mhz'] = (float)$value / 2.0;
				}
				if (empty($cpu['mips'])) {
					$cpu['mips'] = (float)$value / 2.0;
				}
				break;
			// cores
			case 'cpu cores':
				if (empty($cpu['cores'])) {
					$cpu['cores'] = (int)$value;
				}
				break;
		}
	}

	return $cpu;
}

$cpuInfo = getCpuInfo();
// CPU throttling?
if (abs($cpuInfo['mips'] - $cpuInfo['mhz']) > 400) {
	print("<pre>\n<<< WARNING >>>\nCPU is in powersaving mode? Set CPU governor to 'performance'!\nFire up CPU and recalculate MHz!\n</pre>" . PHP_EOL);
	$cpuInfo = getCpuInfo(true);
}

/**
 * @return array((int)seconds, (str)seconds, (str)operations/sec), (str)opterations/MHz)
 */
function format_result_test($diffSeconds, $opCount)
{
	global $cpuInfo;
	if ($diffSeconds) {
		$ops = $opCount / $diffSeconds;
		$ops_v = convert_si($ops);
		$ops_u = prefix_si($ops);

		$opmhz = 0;
		if (!empty($cpuInfo['mhz'])) {
			$opmhz = $ops / $cpuInfo['mhz'];
		}
		$opmhz_v = convert_si($opmhz);
		$opmhz_u = prefix_si($opmhz);

		return array($diffSeconds, number_format($diffSeconds, 3, '.', ''),
			number_format($ops_v, 2, '.', '') . ' ' . $ops_u,
			number_format($opmhz_v, 2, '.', '') . ' ' . $opmhz_u,
		);
	} else {
		return array(0, '0.000', 'x.xx ', 'x.xx ');
	}
}

function test_01_Math($count = 1400000)
{
	$time_start = get_microtime();
	$mathFunctions = array('abs', 'acos', 'asin', 'atan', 'decbin', 'dechex', 'decoct', 'floor', 'exp', 'log1p', 'sin', 'tan', 'pi', 'is_finite', 'is_nan', 'sqrt', 'rad2deg');
	foreach ($mathFunctions as $key => $function) {
		if (!function_exists($function)) {
			unset($mathFunctions[$key]);
		}
	}
	for ($i = 0; $i < $count; $i++) {
		foreach ($mathFunctions as $function) {
			$r = call_user_func_array($function, array($i));
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_02_String_Concat($count = 14000000)
{
	$time_start = get_microtime();
	$s = '';
	for ($i = 0; $i < $count; ++$i) {
		$s .= '- Valar moghulis' . PHP_EOL;
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_03_String_Simple_Functions($count = 1300000)
{
	global $stringTest;
	$time_start = get_microtime();
	$stringFunctions = array('strtoupper', 'strtolower', 'strrev', 'strlen', 'str_rot13', 'ord', 'trim');
	foreach ($stringFunctions as $key => $function) {
		if (!function_exists($function)) {
			unset($stringFunctions[$key]);
		}
	}
	for ($i = 0; $i < $count; $i++) {
		foreach ($stringFunctions as $function) {
			$r = call_user_func_array($function, array($stringTest));
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_04_String_Multibyte($count = 130000)
{
	global $stringTest, $emptyResult;

	if (!function_exists('mb_strlen')) {
		return $emptyResult;
	}

	$time_start = get_microtime();
	$stringFunctions = array('mb_strtoupper', 'mb_strtolower', 'mb_strlen', 'mb_strwidth');
	foreach ($stringFunctions as $key => $function) {
		if (!function_exists($function)) {
			unset($stringFunctions[$key]);
		}
	}
	for ($i = 0; $i < $count; $i++) {
		foreach ($stringFunctions as $function) {
			$r = call_user_func_array($function, array($stringTest));
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_05_String_Manipulation($count = 1300000)
{
	global $stringTest;
	$time_start = get_microtime();
	$stringFunctions = array('addslashes', 'chunk_split', 'metaphone', 'strip_tags', 'soundex', 'wordwrap');
	foreach ($stringFunctions as $key => $function) {
		if (!function_exists($function)) {
			unset($stringFunctions[$key]);
		}
	}
	for ($i = 0; $i < $count; $i++) {
		foreach ($stringFunctions as $function) {
			$r = call_user_func_array($function, array($stringTest));
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_06_Regex($count = 1300000)
{
	global $stringTest, $regexPattern;
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
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_07_1_Hashing($count = 1300000)
{
	global $stringTest;
	$time_start = get_microtime();
	$stringFunctions = array('crc32', 'md5', 'sha1');
	foreach ($stringFunctions as $key => $function) {
		if (!function_exists($function)) {
			unset($stringFunctions[$key]);
		}
	}
	for ($i = 0; $i < $count; $i++) {
		foreach ($stringFunctions as $function) {
			$r = call_user_func_array($function, array($stringTest));
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_07_2_Crypt($count = 10000)
{
	global $stringTest;
	$time_start = get_microtime();
	$stringFunctions = array('crypt');
	foreach ($stringFunctions as $key => $function) {
		if (!function_exists($function)) {
			unset($stringFunctions[$key]);
		}
	}
	for ($i = 0; $i < $count; $i++) {
		foreach ($stringFunctions as $function) {
			$r = call_user_func_array($function, array($stringTest, '_J9..rasm'));
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_08_Json_Encode($count = 1300000)
{
	global $stringTest, $emptyResult;

	if (!function_exists('json_encode')) {
		return $emptyResult;
	}

	$time_start = get_microtime();
	$data = array(
		$stringTest,
		123456,
		123.456,
		array(123456),
		null,
		false,
		new stdClass(),
	);
	for ($i = 0; $i < $count; $i++) {
		foreach ($data as $value) {
			$r = json_encode($value);
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_09_Json_Decode($count = 1300000)
{
	global $stringTest, $emptyResult;

	if (!function_exists('json_decode')) {
		return $emptyResult;
	}

	$time_start = get_microtime();
	$data = array(
		$stringTest,
		123456,
		123.456,
		array(123456),
		null,
		false,
		new stdClass(),
	);
	foreach ($data as $key => $value) {
		$data[$key] = json_encode($value);
	}
	for ($i = 0; $i < $count; $i++) {
		foreach ($data as $value) {
			$r = json_decode($value);
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_10_Serialize($count = 1300000)
{
	global $stringTest, $emptyResult;

	if (!function_exists('serialize')) {
		return $emptyResult;
	}

	$time_start = get_microtime();
	$data = array(
		$stringTest,
		123456,
		123.456,
		array(123456),
		null,
		false,
		new stdClass(),
	);
	for ($i = 0; $i < $count; $i++) {
		foreach ($data as $value) {
			$r = serialize($value);
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_11_Unserialize($count = 1300000)
{
	global $stringTest, $emptyResult;

	if (!function_exists('unserialize')) {
		return $emptyResult;
	}

	$time_start = get_microtime();
	$data = array(
		$stringTest,
		123456,
		123.456,
		array(123456),
		null,
		false,
		new stdClass(),
	);
	foreach ($data as $key => $value) {
		$data[$key] = serialize($value);
	}
	for ($i = 0; $i < $count; $i++) {
		foreach ($data as $value) {
			$r = unserialize($value);
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_12_Array_Fill($count = 300)
{
	$time_start = get_microtime();
	for ($n = 0; $n < $count; ++$n) {
		for ($i = 0; $i < $count; ++$i) {
			for ($j = 0; $j < $count; ++$j) {
				$X[$i][$j] = $i * $j;
			}
		}
	}
	return format_result_test(get_microtime() - $time_start, pow($count, 3));
}

function test_13_Array_Unset($count = 300)
{
	$time_start = get_microtime();
	for ($n = 0; $n < $count; ++$n) {

		$X = range(0, $count);
		for ($i = 0; $i < $count; ++$i) {
			$X[$i] = range(0, $count);
		}
		for ($i = $count - 1; $i >= 0; $i--) {
			for ($j = 0; $j < $count; ++$j) {
				unset($X[$i][$j]);
			}
			unset($X[$i]);
		}
	}
	return format_result_test(get_microtime() - $time_start, pow($count, 3));
}

function test_14_Loops($count = 190000000)
{
	$time_start = get_microtime();
	for ($i = 0; $i < $count; ++$i) ;
	$i = 0;
	while ($i++ < $count) ;
	return format_result_test(get_microtime() - $time_start, $count * 2);
}

function test_15_Loop_IfElse($count = 90000000)
{
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		if ($i == -1) {
		} elseif ($i == -2) {
		} else if ($i == -3) {
		} else {
		}
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_16_Loop_Ternary($count = 90000000)
{
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		$r = ($i % 2 == 1)
			? (($i % 3 == 1)
				? (($i % 5 == 1)
					? 3
					: 2)
				: 1)
			: 0;
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_17_1_Loop_Defined_Access($count = 20000000)
{
	$time_start = get_microtime();
	$a = array(0 => 1, 1 => 0);
	$r = 0;
	for ($i = 0; $i < $count; $i++) {
		$r += $a[$i % 2];
	}
	return format_result_test(get_microtime() - $time_start, $count);
}

function test_17_2_Loop_Undefined_Access($count = 20000000)
{
	$time_start = get_microtime();
	$a = array();
	$r = 0;
	for ($i = 0; $i < $count; $i++) {
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
	. str_pad("PHP BENCHMARK SCRIPT", $padHeader, " ", STR_PAD_BOTH)
	. "|\n$line\n"
	. str_pad("Start:", $padInfo) . " : " . date("Y-m-d H:i:s") . "\n"
	. str_pad("Server:", $padInfo) . " : " . php_uname('s') . '/' . php_uname('r') . ' ' . php_uname('m') . "\n"
	. str_pad("CPU:", $padInfo) . " :\n"
	. str_pad("model", $padInfo, ' ', STR_PAD_LEFT) . " : " . $cpuInfo['model'] . "\n"
	. str_pad("cores", $padInfo, ' ', STR_PAD_LEFT) . " : " . $cpuInfo['cores'] . "\n"
	. str_pad("MHz", $padInfo, ' ', STR_PAD_LEFT) . " : " . $cpuInfo['mhz'] . 'MHz' . "\n"
	. str_pad("PHP version:", $padInfo) . " : " . PHP_VERSION . "\n"
	. str_pad("Benchmark version:", $padInfo) . " : " . $scriptVersion . "\n"
	. str_pad("Platform:", $padInfo) . " : " . PHP_OS . "\n"
	. "$line\n"
	. str_pad('TEST NAME', $padLabel) . " :"
	. str_pad('SECONDS', 8 + 4, ' ', STR_PAD_LEFT) . " |" . str_pad('OP/SEC', 9 + 4, ' ', STR_PAD_LEFT) . " |" . str_pad('OP/SEC/MHz', 9 + 7, ' ', STR_PAD_LEFT) . "\n"
	. "$line\n";

foreach ($functions['user'] as $user) {
	if (preg_match('/^test_/', $user)) {
		$testName = str_replace('test_', '', $user);
		echo str_pad($testName, $padLabel) . " :";
		list($resultSec, $resultSecFmt, $resultOps, $resultOpMhz) = $user();
		$total += $resultSec;
		echo str_pad($resultSecFmt, 8, ' ', STR_PAD_LEFT) . " sec |" . str_pad($resultOps, 9, ' ', STR_PAD_LEFT) . "Op/s |" . str_pad($resultOpMhz, 9, ' ', STR_PAD_LEFT) . "Ops/MHz" . "\n";
	}
}

echo $line . "\n"
	. str_pad("Total time:", $padLabel) . " : " . number_format($total, 3) . " sec.\n"
	. str_pad("Current memory usage:", $padLabel) . " : " . convert(memory_get_usage()) . ".\n"
	// Hi from php-4
	. (function_exists('memory_get_peak_usage') ? str_pad("Peak memory usage:", $padLabel) . " : " . convert(memory_get_peak_usage()) . ".\n" : '')
	. "</pre>\n";
