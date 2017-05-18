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
#  Date        : May 18, 2017                                                  #
#  version     : 1.0.16                                                        #
#  License     : Creative Commons CC-BY license                                #
#  Website     : https://github.com/rusoft/php-simple-benchmark-script         #
#  Website     : https://git.rusoft.ru/open-source/php-simple-benchmark-script #
#                                                                              #
################################################################################
*/

$scriptVersion = '1.0.16';

// Used in hacks/fixes checks
$phpversion = explode('.', PHP_VERSION);

$stringTest = "    the quick <b>brown</b> fox jumps <i>over</i> the lazy dog and eat <span>lorem ipsum</span><br/> Valar morghulis  <br/>\n\rабыр\nвалар дохаэрис         ";
$regexPattern = '/[\s,]+/';

set_time_limit(600);
@ini_set('memory_limit', '256M');

$line = str_pad("-", 91, "-");
$padHeader = 89;
$padInfo = 19;
$padLabel = 31;

$emptyResult = array(0, '-.---', '-.--', '-.--', 0);

$cryptSalt = null;
$cryptAlgoName = 'default';

// That gives around 256Mb memory use and reasonable test time
$testMemoryFull = 256*1024*1024;
// Arrays are matrix [$dimention] x [$dimention]
$arrayDimensionLimit = 400;

// That limit gives around 256Mb too
$stringConcatLoopRepeat = 1;

/** ---------------------------------- Tests limits - to recalculate -------------------------------------------- */

// Gathered on this machine
$loopMaxPhpTimesMHz = 3800;
// How much time needed for tests on this machine
$loopMaxPhpTimes = array(
	'4.4' => 190,
	'5.2' => 120,
	'5.3' => 105,
	// 5.4, 5.5, 5.6
	'5' => 90,
	// 7.0, 7.1
	'7' => 45,
);
$dumbTestMaxPhpTimes = array(
	'4.4' => 0.088,
	'5.2' => 0.059,
	'5.3' => 0.057,
	// 5.4, 5.5, 5.6
	'5' => 0.57,
	// 7.0, 7.1
	'7' => 0.029,
);
$testsLoopLimits = array(
	'01_math'			=> 1400000,
	// Nice dice roll
	// That limit gives around 256Mb too
	'02_string_concat'	=> 7700000,
	'03_string_simple'	=> 1300000,
	'04_string_mb'		=> 130000,
	'05_string_manip'	=> 1300000,
	'06_regex'			=> 1300000,
	'07_1_hashing'		=> 1300000,
	'07_2_crypt'		=> 10000,
	'08_json_encode'	=> 1300000,
	'09_json_decode'	=> 1300000,
	'10_serialize'		=> 1300000,
	'11_unserialize'	=> 1300000,
	// 13 too
	'12_array_loop'		=> 300,
	'14_loops'			=> 190000000,
	'15_loop_ifelse'	=> 90000000,
	'16_loop_ternary'	=> 90000000,
	'17_1_loop_def'		=> 20000000,
	'17_2_loop_undef'	=> 20000000,
	'18_loop_except'	=> 4000000,
);

/** ---------------------------------- Common functions -------------------------------------------- */

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
 * Return memory_limit in bytes
 */
function getPhpMemoryLimitBytes()
{
	// http://stackoverflow.com/a/10209530
	$memory_limit = strtolower(ini_get('memory_limit'));
	if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
		if ($matches[2] == 'g') {
			$memory_limit = intval($matches[1]) * 1024 * 1024 * 1024; // nnnG -> nnn GB
		} else if ($matches[2] == 'm') {
			$memory_limit = intval($matches[1]) * 1024 * 1024; // nnnM -> nnn MB
		} else if ($matches[2] == 'k') {
			$memory_limit = intval($matches[1]) * 1024; // nnnK -> nnn KB
		} else {
			$memory_limit = intval($matches[1]); // nnn -> nnn B
		}
	}
	return $memory_limit;
}

/**
 * Return array (dict) with system memory info
 * http://stackoverflow.com/a/1455610
 */
function getSystemMemInfo()
{
	$data = explode("\n", file_get_contents("/proc/meminfo"));
	$meminfo = array();
	foreach ($data as $line) {
		if (empty($line)) {
			continue;
		}

		list($key, $val) = explode(":", $line);
		$_val = explode(" ", strtolower(trim($val)));
		$val = intval($_val[0]);
		if (isset($_val[1]) && $_val[1] == 'kb') {
			$val *= 1024;
		}
		$meminfo[$key] = trim($val);
	}
	return $meminfo;
}

/**
 * Return system memory FREE+CACHED+BUFFERS bytes (may be free)
 */
function getSystemMemoryFreeLimitBytes()
{
	$info = getSystemMemInfo();
	if (isset($info['MemAvailable'])) {
		return $info['MemAvailable'];
	}
	return $info['MemFree'] + $info['Cached'] + $info['Buffers'];
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

	if ($fireUpCpu) {
		// Fire up CPU, Don't waste much time here
		$i = 10000000;
		while ($i--) ;
	}

	// Code from https://github.com/jrgp/linfo/blob/master/src/Linfo/OS/Linux.php
	// Adopted
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

function dumb_test_Functions()
{
	global $stringTest;

	$count = 10000;
	$time_start = get_microtime();
	$stringFunctions = array('strtoupper', 'strtolower', 'strlen', 'str_rot13', 'ord', 'mb_strlen', 'trim', 'md5', 'json_encode');
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
	return get_microtime() - $time_start;
}



/** ---------------------------------- Code for common variables, tune values -------------------------------------------- */

// Search most common available algo for SALT
// http://php.net/manual/ru/function.crypt.php example #3
$cryptSalt = null;
if (defined('CRYPT_STD_DES') && CRYPT_STD_DES == 1) {
	$cryptSalt = 'rl';
	$cryptAlgoName = 'Std. DES';
}
if (defined('CRYPT_EXT_DES') && CRYPT_EXT_DES == 1) {
	$cryptSalt = '_J9..rasm';
	$cryptAlgoName = 'Ext. DES';
}
if (defined('CRYPT_MD5') && CRYPT_MD5 == 1) {
	$cryptSalt = '$1$rasmusle$';
	$cryptAlgoName = 'MD5';
}

/**
 * These are available since 5.3+
 * MD5 should be available to all versions.
 */

/*
if (defined('CRYPT_BLOWFISH') && CRYPT_BLOWFISH == 1) {
	$cryptSalt = '$2a$07$usesomesillystringforsalt$';
	$cryptAlgoName = 'BlowFish';
}
if (defined('CRYPT_SHA256') && CRYPT_SHA256 == 1) {
	$cryptSalt = '$5$rounds=5000$usesomesillystringforsalt$';
	$cryptAlgoName = 'Sha256';
}
if (defined('CRYPT_SHA512') && CRYPT_SHA512 == 1) {
	$cryptSalt = '$6$rounds=5000$usesomesillystringforsalt$';
	$cryptAlgoName = 'Sha512';
}
*/

if ($cryptAlgoName != 'MD5' && $cryptAlgoName != 'default') {
	print("<pre>\n<<< WARNING >>>\nHashing algorithm MD5 not available for crypt() in this PHP build!\n It should be available in any PHP build.\n</pre>" . PHP_EOL);
}


$cpuInfo = getCpuInfo();
// CPU throttling?
if (abs($cpuInfo['mips'] - $cpuInfo['mhz']) > 400) {
	print("<pre>\n<<< WARNING >>>\nCPU is in powersaving mode? Set CPU governor to 'performance'!\n Fire up CPU and recalculate MHz!\n</pre>" . PHP_EOL);
	$cpuInfo = getCpuInfo(true);
}

$memoryLimit = min(getPhpMemoryLimitBytes(), getSystemMemoryFreeLimitBytes());
$memoryLimitMb = convert($memoryLimit);

// Adjust array tests limits
if ($memoryLimit < $testMemoryFull) {

	print("<pre>\n<<< WARNING >>>\nAvailable memory for tests: ".$memoryLimitMb
		." is less than minimum required: ".convert($testMemoryFull)
		.".\n Recalculate tests parameters to fit in memory limits."
		."\n</pre>" . PHP_EOL);

	$factor = 1.0 * ($testMemoryFull - $memoryLimit) / $testMemoryFull;

	$diff = (int)($factor * $arrayDimensionLimit);
	$testsLoopLimits['12_array_loop'] += (int)(1.0 * pow($arrayDimensionLimit, 2) * $testsLoopLimits['12_array_loop'] / pow($arrayDimensionLimit - $diff, 2));
	$arrayDimensionLimit -= $diff;

	$diff = (int)($factor * $testsLoopLimits['02_string_concat']);

	// Special hack for php-7.x
	// New string classes, new memory allocator
	// Consumes more, allocate huge blocks
	if ((int)$phpversion[0] >= 7) $diff = (int)($diff * 1.1);

	$stringConcatLoopRepeat = (int)(1.0 * ($testsLoopLimits['02_string_concat'] * $stringConcatLoopRepeat) / ($testsLoopLimits['02_string_concat'] - $diff));
	$testsLoopLimits['02_string_concat'] -= $diff;
}

/** Recalc loop limits if max_execution_time less than needed */
$maxTime = ini_get('max_execution_time');
$needTime = 600;
$pv = $phpversion[0] . '.' . $phpversion[1];
if (isset($loopMaxPhpTimes[$pv])) {
	$needTime = $loopMaxPhpTimes[$pv];
} elseif (isset($loopMaxPhpTimes[$phpversion[0]])) {
	$needTime = $loopMaxPhpTimes[$phpversion[0]];
}

if (isset($dumbTestMaxPhpTimes[$pv])) {
	$dumbTestTimeMax = $dumbTestMaxPhpTimes[$pv];
} elseif (isset($dumbTestMaxPhpTimes[$phpversion[0]])) {
	$dumbTestTimeMax = $dumbTestMaxPhpTimes[$phpversion[0]];
}

$factor = 1.0;
// Don't bother if time is unlimited
if ($maxTime) {
	if ($needTime > ($maxTime - 1)) {
		$factor = 1.0 * ($maxTime-1) / $needTime;
	}
}
if ($factor < 1.0) {
	// Adjust more only if maxTime too small
	if ($cpuInfo['mhz'] < $loopMaxPhpTimesMHz) {
		$factor *= 1.0 * $cpuInfo['mhz'] / $loopMaxPhpTimesMHz;
	}

	$dumbTestTime = dumb_test_Functions();
	if ($dumbTestTime > $dumbTestTimeMax) {
		$factor *= 1.0 * $dumbTestTimeMax / $dumbTestTime;
	}

	print("<pre>\n<<< WARNING >>>\nMax execution time is less than needed for tests!\n Will try to reduce tests time as much as possible.\n</pre>" . PHP_EOL);
	foreach ($testsLoopLimits as $tst => $loops) {
		$testsLoopLimits[ $tst ] = (int)($loops * $factor);
	}
}

/** ---------------------------------- Common functions for tests -------------------------------------------- */

/**
 * @return array((int)seconds, (str)seconds, (str)operations/sec), (str)opterations/MHz)
 */
function format_result_test($diffSeconds, $opCount, $memory = 0)
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
			convert($memory)
		);
	} else {
		return array(0, '0.000', 'x.xx ', 'x.xx ', 0);
	}
}


/** ---------------------------------- Tests functions -------------------------------------------- */


function test_01_Math()
{
	global $testsLoopLimits;

	$count = $testsLoopLimits['01_math'];
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
	return format_result_test(get_microtime() - $time_start, $count, memory_get_usage(true));
}

function test_02_String_Concat()
{
	global $testsLoopLimits, $stringConcatLoopRepeat;

	$count = $testsLoopLimits['02_string_concat'];
	$time_start = get_microtime();
	for ($r = 0; $r < $stringConcatLoopRepeat; ++$r) {
		$s = '';
		for ($i = 0; $i < $count; ++$i) {
			$s .= '- Valar dohaeris' . PHP_EOL;
		}
	}
	return format_result_test(get_microtime() - $time_start, $count*$stringConcatLoopRepeat, memory_get_usage(true));
}

function test_03_String_Simple_Functions()
{
	global $stringTest, $testsLoopLimits;

	$count = $testsLoopLimits['03_string_simple'];
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
	return format_result_test(get_microtime() - $time_start, $count, memory_get_usage(true));
}

function test_04_String_Multibyte()
{
	global $stringTest, $emptyResult, $testsLoopLimits;

	if (!function_exists('mb_strlen')) {
		return $emptyResult;
	}

	$count = $testsLoopLimits['04_string_mb'];
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
	return format_result_test(get_microtime() - $time_start, $count, memory_get_usage(true));
}

function test_05_String_Manipulation()
{
	global $stringTest, $testsLoopLimits;
	$count = $testsLoopLimits['05_string_manip'];
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
	return format_result_test(get_microtime() - $time_start, $count, memory_get_usage(true));
}

function test_06_Regex()
{
	global $stringTest, $regexPattern, $testsLoopLimits;
	$count = $testsLoopLimits['06_regex'];
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
	return format_result_test(get_microtime() - $time_start, $count, memory_get_usage(true));
}

function test_07_1_Hashing()
{
	global $stringTest, $testsLoopLimits;
	$count = $testsLoopLimits['07_1_hashing'];
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
	return format_result_test(get_microtime() - $time_start, $count, memory_get_usage(true));
}

function test_07_2_Crypt()
{
	global $stringTest, $cryptSalt, $testsLoopLimits;
	$count = $testsLoopLimits['07_2_crypt'];
	$time_start = get_microtime();
	$stringFunctions = array('crypt');
	foreach ($stringFunctions as $key => $function) {
		if (!function_exists($function)) {
			unset($stringFunctions[$key]);
		}
	}
	for ($i = 0; $i < $count; $i++) {
		foreach ($stringFunctions as $function) {
			$r = call_user_func_array($function, array($stringTest, $cryptSalt));
		}
	}
	return format_result_test(get_microtime() - $time_start, $count, memory_get_usage(true));
}

function test_08_Json_Encode()
{
	global $stringTest, $emptyResult, $testsLoopLimits;

	if (!function_exists('json_encode')) {
		return $emptyResult;
	}

	$count = $testsLoopLimits['08_json_encode'];
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
	return format_result_test(get_microtime() - $time_start, $count, memory_get_usage(true));
}

function test_09_Json_Decode()
{
	global $stringTest, $emptyResult, $testsLoopLimits;

	if (!function_exists('json_decode')) {
		return $emptyResult;
	}

	$count = $testsLoopLimits['09_json_decode'];
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
	return format_result_test(get_microtime() - $time_start, $count, memory_get_usage(true));
}

function test_10_Serialize()
{
	global $stringTest, $emptyResult, $testsLoopLimits;

	if (!function_exists('serialize')) {
		return $emptyResult;
	}

	$count = $testsLoopLimits['10_serialize'];
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
	return format_result_test(get_microtime() - $time_start, $count, memory_get_usage(true));
}

function test_11_Unserialize()
{
	global $stringTest, $emptyResult, $testsLoopLimits;

	if (!function_exists('unserialize')) {
		return $emptyResult;
	}

	$count = $testsLoopLimits['11_unserialize'];
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
	return format_result_test(get_microtime() - $time_start, $count, memory_get_usage(true));
}

function test_12_Array_Fill()
{
	global $testsLoopLimits, $arrayDimensionLimit;

	$arrayTestLoopLimit = $testsLoopLimits['12_array_loop'];
	$time_start = get_microtime();
	for ($n = 0; $n < $arrayTestLoopLimit; ++$n) {
		for ($i = 0; $i < $arrayDimensionLimit; ++$i) {
			for ($j = 0; $j < $arrayDimensionLimit; ++$j) {
				$X[$i][$j] = $i * $j;
			}
		}
	}
	return format_result_test(get_microtime() - $time_start, pow($arrayDimensionLimit, 2)*$arrayTestLoopLimit, memory_get_usage(true));
}

function test_13_Array_Unset()
{
	global $testsLoopLimits, $arrayDimensionLimit;

	$arrayTestLoopLimit = $testsLoopLimits['12_array_loop'];
	$time_start = get_microtime();
	for ($n = 0; $n < $arrayTestLoopLimit; ++$n) {

		$X = range(0, $arrayDimensionLimit);
		for ($i = 0; $i < $arrayDimensionLimit; ++$i) {
			$X[$i] = range(0, $arrayDimensionLimit);
		}
		for ($i = $arrayDimensionLimit - 1; $i >= 0; $i--) {
			for ($j = 0; $j < $arrayDimensionLimit; ++$j) {
				unset($X[$i][$j]);
			}
			unset($X[$i]);
		}
	}
	return format_result_test(get_microtime() - $time_start, pow($arrayDimensionLimit, 2)*$arrayTestLoopLimit, memory_get_usage(true));
}

function test_14_Loops()
{
	global $testsLoopLimits;

	$count = $testsLoopLimits['14_loops'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; ++$i) ;
	$i = 0;
	while ($i++ < $count) ;
	return format_result_test(get_microtime() - $time_start, $count * 2, memory_get_usage(true));
}

function test_15_Loop_IfElse()
{
	global $testsLoopLimits;

	$count = $testsLoopLimits['15_loop_ifelse'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		if ($i == -1) {
		} elseif ($i == -2) {
		} else if ($i == -3) {
		} else {
		}
	}
	return format_result_test(get_microtime() - $time_start, $count, memory_get_usage(true));
}

function test_16_Loop_Ternary()
{
	global $testsLoopLimits;

	$count = $testsLoopLimits['16_loop_ternary'];
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
	return format_result_test(get_microtime() - $time_start, $count, memory_get_usage(true));
}

function test_17_1_Loop_Defined_Access()
{
	global $testsLoopLimits;

	$count = $testsLoopLimits['17_1_loop_def'];
	$time_start = get_microtime();
	$a = array(0 => 1, 1 => 0);
	$r = 0;
	for ($i = 0; $i < $count; $i++) {
		$r += $a[$i % 2];
	}
	return format_result_test(get_microtime() - $time_start, $count, memory_get_usage(true));
}

function test_17_2_Loop_Undefined_Access()
{
	global $testsLoopLimits;

	$count = $testsLoopLimits['17_2_loop_undef'];
	$time_start = get_microtime();
	$a = array();
	$r = 0;
	for ($i = 0; $i < $count; $i++) {
		$r += @$a[$i % 2] ? 0 : 1;
	}
	return format_result_test(get_microtime() - $time_start, $count, memory_get_usage(true));
}

if ((int)$phpversion[0] >= 5) {
	include_once 'php5.inc';
}

/** ---------------------------------- Common code -------------------------------------------- */


$total = 0;
$functions = get_defined_functions();
sort($functions['user']);

echo "<pre>\n$line\n|"
	. str_pad("PHP BENCHMARK SCRIPT", $padHeader, " ", STR_PAD_BOTH)
	. "|\n$line\n"
	. str_pad("Start:", $padInfo) . " : " . date("Y-m-d H:i:s") . "\n"
	. str_pad("Server:", $padInfo) . " : " . php_uname('s') . '/' . php_uname('r') . ' ' . php_uname('m') . "\n"
	. str_pad("Platform:", $padInfo) . " : " . PHP_OS . "\n"
	. str_pad("CPU:", $padInfo) . " :\n"
	. str_pad("model", $padInfo, ' ', STR_PAD_LEFT) . " : " . $cpuInfo['model'] . "\n"
	. str_pad("cores", $padInfo, ' ', STR_PAD_LEFT) . " : " . $cpuInfo['cores'] . "\n"
	. str_pad("MHz", $padInfo, ' ', STR_PAD_LEFT) . " : " . $cpuInfo['mhz'] . 'MHz' . "\n"
	. str_pad("Memory", $padInfo) . " : " . $memoryLimitMb . ' available' . "\n"
	. str_pad("Benchmark version:", $padInfo) . " : " . $scriptVersion . "\n"
	. str_pad("PHP version:", $padInfo) . " : " . PHP_VERSION . "\n"
	. str_pad("Max execution time:", $padInfo) . " : " . $maxTime . " sec.\n"
	. str_pad("Crypt hash algo:", $padInfo) . " : " . $cryptAlgoName . "\n"
	. "$line\n"
	. str_pad('TEST NAME', $padLabel) . " :"
	. str_pad('SECONDS', 8 + 4, ' ', STR_PAD_LEFT) . " |" . str_pad('OP/SEC', 9 + 4, ' ', STR_PAD_LEFT) . " |" . str_pad('OP/SEC/MHz', 9 + 7, ' ', STR_PAD_LEFT) . " |" . str_pad('MEMORY', 10, ' ', STR_PAD_LEFT) . "\n"
	. "$line\n";

foreach ($functions['user'] as $user) {
	if (preg_match('/^test_/', $user)) {
		$testName = str_replace('test_', '', $user);
		echo str_pad($testName, $padLabel) . " :";
		list($resultSec, $resultSecFmt, $resultOps, $resultOpMhz, $memory) = $user();
		$total += $resultSec;
		echo str_pad($resultSecFmt, 8, ' ', STR_PAD_LEFT) . " sec |" . str_pad($resultOps, 9, ' ', STR_PAD_LEFT) . "Op/s |" . str_pad($resultOpMhz, 9, ' ', STR_PAD_LEFT) . "Ops/MHz |" . str_pad($memory, 10, ' ', STR_PAD_LEFT) . "\n";
	}
}

echo $line . "\n"
	. str_pad("Total time:", $padLabel) . " : " . number_format($total, 3) . " sec.\n"
	. str_pad("Current memory usage:", $padLabel) . " : " . convert(memory_get_usage()) . ".\n"
	// Hi from php-4
	. (function_exists('memory_get_peak_usage') ? str_pad("Peak memory usage:", $padLabel) . " : " . convert(memory_get_peak_usage()) . ".\n" : '')
	. "</pre>\n";
