<?php
/*
################################################################################
#                      PHP Benchmark Performance Script                        #
#                           2010      Code24 BV                                #
#                           2015-2021 Rusoft                                   #
#                                                                              #
#  Author      : Alessandro Torrisi                                            #
#  Company     : Code24 BV, The Netherlands                                    #
#  Author      : Sergey Dryabzhinsky                                           #
#  Company     : Rusoft Ltd, Russia                                            #
#  Date        : Dec 05, 2021                                                  #
#  Version     : 1.0.44                                                        #
#  License     : Creative Commons CC-BY license                                #
#  Website     : https://github.com/rusoft/php-simple-benchmark-script         #
#  Website     : https://git.rusoft.ru/open-source/php-simple-benchmark-script #
#                                                                              #
################################################################################
*/

function flprint($msg) {
	print($msg);
	flush();
}

function print_pre($msg) {
	if (php_sapi_name() != 'cli') {
		print('<pre>'.$msg.'</pre>');
	} else {
		print($msg);
	}
	flush();
}

$scriptVersion = '1.0.44';

// Special string to flush buffers, nginx for example
$flushStr = '<!-- '.str_repeat(" ", 4096).' -->';

if (php_sapi_name() != 'cli') {
	// Hello, nginx!
	header('X-Accel-Buffering: no', true);
	header('Content-Type: text/html; charset=utf-8', true);
	flush();
} else {
	$flushStr = '';
}

$tz = ini_get('date.timezone');
if (!$tz) ini_set('date.timezone', 'Europe/Moscow');

ini_set('display_errors', 0);
ini_set('error_log', null);
ini_set('implicit_flush', 1);
ini_set('output_buffering', 0);
ob_implicit_flush(1);

// Disable explicit error reporting
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Check XDebug
$xdebug = (int)ini_get('xdebug.default_enable');
if ($xdebug) {
	print_pre('<<< ERROR >>> You need to disable Xdebug extension! It greatly slow things down!'.PHP_EOL);
	exit(1);
}
ini_set('xdebug.show_exception_trace', 0);

// Check OpCache
if (php_sapi_name() != 'cli') {
	$opcache = (int)ini_get('opcache.enable');
	if ($opcache) {
		print_pre('<<< WARNING >>> You may need to disable OpCache extension! It may affect results greatly! Make it via .htaccess, VHost or fpm config'.PHP_EOL);
	}
} else {
	$opcache = (int)ini_get('opcache.enable_cli');
	if ($opcache) {
		print_pre('<<< WARNING >>> You may need to disable Cli OpCache extension! It may affect results greatly! Run php with param: -dopcache.enable_cli=0'.PHP_EOL);
	}
}

$mbover = (int)ini_get('mbstring.func_overload');
if ($mbover != 0) {
	print_pre('<<< ERROR >>> You must disable mbstring string functions overloading! It greatly slow things down! And messes with results.'.PHP_EOL);
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
	print_pre('<<< ERROR >>> Need PHP 4.3+! Current version is ' . PHP_VERSION .PHP_EOL);
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

$originMemoryLimit = @ini_get('memory_limit');
$originTimeLimit = @ini_get('max_execution_time');

/* Default execution time limit in seconds */
$defaultTimeLimit = 600;
/* Default PHP memory limit in Mb */
$defaultMemoryLimit = 256;

$recalculateLimits = 1;

$printDumbTest = 0;

$outputTestsList = 0;

$showOnlySystemInfo = 0;

$doNotTaskSet = 0;

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

if ((int)getenv('DO_NOT_TASK_SET')) {
	$doNotTaskSet = 1;
}
if (isset($_GET['do_not_task_set']) && (int)$_GET['do_not_task_set']) {
	$doNotTaskSet = 1;
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
$shortopts .= "S";
$shortopts .= "m:";       // Обязательное значение
$shortopts .= "t:";       // Обязательное значение
$shortopts .= "T:";       // Обязательное значение

$longopts = array(
	"help",
	"dont-recalc",
	"dumb-test-print",
	"list-tests",
	"system-info",
	"do-not-task-set",
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
					print_pre(
						PHP_EOL
						. 'PHP Benchmark Performance Script, version ' . $scriptVersion . PHP_EOL
						. PHP_EOL
						. 'Usage: ' . basename(__FILE__) . ' [-h|--help] [-d|--dont-recalc] [-D|--dumb-test-print] [-L|--list-tests] [-I|--system-info] [-S|--do-not-task-set] [-m|--memory-limit=256] [-t|--time-limit=600] [-T|--run-test=name1 ...]' . PHP_EOL
						. PHP_EOL
						. '	-h|--help		- print this help and exit' . PHP_EOL
						. '	-d|--dont-recalc	- do not recalculate test times / operations count even if memory of execution time limits are low' . PHP_EOL
						. '	-D|--dumb-test-print	- print dumb test time, for debug purpose' . PHP_EOL
						. '	-L|--list-tests		- output list of available tests and exit' . PHP_EOL
						. '	-I|--system-info	- output system info but do not run tests and exit' . PHP_EOL
						. '	-S|--do-not-task-set	- if run on cli - dont call taskset to pin process to one cpu core' . PHP_EOL
						. '	-m|--memory-limit <Mb>	- set memory_limit value in Mb, defaults to 256 (Mb)' . PHP_EOL
						. '	-t|--time-limit <sec>	- set max_execution_time value in seconds, defaults to 600 (sec)' . PHP_EOL
						. '	-T|--run-test <name>	- run selected test, test names from --list-tests output, can be defined multiple times' . PHP_EOL
						. PHP_EOL
						. 'Example: php ' . basename(__FILE__) . ' -m=64 -t=30' . PHP_EOL
						. PHP_EOL
					);
				} else {
					print_pre(
						PHP_EOL
						. 'PHP Benchmark Performance Script, version ' . $scriptVersion . PHP_EOL
						. PHP_EOL
						. 'Usage: ' . basename(__FILE__) . ' [-h] [-d] [-D] [-L] [-I] [-S] [-m 256] [-t 600] [-T name1 ...]' . PHP_EOL
						. PHP_EOL
						. '	-h		- print this help and exit' . PHP_EOL
						. '	-d		- do not recalculate test times / operations count even if memory of execution time limits are low' . PHP_EOL
						. '	-D		- print dumb test time, for debug purpose' . PHP_EOL
						. '	-L		- output list of available tests and exit' . PHP_EOL
						. '	-I		- output system info but do not run tests and exit' . PHP_EOL
						. '	-S		- if run on cli - dont call taskset to pin process to one cpu core' . PHP_EOL
						. '	-m <Mb>		- set memory_limit value in Mb, defaults to 256 (Mb)' . PHP_EOL
						. '	-t <sec>	- set max_execution_time value in seconds, defaults to 600 (sec)' . PHP_EOL
						. '	-T <name>	- run selected test, test names from -L output, can be defined multiple times' . PHP_EOL
						. PHP_EOL
						. 'Example: php ' . basename(__FILE__) . ' -m 64 -t 30' . PHP_EOL
						. PHP_EOL
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

			case 'S':
			case 'do-not-task-set':
				$doNotTaskSet = 1;
				break;

			case 't':
			case 'time-limit':
				if (is_numeric($oval)) {
					$defaultTimeLimit = (int)$oval;
				} else {
					print_pre("<<< WARNING >>> Option '$okey' has not numeric value '$oval'! Skip." . PHP_EOL);
				}
				break;

			case 'T':
			case 'run-test':
				// Multiple values are joined into array
				if (!empty($oval)) {
					$selectedTests = (array)$oval;
				} else {
					print_pre("<<< WARNING >>> Option '$okey' has no value! Skip." . PHP_EOL);
				}
				break;

			default:
				print_pre("<<< WARNING >>> Unknown option '$okey'!" . PHP_EOL);
		}

	}

}

set_time_limit($defaultTimeLimit);
@ini_set('memory_limit', $defaultMemoryLimit . 'M');

if (php_sapi_name() == 'cli') {
	if (file_exists('/usr/bin/taskset') && !$doNotTaskSet) {
		shell_exec('/usr/bin/taskset -c -p 0 ' . getmypid());
	}
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
$arrayDimensionLimit = 600;

// That limit gives around 256Mb too
$stringConcatLoopRepeat = 5;

$runOnlySelectedTests = !empty($selectedTests);

/** ---------------------------------- Tests limits - to recalculate -------------------------------------------- */

// Gathered on this machine
$loopMaxPhpTimesMHz = 3800;
// How much time needed for tests on this machine
$loopMaxPhpTimes = array(
	'4.4' => 324,
	'5.2' => 248,
	'5.3' => 204,
	'5.4' => 188,
	'5.5' => 189,
	'5.6' => 186,
	'7.0' => 105,
	'7.1' => 102,
	'7.2' => 98,
	'7.3' => 89,
	'7.4' => 86,
	'8.0' => 81,
	'8.1' => 80,
);
// Simple and fast test times, used to adjust all test times and limits
$dumbTestMaxPhpTimes = array(
	'4.4' => 1.041,
	'5.2' => 0.771,
	'5.3' => 0.737,
	'5.4' => 0.769,
	'5.5' => 0.770,
	'5.6' => 0.781,
	'7.0' => 0.425,
	'7.1' => 0.425,
	'7.2' => 0.412,
	'7.3' => 0.339,
	'7.4' => 0.340,
	'8.0' => 0.324,
	'8.1' => 0.323,
);
// Nice dice roll
// Should be passed into 600 seconds
$testsLoopLimits = array(
	'01_math'			=> 2000000,
	// That limit gives around 128Mb too
	'02_string_concat'	=> 7000000,
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
	'13_array_loop'		=> 250,
	'14_array_loop'		=> 250,
	'15_loops'			=> 200000000,
	'16_loop_ifelse'	=> 100000000,
	'17_loop_ternary'	=> 100000000,
	'18_1_loop_def'		=> 50000000,
	'18_2_loop_undef'	=> 50000000,
	'19_type_func'		=> 5000000,
	'20_type_conv'		=> 5000000,
	'21_loop_except'	=> 10000000,
	'22_loop_nullop'	=> 60000000,
	'23_loop_spaceship'	=> 60000000,
	'26_1_public'		=> 10000000,
	'26_2_getset'		=> 10000000,
	'26_3_magic'		=> 10000000,
	'27_simplexml'		=> 50000,
	'28_domxml'			=> 50000,
	'29_datetime'		=> 500000,
	'30_intl_number_format'		=> 20000,
	'31_intl_message_format'	=> 200000,
	'32_intl_calendar'			=> 300000,
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
	print_pre("$line\n<<< WARNING >>>\nHashing algorithm MD5 not available for crypt() in this PHP build!\n It should be available in any PHP build.\n$line" . PHP_EOL);
}


$cpuInfo = getCpuInfo();
// CPU throttling?
if ($cpuInfo['mips'] && $cpuInfo['mhz']) {
	if (abs($cpuInfo['mips'] - $cpuInfo['mhz']) > 300) {
		print_pre("$line\n<<< WARNING >>>\nCPU is in powersaving mode? Set CPU governor to 'performance'!\n Fire up CPU and recalculate MHz!\n$line" . PHP_EOL);
		// TIME WASTED HERE
		$cpuInfo = getCpuInfo(true);
	}
} else if ($cpuInfo['max-mhz'] && $cpuInfo['mhz']) {
	if (abs($cpuInfo['max-mhz'] - $cpuInfo['mhz']) > 300) {
		print_pre("$line\n<<< WARNING >>>\nCPU is in powersaving mode? Set CPU governor to 'performance'!\n Fire up CPU and recalculate MHz!\n$line" . PHP_EOL);
		// TIME WASTED HERE
		$cpuInfo = getCpuInfo(true);
	}
}

$memoryLimit = min(getPhpMemoryLimitBytes(), getSystemMemoryFreeLimitBytes());
$memoryLimitMb = convert($memoryLimit);

// Adjust array tests limits
if ($memoryLimit < $testMemoryFull) {

	print_pre("$line\n<<< WARNING >>>\nAvailable memory for tests: " . $memoryLimitMb
		. " is less than minimum required: " . convert($testMemoryFull)
		. ".\n Recalculate tests parameters to fit in memory limits."
		. "\n$line" . PHP_EOL);

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

if ($printDumbTest) {
	print_pre("Need time: " .$needTime . PHP_EOL
		. "Max time: " .$maxTime . PHP_EOL);
}

if (isset($dumbTestMaxPhpTimes[$pv])) {
	$dumbTestTimeMax = $dumbTestMaxPhpTimes[$pv];
} elseif (isset($dumbTestMaxPhpTimes[$phpversion[0]])) {
	$dumbTestTimeMax = $dumbTestMaxPhpTimes[$phpversion[0]];
}

if ($cpuInfo['mhz'] > $loopMaxPhpTimesMHz) {
	// In reality it's non-linear, but that is best we can suggest
	$needTime *= 1.0 / $cpuInfo['mhz'] * $loopMaxPhpTimesMHz;
	$dumbTestTimeMax *= 1.0 / $cpuInfo['mhz'] * $loopMaxPhpTimesMHz;

	if ($printDumbTest) {
		print_pre("CPU is faster than base one, need time recalc: " .$needTime . PHP_EOL);
	}
}

if ($recalculateLimits) {

$factor = 1.0;
// Don't bother if time is unlimited
if ($maxTime) {
	// Adjust more only if maxTime too small
	if ($needTime > $maxTime) {
		$factor = 1.0 * $maxTime / $needTime;
	}
}

if ($factor < 1.0) {
	// Adjust more only if HZ too small
	if ($cpuInfo['mhz'] < $loopMaxPhpTimesMHz) {
		$factor *= 1.0 * $cpuInfo['mhz'] / $loopMaxPhpTimesMHz;
	}

	// TIME WASTED HERE
	$dumbTestTime = dumb_test_Functions();
	//	Debug
	if ($printDumbTest) {
		print_pre("Dumb test time: " .$dumbTestTime . PHP_EOL
			. "Dumb test time max: " .$dumbTestTimeMax . PHP_EOL);
	}
	if ($dumbTestTime > $dumbTestTimeMax) {
		$factor *= 1.0 * $dumbTestTimeMax / $dumbTestTime;
	}
} else {
	// TIME WASTED HERE
	$dumbTestTime = dumb_test_Functions();
	//	Debug
	if ($printDumbTest) {
		print_pre("Dumb test time: " .$dumbTestTime . PHP_EOL
			. "Dumb test time max: " .$dumbTestTimeMax . PHP_EOL);
	}
}

$cpuModel = $cpuInfo['model'];
if (strpos($cpuModel, 'Atom') !== false || strpos($cpuInfo['model'], 'ARM') !== false) {
	print_pre("$line\n<<< WARNING >>>\nYour processor '{$cpuModel}' have too low performance!\n$line" . PHP_EOL);
	$factor = 1.0/3;
}

if ($factor < 1.0) {
	print_pre("$line\n<<< WARNING >>>\nMax execution time is less than needed for tests!\nWill try to reduce tests time as much as possible.\nFactor is: '$factor'\n$line" . PHP_EOL);
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

if (is_file('common.inc')) {
	include_once 'common.inc';
} else {
	print_pre("$line\n<<< ERROR >>>\nMissing file 'common.inc' with common tests!\n$line");
	exit(1);
}

if ((int)$phpversion[0] >= 5) {
	if (is_file('php5.inc')) {
		include_once 'php5.inc';
	} else {
		print_pre("$line\n<<< WARNING >>>\nMissing file 'php5.inc' with try/Exception/catch loop test!\n It matters only for php version 5+.\n$line");
	}
}

if ((int)$phpversion[0] >= 7) {
	if (is_file('php7.inc')) {
		include_once 'php7.inc';
	} else {
		print_pre("$line\n<<< WARNING >>>\nMissing file 'php7.inc' with PHP 7 new features tests!\n It matters only for php version 7+.\n$line");
	}
}


$functions = get_defined_functions();
sort($functions['user']);

/** ------------------------------- Early checks ------------------------------- */

if ($outputTestsList) {
	if (php_sapi_name() != 'cli')
		print("<pre>");
	print("\nAvailable tests:\n");
	foreach ($functions['user'] as $user) {
		if (strpos($user, 'test_') === 0) {
			$testName = str_replace('test_', '', $user);
			echo $testName . PHP_EOL;
		}
	}
	if (php_sapi_name() != 'cli')
		print("</pre>\n");
	exit(0);
}

/** ---------------------------------- Common code -------------------------------------------- */

$has_mbstring = "yes";
if (!function_exists('mb_strlen')) {
	print_pre("Extenstion 'mbstring' not loaded or not compiled! Multi-byte string tests will produce empty result!");
	$has_mbstring = "no";
}
$has_json = "yes";
if (!function_exists('json_encode')) {
	print_pre("Extenstion 'json' not loaded or not compiled! JSON tests will produce empty result!");
	$has_json = "no";
}
$has_pcre = "yes";
if (!function_exists('preg_match')) {
	print_pre("Extenstion 'pcre' not loaded or not compiled! Regex tests will procude empty result!");
	$has_pcre = "no";
}
$has_opcache = "no";
if (extension_loaded('Zend OPcache')) {
	$has_opcache = "yes";
}
$has_xdebug = "no";
if (extension_loaded('xdebug')) {
	print_pre("Extenstion 'xdebug' loaded! It will affect results and slow things greatly! Even if not enabled!");
	$has_xdebug = "yes";
}
$has_dom = "no";
if (extension_loaded('dom')) {
	$has_dom = "yes";
}
$has_simplexml = "no";
if (extension_loaded('simplexml')) {
	$has_simplexml = "yes";
}
$has_intl = "no";
if (extension_loaded('intl')) {
	$has_intl = "yes";
}

$total = 0;

if (!defined('PCRE_VERSION')) define('PCRE_VERSION', '-.--');
if (!defined('LIBXML_DOTTED_VERSION')) define('LIBXML_DOTTED_VERSION', '-.-.-');
if (!defined('INTL_ICU_VERSION')) define('INTL_ICU_VERSION', '-.-');

if (php_sapi_name() != 'cli') echo "<pre>";
echo "\n$line\n|"
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
	. str_pad("Benchmark version", $padInfo) . " : " . $scriptVersion . "\n"
	. str_pad("PHP version", $padInfo) . " : " . PHP_VERSION . "\n"
	. str_pad("PHP time limit", $padInfo) . " : " . $originTimeLimit . " sec\n"
	. str_pad("PHP memory limit", $padInfo) . " : " . $originMemoryLimit . "\n"
	. str_pad("Memory", $padInfo) . " : " . $memoryLimitMb . ' available' . "\n"
	. str_pad("loaded modules", $padInfo, ' ', STR_PAD_LEFT) . " :\n"
	. str_pad("json", $padInfo, ' ', STR_PAD_LEFT) . " : $has_json\n"
	. str_pad("mbstring", $padInfo, ' ', STR_PAD_LEFT) . " : $has_mbstring\n"
	. str_pad("pcre", $padInfo, ' ', STR_PAD_LEFT) . " : $has_pcre" . ($has_pcre == 'yes' ? '; version: ' . PCRE_VERSION : '') . "\n"
	. str_pad("simplexml", $padInfo, ' ', STR_PAD_LEFT) . " : $has_simplexml; libxml version: ".LIBXML_DOTTED_VERSION."\n"
	. str_pad("dom", $padInfo, ' ', STR_PAD_LEFT) . " : $has_dom\n"
	. str_pad("intl", $padInfo, ' ', STR_PAD_LEFT) . " : $has_intl" . ($has_intl == 'yes' ? '; icu version: ' . INTL_ICU_VERSION : '')."\n"
	. str_pad("opcache", $padInfo, ' ', STR_PAD_LEFT) . " : $has_opcache; enabled: ". intval($opcache) . "\n"
	. str_pad("xdebug", $padInfo, ' ', STR_PAD_LEFT) . " : $has_xdebug\n"
	. str_pad("Set time limit", $padInfo) . " : " . $maxTime . " sec\n"
	. str_pad("Crypt hash algo", $padInfo) . " : " . $cryptAlgoName . "\n"
	. "$line\n" . $flushStr;
flush();

if (!$showOnlySystemInfo) {

echo str_pad('TEST NAME', $padLabel) . " :"
	. str_pad('SECONDS', 9 + 4, ' ', STR_PAD_LEFT) . " |" . str_pad('OP/SEC', 9 + 4, ' ', STR_PAD_LEFT) . " |" . str_pad('OP/SEC/MHz', 9 + 7, ' ', STR_PAD_LEFT) . " |" . str_pad('MEMORY', 10, ' ', STR_PAD_LEFT) . "\n"
	. "$line\n" . $flushStr;
flush();

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
		echo $flushStr;
		flush();
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

if (php_sapi_name() != 'cli')
	echo "</pre>\n";
flush();
