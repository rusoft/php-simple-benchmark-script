<?php
/*
################################################################################
#                      PHP Benchmark Performance Script                        #
#                           2010      Code24 BV                                #
#                           2015-2020 Rusoft                                   #
#                                                                              #
#  Author      : Alessandro Torrisi                                            #
#  Company     : Code24 BV, The Netherlands                                    #
#  Author      : Sergey Dryabzhinsky                                           #
#  Company     : Rusoft Ltd, Russia                                            #
#  Date        : Nov 26, 2020                                                  #
#  Version     : 1.0.37                                                        #
#  License     : Creative Commons CC-BY license                                #
#  Website     : https://github.com/rusoft/php-simple-benchmark-script         #
#  Website     : https://git.rusoft.ru/open-source/php-simple-benchmark-script #
#                                                                              #
################################################################################
*/

$scriptVersion = '1.0.37';

ini_set('display_errors', 0);
ini_set('error_log', null);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
// Disable explicit error reporting
$xdebug = ini_get('xdebug.default_enable');
ini_set('xdebug.show_exception_trace', 0);

if ($xdebug) {
	print('<pre><<< ERROR >>> You need to disable Xdebug extension! It greatly slow things down!</pre>'.PHP_EOL);
	exit(1);
}

// Used in hacks/fixes checks
$phpversion = explode('.', PHP_VERSION);

$dropDead = false;
// No php < 4
if ((int)$phpversion[0] < 4) {
	$dropDead = true;
}
// No php <= 4.3
if ((int)$phpversion[0] == 4 && (int)$phpversion[1] < 3) {
	$dropDead = true;
}
if ($dropDead) {
	print('<pre><<< ERROR >>> Need PHP 4.3+! Current version is ' . PHP_VERSION . '</pre>'.PHP_EOL);
	exit(1);
}
if (!defined('PHP_MAJOR_VERSION')) {
	define('PHP_MAJOR_VERSION', (int)$phpversion[0]);
}
if (!defined('PHP_MINOR_VERSION')) {
	define('PHP_MINOR_VERSION', (int)$phpversion[1]);
}

$stringTest = "    the quick <b>brown</b> fox jumps <i>over</i> the lazy dog and eat <span>lorem ipsum</span><br/> Valar morghulis  <br/>\n\rабыр\nвалар дохаэрис   <span class='alert alert-danger'>У нас закончились ложки, Нео!</span>      ";
$regexPattern = '/[\s,]+/';

/** ------------------------------- Main Defaults ------------------------------- */

/* Default execution time limit in seconds */
$defaultTimeLimit = 600;
/* Default PHP memory limit in Mb */
$defaultMemoryLimit = 256;

$recalculateLimits = 1;

$printDumbTest = 0;

$outputTestsList = 0;

$showOnlySystemInfo = 0;

$selectedTests = array();

if ($t = (int)getenv('PHP_TIME_LIMIT')) {
	$defaultTimeLimit = $t;
}
if (isset($_GET['time_limit']) && $t = (int)$_GET['time_limit']) {
	$defaultTimeLimit = $t;
}

if ($m = (int)getenv('PHP_MEMORY_LIMIT')) {
	$defaultMemoryLimit = $m;
}
if (isset($_GET['memory_limit']) && $m = (int)$_GET['memory_limit']) {
	$defaultMemoryLimit = $m;
}

if ((int)getenv('DONT_RECALCULATE_LIMITS')) {
	$recalculateLimits = 0;
}
if (isset($_GET['dont_recalculate_limits']) && (int)$_GET['dont_recalculate_limits']) {
	$recalculateLimits = 0;
}

if ((int)getenv('PRINT_DUMB_TEST')) {
	$printDumbTest = 1;
}
if (isset($_GET['print_dumb_test']) && (int)$_GET['print_dumb_test']) {
	$printDumbTest = 1;
}

if ((int)getenv('LIST_TESTS')) {
	$outputTestsList = 1;
}
if (isset($_GET['list_tests']) && (int)$_GET['list_tests']) {
	$outputTestsList = 1;
}

if ((int)getenv('SYSTEM_INFO')) {
	$showOnlySystemInfo = 1;
}
if (isset($_GET['system_info']) && (int)$_GET['system_info']) {
	$showOnlySystemInfo = 1;
}

if ($r = getenv('RUN_TESTS')) {
	$selectedTests = explode(',', $r);
}
if (!empty($_GET['run_tests'])) {
	$selectedTests = explode(',', $_GET['run_tests']);
}

// http://php.net/manual/ru/function.getopt.php example #2
$shortopts = "h";
$shortopts .= "d";
$shortopts .= "D";
$shortopts .= "L";
$shortopts .= "I";
$shortopts .= "m:";       // Обязательное значение
$shortopts .= "t:";       // Обязательное значение
$shortopts .= "T:";       // Обязательное значение

$longopts = array(
	"help",
	"dont-recalc",
	"dumb-test-print",
	"list-tests",
	"system-info",
	"memory-limit:",      // Обязательное значение
	"time-limit:",        // Обязательное значение
	"run-test:",          // Обязательное значение
);

$hasLongOpts = true;
if ((int)$phpversion[0] > 5) {
	$options = getopt($shortopts, $longopts);
} elseif ((int)$phpversion[0] == 5 && (int)$phpversion[1] >= 3) {
	$options = getopt($shortopts, $longopts);
} else {
	$options = getopt($shortopts);
	$hasLongOpts = false;
}

if ($options) {

	foreach ($options as $okey => $oval) {

		switch ($okey) {

			case 'h':
			case 'help':
				if ($hasLongOpts) {
					print(
						'<pre>' . PHP_EOL
						. 'PHP Benchmark Performance Script, version ' . $scriptVersion . PHP_EOL
						. PHP_EOL
						. 'Usage: ' . basename(__FILE__) . ' [-h|--help] [-d|--dont-recalc] [-D|--dumb-test-print] [-L|--list-tests] [-I|--system-info] [-m|--memory-limit=256] [-t|--time-limit=600] [-T|--run-test=name1 ...]' . PHP_EOL
						. PHP_EOL
						. '	-h|--help		- print this help and exit' . PHP_EOL
						. '	-d|--dont-recalc	- do not recalculate test times / operations count even if memory of execution time limits are low' . PHP_EOL
						. '	-D|--dumb-test-print	- print dumb test time, for debug purpose' . PHP_EOL
						. '	-L|--list-tests		- output list of available tests and exit' . PHP_EOL
						. '	-I|--system-info	- output system info but do not run tests and exit' . PHP_EOL
						. '	-m|--memory-limit <Mb>	- set memory_limit value in Mb, defaults to 256 (Mb)' . PHP_EOL
						. '	-t|--time-limit <sec>	- set max_execution_time value in seconds, defaults to 600 (sec)' . PHP_EOL
						. '	-T|--run-test <name>	- run selected test, test names from --list-tests output, can be defined multiple times' . PHP_EOL
						. PHP_EOL
						. 'Example: php ' . basename(__FILE__) . ' -m=64 -t=30' . PHP_EOL
						. '</pre>' . PHP_EOL
					);
				} else {
					print(
						'<pre>' . PHP_EOL
						. 'PHP Benchmark Performance Script, version ' . $scriptVersion . PHP_EOL
						. PHP_EOL
						. 'Usage: ' . basename(__FILE__) . ' [-h] [-d] [-D] [-L] [-m 256] [-t 600] [-T name1 ...]' . PHP_EOL
						. PHP_EOL
						. '	-h		- print this help and exit' . PHP_EOL
						. '	-d		- do not recalculate test times / operations count even if memory of execution time limits are low' . PHP_EOL
						. '	-D		- print dumb test time, for debug purpose' . PHP_EOL
						. '	-L		- output list of available tests and exit' . PHP_EOL
						. '	-I		- output system info but do not run tests and exit' . PHP_EOL
						. '	-m <Mb>		- set memory_limit value in Mb, defaults to 256 (Mb)' . PHP_EOL
						. '	-t <sec>	- set max_execution_time value in seconds, defaults to 600 (sec)' . PHP_EOL
						. '	-T <name>	- run selected test, test names from -L output, can be defined multiple times' . PHP_EOL
						. PHP_EOL
						. 'Example: php ' . basename(__FILE__) . ' -m 64 -t 30' . PHP_EOL
						. '</pre>' . PHP_EOL
					);
				}
				exit(0);
				break;

			case 'm':
			case 'memory-limit':
				if (is_numeric($oval)) {
					$defaultMemoryLimit = (int)$oval;
				} else {
					print("<pre><<< WARNING >>> Option '$okey' has not numeric value '$oval'! Skip.</pre>" . PHP_EOL);
				}
				break;

			case 'd':
			case 'dont-recalc':
				$recalculateLimits = 0;
				break;

			case 'D':
			case 'dumb-test-print':
				$printDumbTest = 1;
				break;

			case 'L':
			case 'list-tests':
				$outputTestsList = 1;
				break;

			case 'I':
			case 'system-info':
				$showOnlySystemInfo = 1;
				break;

			case 't':
			case 'time-limit':
				if (is_numeric($oval)) {
					$defaultTimeLimit = (int)$oval;
				} else {
					print("<pre><<< WARNING >>> Option '$okey' has not numeric value '$oval'! Skip.</pre>" . PHP_EOL);
				}
				break;

			case 'T':
			case 'run-test':
				// Multiple values are joined into array
				if (!empty($oval)) {
					$selectedTests = (array)$oval;
				} else {
					print("<pre><<< WARNING >>> Option '$okey' has no value! Skip.</pre>" . PHP_EOL);
				}
				break;

			default:
				print("<pre><<< WARNING >>> Unknown option '$okey'!</pre>" . PHP_EOL);
		}

	}

}

set_time_limit($defaultTimeLimit);
@ini_set('memory_limit', $defaultMemoryLimit . 'M');

// Force output flushing, like in CLI
// May help with proxy-pass apache-nginx
@ini_set('output_buffering', 0);
@ini_set('implicit_flush', 1);
ob_implicit_flush(1);
// Special for nginx
header('X-Accel-Buffering: no');

if (file_exists('/usr/bin/taskset')) {
	shell_exec('/usr/bin/taskset -c -p 0 ' . getmypid());
}

/** ------------------------------- Main Constants ------------------------------- */

$line = str_pad("-", 91, "-");
$padHeader = 89;
$padInfo = 19;
$padLabel = 30;

$emptyResult = array(0, '-.---', '-.--', '-.--', 0);

$cryptSalt = null;
$cryptAlgoName = 'default';

// That gives around 256Mb memory use and reasonable test time
$testMemoryFull = 256 * 1024 * 1024;
// Arrays are matrix [$dimention] x [$dimention]
$arrayDimensionLimit = 500;

// That limit gives around 256Mb too
$stringConcatLoopRepeat = 1;

$runOnlySelectedTests = !empty($selectedTests);

/** ---------------------------------- Tests limits - to recalculate -------------------------------------------- */

// Gathered on this machine
$loopMaxPhpTimesMHz = 3099;
// How much time needed for tests on this machine
$loopMaxPhpTimes = array(
	'4.4' => 318,
	'5.2' => 217,
	'5.3' => 186,
	'5.4' => 170,
	'5.5' => 167,
	'5.6' => 170,
	'7.0' => 93,
	'7.1' => 92,
	'7.2' => 86,
	'7.3' => 73,
	'7.4' => 72,
	'8.0' => 67,
);
// Simple and fast test times, used to adjust all test times and limits
$dumbTestMaxPhpTimes = array(
	'4.4' => 1.706,
	'5.2' => 1.0596,
	'5.3' => 1.0495,
	'5.4' => 1.006,
	'5.5' => 1.0256,
	'5.6' => 1.0296,
	'7.0' => 0.569,
	'7.1' => 0.553,
	'7.2' => 0.504,
	'7.3' => 0.435,
	'7.4' => 0.423,
	'8.0' => 0.403,
);
$testsLoopLimits = array(
	'01_math'			=> 1000000,
	// Nice dice roll
	// That limit gives around 256Mb too
	'02_string_concat'	=> 7700000,
	'03_1_string_number_concat'	=> 5000000,
	'03_2_string_number_format'	=> 5000000,
	'04_string_simple'	=> 1300000,
	'05_string_mb'		=> 130000,
	'06_string_manip'	=> 1300000,
	'07_regex'			=> 1300000,
	'08_1_hashing'		=> 1300000,
	'08_2_crypt'		=> 10000,
	'09_json_encode'	=> 1300000,
	'10_json_decode'	=> 1300000,
	'11_serialize'		=> 1300000,
	'12_unserialize'	=> 1300000,
	'13_array_loop'		=> 200,
	'14_array_loop'		=> 200,
	'15_loops'			=> 100000000,
	'16_loop_ifelse'	=> 50000000,
	'17_loop_ternary'	=> 50000000,
	'18_1_loop_def'		=> 20000000,
	'18_2_loop_undef'	=> 20000000,
	'19_type_func'		=> 3000000,
	'20_type_conv'		=> 3000000,
	'21_loop_except'	=> 4000000,
	'22_loop_nullop'	=> 50000000,
	'23_loop_spaceship'	=> 50000000,
	'26_1_public'		=> 5000000,
	'26_2_getset'		=> 5000000,
	'26_3_magic'		=> 5000000,
);
$totalOps = 0;

/** ---------------------------------- Common functions -------------------------------------------- */

/**
 * Gt pretty OS release name, if available
 */
function get_current_os()
{
	$osFile = '/etc/os-release';
	$result = PHP_OS;
	if (file_exists($osFile)) {
		$f = fopen($osFile, 'r');
		while (!feof($f)) {
			$line = trim(fgets($f, 1000000));
			if (strpos($line, 'PRETTY_NAME=') === 0) {
				$s = explode('=', $line);
				$result = array_pop($s);
				$result = str_replace('"','', $result);
			}
		}
	}
	return $result;
}

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
	if ($size <= 0) $i = 0;
	else $i = floor(log($size, 1024));
	if ($i < 0) $i = 0;
	return @round($size / pow(1024, $i), 2) . ' ' . $unit[$i];
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
		'vendor' => '',
		'cores' => 0,
		'available' => 0,
		'mhz' => 0.0,
		'max-mhz' => 0.0,
		'min-mhz' => 0.0,
		'mips' => 0.0
	);

	if (!is_readable('/proc/cpuinfo')) {
		$cpu['model'] = 'Unknown';
		$cpu['vendor'] = 'Unknown';
		$cpu['cores'] = 1;
		$cpu['available'] = 1;
		return $cpu;
	}

	if ($fireUpCpu) {
		// Fire up CPU, Don't waste much time here
		$i = 30000000;
		while ($i--) ;
	}
	if (file_exists('/sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq')) {
		$cpu['mhz'] = ((int)file_get_contents('/sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq'))/1000.0;
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
			case 'bogomips': // twice of MHz usualy on Intel/Amd
			case 'BogoMIPS': // twice of MHz usualy on Intel/Amd
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
			case 'processor':
			case 'core id':
				if (empty($cpu['available'])) {
					$cpu['available'] = (int)$value+1;
				} else {
					if ($cpu['available'] < (int)$value+1) {
						$cpu['available'] = (int)$value+1;
					}
				}
				break;
		}
	}

	// Raspberry Pi or other ARM board etc.
	$cpuData = array();
	if (is_executable('/usr/bin/lscpu')) {
		$cpuData = explode("\n", shell_exec('/usr/bin/lscpu'));
	}
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
			case 'Model name':
				if (empty($cpu['model'])) {
					$cpu['model'] = $value;
				}
				break;
			// cores
			case 'CPU(s)':
				if (empty($cpu['cores'])) {
					$cpu['cores'] = (int)$value;
					// Different output, not like /proc/cpuinfo
					$cpu['available'] = (int)$value;
				}
				break;
			// MHz
			case 'CPU max MHz':
				if (empty($cpu['max-mhz'])) {
					$cpu['max-mhz'] = (int)$value;
				}
				break;
			case 'CPU min MHz':
				if (empty($cpu['min-mhz'])) {
					$cpu['min-mhz'] = (int)$value;
				}
				break;
			// vendor
			case 'Vendor ID':
				if (empty($cpu['vendor'])) {
					$cpu['vendor'] = $value;
				}
				break;
		}
	}

	if ($cpu['vendor'] == 'ARM') {
		// Unusable
		$cpu['mips'] = 0;
	}

	return $cpu;
}

function dumb_test_Functions()
{
	global $stringTest;

	$count = 100000;
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

function mymemory_usage()
{
	$m = memory_get_usage(true);
	if (!$m) {
		// If Zend Memory Manager disabled
		// Dummy, not accurate
		$dat = getrusage();
		$m = $dat["ru_maxrss"];
	}
	return $m;
}


// Run tests or not?
if (!$outputTestsList) {

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
if ($cpuInfo['mips'] && $cpuInfo['mhz']) {
	if (abs($cpuInfo['mips'] - $cpuInfo['mhz']) > 300) {
		print("<pre>\n<<< WARNING >>>\nCPU is in powersaving mode? Set CPU governor to 'performance'!\n Fire up CPU and recalculate MHz!\n</pre>" . PHP_EOL);
		// TIME WASTED HERE
		$cpuInfo = getCpuInfo(true);
	}
} else if ($cpuInfo['max-mhz'] && $cpuInfo['mhz']) {
	if (abs($cpuInfo['max-mhz'] - $cpuInfo['mhz']) > 300) {
		print("<pre>\n<<< WARNING >>>\nCPU is in powersaving mode? Set CPU governor to 'performance'!\n Fire up CPU and recalculate MHz!\n</pre>" . PHP_EOL);
		// TIME WASTED HERE
		$cpuInfo = getCpuInfo(true);
	}
}

$memoryLimit = min(getPhpMemoryLimitBytes(), getSystemMemoryFreeLimitBytes());
$memoryLimitMb = convert($memoryLimit);

// Adjust array tests limits
if ($memoryLimit < $testMemoryFull) {

	print("<pre>\n<<< WARNING >>>\nAvailable memory for tests: " . $memoryLimitMb
		. " is less than minimum required: " . convert($testMemoryFull)
		. ".\n Recalculate tests parameters to fit in memory limits."
		. "\n</pre>" . PHP_EOL);

	$factor = 1.0 * ($testMemoryFull - $memoryLimit) / $testMemoryFull;

	$diff = (int)($factor * $arrayDimensionLimit);
	$testsLoopLimits['13_array_loop'] += (int)(1.0 * pow($arrayDimensionLimit, 2) * $testsLoopLimits['13_array_loop'] / pow($arrayDimensionLimit - $diff, 2));
	$testsLoopLimits['14_array_loop'] = $testsLoopLimits['13_array_loop'];
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
$needTime = $defaultTimeLimit;
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

if ($recalculateLimits) {

$factor = 1.0;
// Don't bother if time is unlimited
if ($maxTime) {
	if ($needTime > ($maxTime - 1)) {
		$factor = 1.0 * ($maxTime - 1) / $needTime;
	}
}

if ($factor < 1.0) {
	// Adjust more only if maxTime too small
	if ($cpuInfo['mhz'] < $loopMaxPhpTimesMHz) {
		$factor *= 1.0 * $cpuInfo['mhz'] / $loopMaxPhpTimesMHz;
	}

	// TIME WASTED HERE
	$dumbTestTime = dumb_test_Functions();
//	Debug
	if ($printDumbTest) {
		print("Dumb test time: " .$dumbTestTime . PHP_EOL);
	}
	if ($dumbTestTime > $dumbTestTimeMax) {
		$factor *= 1.0 * $dumbTestTimeMax / $dumbTestTime;
	}
}

$cpuModel = $cpuInfo['model'];
if (strpos($cpuModel, 'Atom') !== false || strpos($cpuInfo['model'], 'ARM') !== false) {
	print("<pre>\n<<< WARNING >>>\nYour processor '{$cpuModel}' have too low performance!\n</pre>" . PHP_EOL);
	$factor = 1.0/3;
}

if ($factor < 1.0) {
	print("<pre>\n<<< WARNING >>>\nMax execution time is less than needed for tests!\nWill try to reduce tests time as much as possible.\n</pre>" . PHP_EOL);
	foreach ($testsLoopLimits as $tst => $loops) {
		$testsLoopLimits[$tst] = (int)($loops * $factor);
	}
}

} // recalculate time limits

} // only show tests names or not?

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
			$s .= '- Valar dohaeris' . PHP_EOL;
		}
	}
	$totalOps += $count * $stringConcatLoopRepeat;
	return format_result_test(get_microtime() - $time_start, $count * $stringConcatLoopRepeat, mymemory_usage());
}

function test_03_1_String_Number_Concat()
{
	global $testsLoopLimits, $stringConcatLoopRepeat, $totalOps;

	$count = $testsLoopLimits['03_1_string_number_concat'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; ++$i) {
		$f = $i * 1.0;
		$s = 'This is number ' . $i . ' string concat. Число: ' . $f . PHP_EOL;
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_03_2_String_Number_Format()
{
	global $testsLoopLimits, $stringConcatLoopRepeat, $totalOps;

	$count = $testsLoopLimits['03_2_string_number_format'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; ++$i) {
		$f = $i * 1.0;
		$s = "This is number $i string format. Число: $f\n";
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
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
	for ($n = 0; $n < $arrayTestLoopLimit; ++$n) {
		$X = array();
		for ($i = 0; $i < $arrayDimensionLimit; ++$i) {
			for ($j = 0; $j < $arrayDimensionLimit; ++$j) {
				$X[$i][$j] = $i * $j;
			}
		}
	}
	$totalOps += pow($arrayDimensionLimit, 2) * $arrayTestLoopLimit;
	return format_result_test(get_microtime() - $time_start, pow($arrayDimensionLimit, 2) * $arrayTestLoopLimit, mymemory_usage());
}

function test_14_Array_Range()
{
	global $testsLoopLimits, $arrayDimensionLimit, $totalOps;

	$arrayTestLoopLimit = $testsLoopLimits['14_array_loop'];
	$time_start = get_microtime();
	for ($n = 0; $n < $arrayTestLoopLimit; ++$n) {
		$x = range(0, $arrayDimensionLimit);
		for ($i = 0; $i < $arrayDimensionLimit; $i++) {
			$x[$i] = range(0, $arrayDimensionLimit);
		}
	}
	$totalOps += $arrayDimensionLimit * $arrayTestLoopLimit;
	return format_result_test(get_microtime() - $time_start, $arrayDimensionLimit * $arrayTestLoopLimit, mymemory_usage());
}

function test_14_Array_Unset()
{
	global $testsLoopLimits, $arrayDimensionLimit, $totalOps;

	$xx = range(0, $arrayDimensionLimit);
	for ($i = 0; $i < $arrayDimensionLimit; $i++) {
		$xx[$i] = range(0, $arrayDimensionLimit);
	}

	$arrayTestLoopLimit = $testsLoopLimits['14_array_loop'];
	$time_start = get_microtime();
	for ($n = 0; $n < $arrayTestLoopLimit; ++$n) {
		$x = $xx;
		for ($i = $arrayDimensionLimit; $i >= 0; $i--) {
			for ($j = 0; $j <= $arrayDimensionLimit; $j++) {
				unset($x[$i][$j]);
			}
			unset($x[$i]);
		}
	}
	$totalOps += pow($arrayDimensionLimit, 2) * $arrayTestLoopLimit;
	return format_result_test(get_microtime() - $time_start, pow($arrayDimensionLimit, 2) * $arrayTestLoopLimit, mymemory_usage());
}

function test_15_Loops()
{
	global $testsLoopLimits, $totalOps;

	$count = $testsLoopLimits['15_loops'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; ++$i) ;
	$i = 0;
	while ($i++ < $count) ;
	$totalOps += $count * 2;
	return format_result_test(get_microtime() - $time_start, $count * 2, mymemory_usage());
}

function test_16_Loop_IfElse()
{
	global $testsLoopLimits, $totalOps;

	$count = $testsLoopLimits['16_loop_ifelse'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		if ($i == -1) {
		} elseif ($i == -2) {
		} else if ($i == -3) {
		} else {
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
	for ($i = 0; $i < $count; $i++) {
		$r = ($i % 2 == 1)
			? (($i % 3 == 1)
				? (($i % 5 == 1)
					? 3
					: 2)
				: 1)
			: 0;
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

	$a = array();
	$r = 0;

	$count = $testsLoopLimits['18_2_loop_undef'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		$r += @$a[$i % 2] ? 0 : 1;
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_19_Type_Functions()
{
	global $testsLoopLimits, $totalOps;

	$ia = array('123456', '0.000001', '0x123');
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

	$ia = array('123456', '0.000001', '0x123');
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


if ((int)$phpversion[0] >= 5) {
	if (is_file('php5.inc')) {
		include_once 'php5.inc';
	} else {
		print("<pre>\n<<< WARNING >>>\nMissing file 'php5.inc' with try/Exception/catch loop test!\n It matters only for php version 5+.\n</pre>");
	}
}

if ((int)$phpversion[0] >= 7) {
	if (is_file('php7.inc')) {
		include_once 'php7.inc';
	} else {
		print("<pre>\n<<< WARNING >>>\nMissing file 'php7.inc' with PHP 7 new features tests!\n It matters only for php version 7+.\n</pre>");
	}
}


$functions = get_defined_functions();
sort($functions['user']);

/** ------------------------------- Early checks ------------------------------- */

if ($outputTestsList) {
	echo "<pre>\nAvailable tests:\n";
	foreach ($functions['user'] as $user) {
		if (strpos($user, 'test_') === 0) {
			$testName = str_replace('test_', '', $user);
			echo $testName . PHP_EOL;
		}
	}
	echo "</pre>\n";
	exit(0);
}

/** ---------------------------------- Common code -------------------------------------------- */

$has_mbstring = "yes";
if (!function_exists('mb_strlen')) {
	echo "<pre>Extenstion 'mbstring' not loaded or not compiled! Multi-byte string tests will produce empty result!</pre>";
	$has_mbstring = "no";
}
$has_json = "yes";
if (!function_exists('json_encode')) {
	echo "<pre>Extenstion 'json' not loaded or not compiled! JSON tests will produce empty result!</pre>";
	$has_json = "no";
}
$has_pcre = "yes";
if (!function_exists('preg_match')) {
	echo "<pre>Extenstion 'pcre' not loaded or not compiled! Regex tests will procude empty result!</pre>";
	$has_pcre = "no";
}

$total = 0;

echo "<pre>\n$line\n|"
	. str_pad("PHP BENCHMARK SCRIPT", $padHeader, " ", STR_PAD_BOTH)
	. "|\n$line\n"
	. str_pad("Start", $padInfo) . " : " . date("Y-m-d H:i:s") . "\n"
	. str_pad("Server", $padInfo) . " : " . php_uname('s') . '/' . php_uname('r') . ' ' . php_uname('m') . "\n"
	. str_pad("Platform", $padInfo) . " : " . PHP_OS . "\n"
	. str_pad("System", $padInfo) . " : " . get_current_os() . "\n"
	. str_pad("CPU", $padInfo) . " :\n"
	. str_pad("model", $padInfo, ' ', STR_PAD_LEFT) . " : " . $cpuInfo['model'] . "\n"
	. str_pad("cores", $padInfo, ' ', STR_PAD_LEFT) . " : " . $cpuInfo['cores'] . "\n"
	. str_pad("available", $padInfo, ' ', STR_PAD_LEFT) . " : " . $cpuInfo['available'] . "\n"
	. str_pad("MHz", $padInfo, ' ', STR_PAD_LEFT) . " : " . $cpuInfo['mhz'] . 'MHz' . "\n"
	. str_pad("Memory", $padInfo) . " : " . $memoryLimitMb . ' available' . "\n"
	. str_pad("Benchmark version", $padInfo) . " : " . $scriptVersion . "\n"
	. str_pad("PHP version", $padInfo) . " : " . PHP_VERSION . "\n"
	. str_pad("available modules", $padInfo, ' ', STR_PAD_LEFT) . " :\n"
	. str_pad("mbstring", $padInfo, ' ', STR_PAD_LEFT) . " : $has_mbstring\n"
	. str_pad("json", $padInfo, ' ', STR_PAD_LEFT) . " : $has_json\n"
	. str_pad("pcre", $padInfo, ' ', STR_PAD_LEFT) . " : $has_pcre\n"
	. str_pad("Max execution time", $padInfo) . " : " . $maxTime . " sec\n"
	. str_pad("Crypt hash algo", $padInfo) . " : " . $cryptAlgoName . "\n"
	. "$line\n";

if (!$showOnlySystemInfo) {

echo str_pad('TEST NAME', $padLabel) . " :"
	. str_pad('SECONDS', 9 + 4, ' ', STR_PAD_LEFT) . " |" . str_pad('OP/SEC', 9 + 4, ' ', STR_PAD_LEFT) . " |" . str_pad('OP/SEC/MHz', 9 + 7, ' ', STR_PAD_LEFT) . " |" . str_pad('MEMORY', 10, ' ', STR_PAD_LEFT) . "\n"
	. "$line\n";

foreach ($functions['user'] as $user) {
	if (strpos($user, 'test_') === 0) {
		$testName = str_replace('test_', '', $user);
		if ($runOnlySelectedTests) {
			if (!in_array($testName, $selectedTests)) {
				continue;
			}
		}
		echo str_pad($testName, $padLabel) . " :";
		list($resultSec, $resultSecFmt, $resultOps, $resultOpMhz, $memory) = $user();
		$total += $resultSec;
		echo str_pad($resultSecFmt, 9, ' ', STR_PAD_LEFT) . " sec |" . str_pad($resultOps, 9, ' ', STR_PAD_LEFT) . "Op/s |" . str_pad($resultOpMhz, 9, ' ', STR_PAD_LEFT) . "Ops/MHz |" . str_pad($memory, 10, ' ', STR_PAD_LEFT) . "\n";
	}
}

list($resultSec, $resultSecFmt, $resultOps, $resultOpMhz, $memory) = format_result_test($total, $totalOps, 0);

echo "$line\n"
	. str_pad("Total time:", $padLabel) . " :";
echo str_pad($resultSecFmt, 9, ' ', STR_PAD_LEFT) . " sec |" . str_pad($resultOps, 9, ' ', STR_PAD_LEFT) . "Op/s |" . str_pad($resultOpMhz, 9, ' ', STR_PAD_LEFT) . "Ops/MHz |" . "\n";
echo str_pad("Current PHP memory usage:", $padLabel) . " :" . str_pad(convert(mymemory_usage()), 12, ' ', STR_PAD_LEFT) . "\n"
	// php-4 don't have peak_usage function
	. (function_exists('memory_get_peak_usage')
		? str_pad("Peak PHP memory usage:", $padLabel) . " :" . str_pad(convert(memory_get_peak_usage()), 12, ' ', STR_PAD_LEFT) . "\n"
		 : ''
	);

} // show only system info?

echo "</pre>\n";
