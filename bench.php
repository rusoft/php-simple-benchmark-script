<?php
/*
################################################################################
#                      PHP Benchmark Performance Script                        #
#                           2010      Code24 BV                                #
#                           2015-2025 Rusoft                                   #
#                                                                              #
#  Author      : Alessandro Torrisi                                            #
#  Company     : Code24 BV, The Netherlands                                    #
#  Author      : Sergey Dryabzhinsky                                           #
#  Company     : Rusoft Ltd, Russia                                            #
#  Date        : May 29, 2025                                                  #
#  Version     : 1.0.61-dev                                                    #
#  License     : Creative Commons CC-BY license                                #
#  Website     : https://github.com/rusoft/php-simple-benchmark-script         #
#  Website     : https://gitea.rusoft.ru/open-source/php-simple-benchmark-script #
#                                                                              #
################################################################################
*/

include_once("php-options.php");

$scriptVersion = '1.0.61-dev';

// Special string to flush buffers, nginx for example
$flushStr = '<!-- '.str_repeat(" ", 8192).' -->';

// Used in hacks/fixes checks
$phpversion = explode('.', PHP_VERSION);

$messagesCnt = 0;
$rawValues4json = false;
$totalOps = 0;

if (php_sapi_name() == 'cli') {
	// Terminal color sequence
	$colorReset = "\033[0m";
	$colorRed = "\033[31m";
	$colorGreen = "\033[32m";
	$colorYellow = "\033[33m";
	$colorGray = "\033[30m";

	$term = getenv('TERM');
	if (in_array($term, array('xterm', 'urxvt', 'linux', 'screen'))) {
		// Concrete terms, or limited terms
		// pass
	} else if (strpos($term, '-color') !== false) {
		// Special string
		// pass
	} else if (strpos($term, '-256color') !== false) {
		// Special string
		// pass
	} else {
		// not pass
		$colorReset = '';
		$colorRed = '';
		$colorGreen = '';
		$colorYellow = '';
		$colorGray = '';
	}
} else {
	// Html colors
	$colorReset = '</span>'; // just closing tag
	$colorRed = '<span style="color:red">';
	$colorGreen = '<span style="color:green">';
	$colorYellow = '<span style="color:orange">';
	$colorGray = '<span style="color:gray">';
}

/** ------------------------------- Main Defaults ------------------------------- */

$has_igb = "{$colorYellow}no{$colorReset}";
if (extension_loaded('igbinary')) {
	$has_igb = "{$colorGreen}yes{$colorReset}";
	@include("igbinary.inc");
}

$has_msg = "{$colorYellow}no{$colorReset}";
if (extension_loaded('msgpack')) {
	$has_msg = "{$colorGreen}yes{$colorReset}";
	@include("msgpack.inc");
}

if (extension_loaded('zstd')) {
	@include_once("compression-zstd.inc");
}
if (extension_loaded('lz4')) {
	@include_once("compression-lz4.inc");
}
if (extension_loaded('brotli')) {
	@include_once("compression-brotli.inc");
}
if (extension_loaded('snappy')) {
	@include_once("compression-snappy.inc");
}
if (extension_loaded('bz2')) {
	@include_once("compression-bz2.inc");
}
if (extension_loaded('zlib')) {
	@include_once("compression-zlib.inc");
}
if (extension_loaded('intl')) {
	@include_once("intl.inc");
}
if (file_exists('UUID.php') && PHP_VERSION >= '5.0.0') {
	@include_once("php-uuid.inc");
}
if (file_exists('kvstorage-mem.inc') && PHP_VERSION >= '5.0.0') {
	@include_once("kv-memory.inc");
}
if (extension_loaded('uuid')) {
	@include_once("mod-uuid.inc");
}
if (extension_loaded('gd')) {
	@include_once("php-gd-imagick-common.inc");
	@include_once("php-gd.inc");
}
if (extension_loaded('imagick')) {
	@include_once("php-gd-imagick-common.inc");
	@include_once("php-imagick.inc");
}

$originMemoryLimit = @ini_get('memory_limit');
$originTimeLimit = @ini_get('max_execution_time');

/* Default execution time limit in seconds */
$defaultTimeLimit = 600;
/*
	Default PHP memory limit in Mb.
	It's for ALL PHP structures!
	Memory allocator works with blocks by X_Mb.
	Some we need a little more.
*/
$defaultMemoryLimit = 130;

$useColors = 1;

$debugMode = 0;

$printJson = 0;

$printMachine = 0;

$recalculateLimits = 1;

$printDumbTest = 0;

$outputTestsList = 0;

$showOnlySystemInfo = 0;

$selectedTests = array();// exact names
$skipTests = array();// patterns to match names


/* ----------------- Fetch environ or GET params */

if ($t = (int)getenv('PHP_TIME_LIMIT')) {
	$defaultTimeLimit = $t;
}
if (isset($_GET['time_limit']) && $t = (int)$_GET['time_limit']) {
	$defaultTimeLimit = $t;
}

if ($x = (int)getenv('DONT_USE_COLORS')) {
	$useColors = $x == 0;
}
if (isset($_GET['dont_use_colors']) && $x = (int)$_GET['dont_use_colors']) {
	$useColors = $x == 0;
}

if ($x = (int)getenv('PHP_DEBUG_MODE')) {
	$debugMode = $x;
}
if (isset($_GET['debug_mode']) && $x = (int)$_GET['debug_mode']) {
	$debugMode = $x;
}

if ($x = (int)getenv('PRINT_JSON')) {
	$printJson = $x;
}
if (isset($_GET['print_json']) && $x = (int)$_GET['print_json']) {
	$printJson = $x;
}
if ($printJson) $printMachine = 0;

if ($x = (int)getenv('PRINT_MACHINE')) {
	$printMachine = $x;
}
if (isset($_GET['print_machine']) && $x = (int)$_GET['print_machine']) {
	$printMachine = $x;
}
if ($printMachine) $printJson = 0;

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

if ($r = getenv('SKIP_TESTS')) {
	$skipTests = explode(',', $r);
}
if (!empty($_GET['skip_tests'])) {
	$skipTests = explode(',', $_GET['skip_tests']);
}



/* common functions */

function print_pre($msg) {
	global $printJson, $printMachine, $messagesCnt;
	if ($printMachine) {
		print($msg);
	} else if ($printJson) {
		$msg = trim(str_replace("\n", " ", $msg));
		if (function_exists('json_encode')) {
			$msg = json_encode($msg);
		} else {
			$msg = '"'.$msg.'"';
		}
		print('"message_'.$messagesCnt.'": '.$msg.','.PHP_EOL);
	} else {
		if (php_sapi_name() != 'cli') {
			print('<pre>'.$msg.'</pre>');
		} else {
			print($msg);
		}
	}
	flush();
	$messagesCnt++;
}

function print_norm($msg) {
	global $printJson, $messagesCnt;
	if ($printJson) {
		$msg = trim(str_replace("\n", " ", $msg));
		if (function_exists('json_encode')) {
			$msg = json_encode($msg);
		} else {
			$msg = '"'.$msg.'"';
		}
		print('"message_'.$messagesCnt.'": '.$msg.','.PHP_EOL);
	} else {
		print($msg);
	}
	flush();
	$messagesCnt++;
}


if (!function_exists('gethostname')) {
	// 5.3.0+ only
	function gethostname() {
		ob_start();
		$last_str = system(`hostname -f`, $errcode);
		ob_end_clean();
		if ($last_str !== false) {
			return $last_str;
		}
		return '';
	}
}


/* global command line options */
if (php_sapi_name() == 'cli') {


// http://php.net/manual/ru/function.getopt.php example #2
$shortopts = "h";
$shortopts .= "x";
$shortopts .= "d";
$shortopts .= "C";
$shortopts .= "J";
$shortopts .= "M";
$shortopts .= "D";
$shortopts .= "L";
$shortopts .= "I";
$shortopts .= "m:";       // Обязательное значение
$shortopts .= "t:";       // Обязательное значение
$shortopts .= "T:";       // Обязательное значение
$shortopts .= "S:";       // Обязательное значение

$longopts = array(
	"help",
	"debug",
	"dont-use-colors",
	"print-json",
	"print-machine",
	"dont-recalc",
	"dumb-test-print",
	"list-tests",
	"system-info",
	"memory-limit:",      // Обязательное значение
	"time-limit:",        // Обязательное значение
	"run-test:",          // Обязательное значение
	"skip-test:",          // Обязательное значение
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

	// First - simple options that do not do any output
	foreach ($options as $okey => $oval) {

		switch ($okey) {
			case 'd':
			case 'dont-recalc':
				$recalculateLimits = 0;
				break;

			case 'x':
			case 'debug':
				$debugMode = 1;
				break;

			case 'C':
			case 'dont-use-colors':
				$useColors = 0;
				break;

			case 'J':
			case 'print-json':
				$printJson = 1;
				$printMachine = 0;
				break;

			case 'M':
			case 'print-machine':
				$printMachine = 1;
				$printJson = 0;
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

		} // switch key

	} // for options


	// Drop colors here
	if (!$useColors || $printJson || $printMachine) {
		$colorReset = '';
		$colorRed = '';
		$colorGreen = '';
		$colorYellow = '';
		$colorGray = '';
	}


	// Start JSON output here
	if ($printJson) print("{ " . PHP_EOL);

	foreach ($options as $okey => $oval) {

		switch ($okey) {

			case 'h':
			case 'help':
				if ($hasLongOpts) {
					print_pre(
						PHP_EOL
						. 'PHP Benchmark Performance Script, version ' . $scriptVersion . PHP_EOL
						. PHP_EOL
						. 'Usage: ' . basename(__FILE__) . ' [-h|--help] [-x|--debug] [-C|--dont-use-colors] [-J|--print-json] [-M|--print-machine] [-d|--dont-recalc] [-D|--dumb-test-print] [-L|--list-tests] [-I|--system-info] [-S|--do-not-task-set] [-m|--memory-limit=130] [-t|--time-limit=600] [-T|--run-test=name] [-S|--skip-test=pattern]' . PHP_EOL
						. PHP_EOL
						. '	-h|--help		- print this help and exit' . PHP_EOL
						. '	-x|--debug		- enable debug mode, raise output level' . PHP_EOL
						. '	-C|--dont-use-colors	- disable printing html-span or color sequences for capable terminal: xterm, *-color, *-256color. And not use it in JSON/machine mode.' . PHP_EOL
						. '	-J|--print-json	- enable printing only in JSON format, useful for automated tests. disables print-machine.' . PHP_EOL
						. '	-M|--print-machine	- enable printing only in machine parsable format, useful for automated tests. disables print-json.' . PHP_EOL
						. '	-d|--dont-recalc	- do not recalculate test times / operations count even if memory of execution time limits are low' . PHP_EOL
						. '	-D|--dumb-test-print	- print dumb test time, for debug purpose' . PHP_EOL
						. '	-L|--list-tests		- output list of available tests and exit' . PHP_EOL
						. '	-I|--system-info	- output system info but do not run tests and exit' . PHP_EOL
						. '	-m|--memory-limit <Mb>	- set memory_limit value in Mb, defaults to 130 (Mb)' . PHP_EOL
						. '	-t|--time-limit <sec>	- set max_execution_time value in seconds, defaults to 600 (sec)' . PHP_EOL
						. '	-T|--run-test <pattern>	- run selected tests, test names from --list-tests output, can be defined multiple times' . PHP_EOL
						. '	-S|--skip-test <pattern>	- skip selected tests, test names pattern to match name from --list-tests output, can be defined multiple times' . PHP_EOL
						. PHP_EOL
						. 'Example: php ' . basename(__FILE__) . ' -m=64 -t=30' . PHP_EOL
						. PHP_EOL
					);
				} else {
					print_pre(
						PHP_EOL
						. 'PHP Benchmark Performance Script, version ' . $scriptVersion . PHP_EOL
						. PHP_EOL
						. 'Usage: ' . basename(__FILE__) . ' [-h] [-x] [-C] [-J] [-M] [-d] [-D] [-L] [-I] [-S] [-m 130] [-t 600] [-T name]' . PHP_EOL
						. PHP_EOL
						. '	-h		- print this help and exit' . PHP_EOL
						. '	-x		- enable debug mode, raise output level' . PHP_EOL
						. '	-C		- disable printing html-span or color sequences for capable terminal: xterm, *-color, *-256color. And not use it in JSON/machine mode.' . PHP_EOL
						. '	-J		- enable printing only in JSON format, useful for automated tests. disables print-machine.' . PHP_EOL
						. '	-M		- enable printing only in machine parsable format, useful for automated tests. disables print-json.' . PHP_EOL
						. '	-d		- do not recalculate test times / operations count even if memory of execution time limits are low' . PHP_EOL
						. '	-D		- print dumb test time, for debug purpose' . PHP_EOL
						. '	-L		- output list of available tests and exit' . PHP_EOL
						. '	-I		- output system info but do not run tests and exit' . PHP_EOL
						. '	-m <Mb>		- set memory_limit value in Mb, defaults to 130 (Mb)' . PHP_EOL
						. '	-t <sec>	- set max_execution_time value in seconds, defaults to 600 (sec)' . PHP_EOL
						. '	-T <pattern>	- run selected tests, test names from -L output, can be defined multiple times' . PHP_EOL
						. '	-S <pattern>	- skip selected tests, test names pattern to match name from -L output, can be defined multiple times' . PHP_EOL
						. PHP_EOL
						. 'Example: php ' . basename(__FILE__) . ' -m 64 -t 30' . PHP_EOL
						. PHP_EOL
					);
				}
				if ($printJson) {
					print("\"messages_count\": {$messagesCnt},\n");
					print("\"end\":true\n}" . PHP_EOL);
				}
				exit(0);
				break;

			case 'm':
			case 'memory-limit':
				if (is_numeric($oval)) {
					$defaultMemoryLimit = (int)$oval;
				} else {
					print_pre("{$colorYellow}<<< WARNING >>>{$colorReset} Option '$okey' has not numeric value '$oval'! Skip." . PHP_EOL);
				}
				break;

			case 't':
			case 'time-limit':
				if (is_numeric($oval)) {
					$defaultTimeLimit = (int)$oval;
				} else {
					print_pre("{$colorYellow}<<< WARNING >>>{$colorReset} Option '$okey' has not numeric value '$oval'! Skip." . PHP_EOL);
				}
				break;

			case 'T':
			case 'run-test':
				// Multiple values are joined into array
				if (!empty($oval)) {
					$selectedTests = (array)$oval;
				} else {
					print_pre("{$colorYellow}<<< WARNING >>>{$colorReset} Option '$okey' has no value! Skip." . PHP_EOL);
				}
				break;


			case 'S':
			case 'skip-test':
				// Multiple values are joined into array
				if (!empty($oval)) {
					$skipTests = (array)$oval;
				} else {
					print_pre("{$colorYellow}<<< WARNING >>>{$colorReset} Option '$okey' has no value! Skip." . PHP_EOL);
				}
				break;


			case 'd':
			case 'dont-recalc':
			case 'x':
			case 'debug':
			case 'C':
			case 'dont-use-colors':
			case 'J':
			case 'print-json':
			case 'M':
			case 'print-machine':
			case 'D':
			case 'dumb-test-print':
			case 'L':
			case 'list-tests':
			case 'I':
			case 'system-info':
				// Done in previous cycle
				break;

			default:
				print_pre("{$colorYellow}<<< WARNING >>>{$colorReset} Unknown option '$okey'!" . PHP_EOL);
		}

	}

} // if options


} // if sapi == cli


// Drop colors here too
if (!$useColors || $printJson || $printMachine) {
	$colorReset = '';
	$colorRed = '';
	$colorGreen = '';
	$colorYellow = '';
	$colorGray = '';
}


if (php_sapi_name() != 'cli') {
	// Hello, nginx!
	header('X-Accel-Buffering: no', true);
	if ($printJson) {
		header('Content-Type: application/json', true);
	} else {
		header('Content-Type: text/html; charset=utf-8', true);
	}
	flush();
} else {
	$flushStr = '';
}

$tz = ini_get('date.timezone');
if (!$tz) ini_set('date.timezone', 'Europe/Moscow');

ini_set('display_errors', 0);
@ini_set('error_log', null);
ini_set('implicit_flush', 1);
ini_set('output_buffering', 0);
ob_implicit_flush(1);

// Disable explicit error reporting
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Check XDebug
$xdebug = (int)ini_get('xdebug.default_enable');
if ($xdebug) {
	print_pre("{$colorYellow}<<< WARNING >>>{$colorReset} You need to disable Xdebug extension! It greatly slow things down! And mess with PHP internals.".PHP_EOL);
}

// Check OpCache
if (php_sapi_name() != 'cli') {
	$opcache = (int)ini_get('opcache.enable');
	if ($opcache) {
		print_pre("{$colorYellow}<<< WARNING >>>{$colorReset} You may want to disable OpCache extension! It can greatly affect the results! Make it via .htaccess, VHost or FPM config.".PHP_EOL);
	}
	$apcache = (int)ini_get('apc.enabled');
} else {
	$opcache = (int)ini_get('opcache.enable_cli');
	if ($opcache) {
		print_pre("{$colorYellow}<<< WARNING >>>{$colorReset} You may want to disable Cli OpCache extension! It can greatly affect the results! Run php with param: -dopcache.enable_cli=0".PHP_EOL);
	}
	$apcache = (int)ini_get('apc.enable_cli');
}
$xcache = (int)ini_get('xcache.cacher');
$eaccel = (int)ini_get('eaccelerator.enable');

$mbover = (int)ini_get('mbstring.func_overload');
if ($mbover != 0) {
	print_pre("{$colorYellow}<<< WARNING >>>{$colorReset} You must disable mbstring string functions overloading! It greatly slow things down! And messes with results.".PHP_EOL);
}

$obd_set = (int)!in_array(ini_get('open_basedir'), array('', null));
if ($obd_set != 0) {
	print_pre("{$colorYellow}<<< WARNING >>>{$colorReset} You should unset `open_basedir` parameter! It may slow things down!".PHP_EOL);
	print_pre("{$colorYellow}<<< WARNING >>>{$colorReset} Parameter `open_basedir` in effect! Script may not able to read system CPU and Memory information. Memory adjustment for tests may not work.\n");
}

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
	print_pre("{$colorRed}<<< ERROR >>>{$colorReset} Need PHP 4.3+! Current version is " . PHP_VERSION .PHP_EOL);
	if ($printJson) {
		print("\"messages_count\": {$messagesCnt},\n");
		print("\"end\":true\n}".PHP_EOL);
	}
	exit(1);
}
if (!defined('PHP_MAJOR_VERSION')) {
	define('PHP_MAJOR_VERSION', (int)$phpversion[0]);
}
if (!defined('PHP_MINOR_VERSION')) {
	define('PHP_MINOR_VERSION', (int)$phpversion[1]);
}

if ($debugMode) {
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
}

$set = set_time_limit($defaultTimeLimit);
if ($set === false) {
	print_pre("{$colorYellow}<<< WARNING >>>{$colorReset} Execution time limit not droppped to '{$defaultTimeLimit}' seconds!\nScript will have only '{$originTimeLimit}' seconds to run." . PHP_EOL);
}
$set = ini_set('memory_limit', $defaultMemoryLimit . 'M');
if ($set === false) {
	print_pre("{$colorYellow}<<< WARNING >>>{$colorReset} Memory limit not set to '{$defaultMemoryLimit}'!" . PHP_EOL);
}

/** ------------------------------- Main Constants ------------------------------- */

$line = str_pad("-", 91, "-");
$padHeader = 89;
$padInfo = 19;
$padLabel = 30;

$emptyResult = array(0, '-.---', '-.-- ', '-.-- ', 0);

$cryptSalt = null;
$cryptAlgoName = 'default';

// That gives around 130Mb memory use and reasonable test time
$testMemoryFull = 130 * 1024 * 1024;
// That gives around 8Mb memory use to run every tests
$testMemoryMin = 5 * 1024 * 1024;
// Arrays are matrix [$dimention] x [$dimention]
$arrayDimensionLimit = 600;

// That limit gives around 128Mb too
$stringConcatLoopRepeat = 5;

$runOnlySelectedTests = !empty($selectedTests);

$stringTest = "    the quick <b>brown</b> fox jumps <i>over</i> the lazy dog and eat <span>lorem ipsum</span><br/> Valar morghulis  <br/>\n\rабыр\nвалар дохаэрис   <span class='alert alert-danger'>У нас закончились ложки, Нео!</span>      ";
$regexPattern = '/[\s,]+/';

/** ---------------------------------- Tests limits - to recalculate -------------------------------------------- */

// Gathered on this machine
$loopMaxPhpTimesMHz = 3800;
// How much time needed for tests on this machine
$loopMaxPhpTimes = array(
	'4.4' => 324,
	'5.2' => 248,
	'5.3' => 211,
	'5.4' => 199,
	'5.5' => 200,
	'5.6' => 204,
	'7.0' => 106,
	'7.1' => 104,
	'7.2' => 98,
	'7.3' => 89,
	'7.4' => 89,
	'8.0' => 83,
	'8.1' => 82,
	'8.2' => 79,
	'8.3' => 77,
	'8.4' => 77
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
	'8.2' => 0.294,
	'8.3' => 0.784,
	'8.4' => 0.759
);
// Nice dice roll
// Should not be longer than 600 seconds
$testsLoopLimits = array(
	'01_math'			=> 2000000,
	// That limit gives around 90Mb
	'02_string_concat'	=> 5000000,
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
	'11_igb_serialize'		=> 1300000,
	'12_igb_unserialize'	=> 1300000,
	'11_msgpack_pack'		=> 1300000,
	'12_msgpack_unpack'	=> 1300000,
	'13_array_loop'		=> 250,
	'14_array_loop'		=> 250,
	'15_clean_loops'	=> 200000000,
	'16_loop_ifelse'	=> 100000000,
	'17_loop_ternary'	=> 100000000,
	'18_1_loop_def'		=> 50000000,
	'18_2_loop_undef'	=> 50000000,
	'19_type_func'		=> 4000000,
	'20_type_cast'		=> 4000000,
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
	'33_phpinfo_generate'		=> 10000,
	'34_gd_qrcode'		=> 1000,
	'35_imagick_qrcode'		=> 1000,
	'36_01_zlib_deflate_compress'	=> 500000,
	'36_02_zlib_gzip_compress'	=> 500000,
	'36_bzip2_compress'	=>  50000,
	'36_lz4_compress'	=> 5000000,
	'36_snappy_compress'	=> 5000000,
	'36_zstd_compress'	=> 5000000,
	'36_brotli_compress'	=> 1000000,
	'37_01_php8_str_ccontains' => 100000,
	'37_02_php8_str_ccontains_simulate' => 100000,
	'38_01_php_uuid'	=> 1000000,
	'38_02_mod_uuid'	=> 1000000,
	'39_01_kvstorage_memory'	=> 1000000,
);
// Should not be more than X Mb
// Different PHP could use different amount of memory
// There defined maximum possible
$testsMemoryLimits = array(
	'01_math'			=> 4,
	'02_string_concat'	=> 90,
	'03_1_string_number_concat'	=> 4,
	'03_2_string_number_format'	=> 4,
	'04_string_simple'	=> 4,
	'05_string_mb'		=> 4,
	'06_string_manip'	=> 4,
	'07_regex'			=> 4,
	'08_1_hashing'		=> 4,
	'08_2_crypt'		=> 4,
	'09_json_encode'	=> 4,
	'10_json_decode'	=> 4,
	'11_serialize'		=> 4,
	'12_unserialize'	=> 4,
	'11_igb_serialize'		=> 4,
	'12_igb_unserialize'	=> 4,
	// php-5.3
	'13_array_loop'		=> 54,
	'14_array_loop'		=> 62,
	// opcache, php-7.4
	'15_clean_loops'	=> 14,
	'16_loop_ifelse'	=> 14,
	'17_loop_ternary'	=> 14,
	'18_1_loop_def'		=> 14,
	'18_2_loop_undef'	=> 14,
	'19_type_func'		=> 14,
	'20_type_cast'		=> 14,
	'21_loop_except'	=> 14,
	'22_loop_nullop'	=> 14,
	'23_loop_spaceship'	=> 14,
	'26_1_public'		=> 14,
	'26_2_getset'		=> 14,
	'26_3_magic'		=> 14,
	'27_simplexml'		=> 14,
	'28_domxml'			=> 14,
	'29_datetime'		=> 14,
	'30_intl_number_format'		=> 14,
	'31_intl_message_format'	=> 14,
	'32_intl_calendar'			=> 14,
	'33_phpinfo_generate'		=> 14,
	'34_gd_qrcode'		=> 14,
	'35_imagick_qrcode'		=> 8,
	'36_01_zlib_deflate_compress'		=> 4,
	'36_02_zlib_gzip_compress'		=> 4,
	'36_bzip2_compress'		=> 4,
	'36_lz4_compress'		=> 4,
	'36_snappy_compress'		=> 4,
	'36_zstd_compress'		=> 4,
	'36_brotli_compress'		=> 4,
	'37_01_php8_str_ccontains' => 4,
	'37_02_php8_str_ccontains_simulate' => 4,
	'38_01_php_uuid'		=> 4,
	'38_02_mod_uuid'		=> 4,
	'39_01_kvstorage_memory'		=> 4,
);

/** ---------------------------------- Common functions -------------------------------------------- */

/**
 * Get pretty OS release name, if available
 */
function get_current_os()
{
	$osFile = '/etc/os-release';
	$result = PHP_OS;
	if (@is_readable($osFile)) {
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
 * 
 * @return int
 */
function getPhpMemoryLimitBytes()
{
	global $debugMode, $colorGray, $colorReset;
	// http://stackoverflow.com/a/10209530
	$memory_limit = strtolower(ini_get('memory_limit'));
	if ($debugMode) {
		print_pre("{$colorGray}<<< DEBUG >>>{$colorReset} getPhpMemoryLimitBytes(): ini_get memory_limit = '{$memory_limit}'\n");
	}
	if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
		if ($debugMode) {
			$ve = var_export($matches, true);
			print_pre("{$colorGray}<<< DEBUG >>>{$colorReset} getPhpMemoryLimitBytes(): parse via preg_math:\n{$ve}\n");
		}
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
	if ($debugMode) {
		print_pre("{$colorGray}<<< DEBUG >>>{$colorReset} getPhpMemoryLimitBytes(): result memory_limit = '{$memory_limit}'\n");
	}
	return $memory_limit;
}

/**
 * Return array (dict) with system memory info.
 * All values in bytes.
 * http://stackoverflow.com/a/1455610
 */
function getSystemMemInfo()
{
	global $debugMode, $colorGray, $colorReset;

	$meminfo = array();
	if (! @is_readable("/proc/meminfo")) {
		if ($debugMode) {
			print_pre("{$colorGray}<<< DEBUG >>>{$colorReset} Can't read /proc/meminfo!" . PHP_EOL);
		}
		return $meminfo;
	}
	$data = explode("\n", file_get_contents("/proc/meminfo"));
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
 * 
 * @return int
 */
function getSystemMemoryFreeLimitBytes()
{
	global $debugMode, $colorGray, $colorReset;

	$info = getSystemMemInfo();

	if ($debugMode) {
		$ve = var_export($info, true);
		print_pre("{$colorGray}<<< DEBUG >>>{$colorReset} getSystemMemoryFreeLimitBytes(): system memory info:\n{$ve}'\n");
	}

	if (empty($info)) {
		return -1;
	}

	if (isset($info['MemAvailable'])) {
		if ($debugMode) {
			print_pre("{$colorGray}<<< DEBUG >>>{$colorReset} getSystemMemoryFreeLimitBytes(): return MemAvailable: {$info['MemAvailable']}\n");
		}
		return $info['MemAvailable'];
	}
	$available = $info['MemFree'] + $info['Cached'] + $info['Buffers'];
	if ($debugMode) {
		print_pre("{$colorGray}<<< DEBUG >>>{$colorReset} getSystemMemoryFreeLimitBytes(): return MemFree + Cached + Buffers: {$available}\n");
	}
	return $available;
}

/**
 * Read /proc/cpuinfo, fetch some data
 */
function getCpuInfo($fireUpCpu = false)
{
	global $debugMode, $colorGray, $colorReset;

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

	if (! @is_readable('/proc/cpuinfo')) {
		if ($debugMode) {
			print_pre("{$colorGray}<<< DEBUG >>>{$colorReset} Can't read /proc/cpuinfo!" . PHP_EOL);
		}
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
	if (@is_readable('/sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq')) {
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
	if (@is_executable('/usr/bin/lscpu')) {
		$cpuData = explode("\n", shell_exec('/usr/bin/lscpu 2>&1'));
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
	if ($printJson || $printMachine) {
		print_pre("<<< WARNING >>> Hashing algorithm MD5 not available for crypt() in this PHP build! It should be available in any PHP build." . PHP_EOL);
	} else {
		print_pre("$line\n{$colorYellow}<<< WARNING >>>{$colorReset}\nHashing algorithm MD5 not available for crypt() in this PHP build!\n It should be available in any PHP build.\n$line" . PHP_EOL);
	}
}

$cpuInfo = getCpuInfo();
// CPU throttling?
if ($cpuInfo['mips'] && $cpuInfo['mhz']) {
	if (abs($cpuInfo['mips'] - $cpuInfo['mhz']) > 300) {
		if ($printJson || $printMachine) {
			print_pre("<<< WARNING >>> CPU is in powersaving mode? Set CPU governor to 'performance'! Fire up CPU and recalculate MHz!" . PHP_EOL);
		} else {
			print_pre("$line\n{$colorYellow}<<< WARNING >>>{$colorReset}\nCPU is in powersaving mode? Set CPU governor to 'performance'!\n Fire up CPU and recalculate MHz!\n$line" . PHP_EOL);
		}
		// TIME WASTED HERE
		$cpuInfo = getCpuInfo(true);
	}
} else if ($cpuInfo['max-mhz'] && $cpuInfo['mhz']) {
	if (abs($cpuInfo['max-mhz'] - $cpuInfo['mhz']) > 300) {
		if ($printJson || $printMachine) {
			print_pre("<<< WARNING >>> CPU is in powersaving mode? Set CPU governor to 'performance'! Fire up CPU and recalculate MHz!" . PHP_EOL);
		} else {
			print_pre("$line\n{$colorYellow}<<< WARNING >>>{$colorReset}\nCPU is in powersaving mode? Set CPU governor to 'performance'!\n Fire up CPU and recalculate MHz!\n$line" . PHP_EOL);
		}
		// TIME WASTED HERE
		$cpuInfo = getCpuInfo(true);
	}
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

if ($debugMode) {
	print_pre("{$colorGray}<<< DEBUG >>>{$colorReset} Need time: " .$needTime . "; Max time: " .$maxTime . PHP_EOL);
}

$memoryLimitPhp = getPhpMemoryLimitBytes();
$memoryLimitSystem = getSystemMemoryFreeLimitBytes();
if ($debugMode) {
	print_pre("{$colorGray}<<< DEBUG >>>{$colorReset} Available memory in system: " . convert($memoryLimitSystem) . PHP_EOL);
	print_pre("{$colorGray}<<< DEBUG >>>{$colorReset} Available memory for php  : " . convert($memoryLimitPhp) . PHP_EOL);
}

if ($memoryLimitSystem < 0) {
	// Can't read /proc/meminfo? Drop it.
	$memoryLimitSystem = $memoryLimitPhp;
	if ($debugMode) {
		print_pre("{$colorGray}<<< DEBUG >>>{$colorReset} Can't read available memory in system. Set it equal to PHP's." . PHP_EOL);
	}
}

$memoryLimit = min($memoryLimitPhp, $memoryLimitSystem);
$memoryLimitMb = convert($memoryLimit);
if ($debugMode) {
	print_pre("{$colorGray}<<< DEBUG >>>{$colorReset} Selected memory for php   : " . $memoryLimitMb . PHP_EOL);
}

if (!$memoryLimit || $memoryLimit == '0' || (int)$memoryLimit < $testMemoryMin) {
	print_pre("{$colorRed}<<< ERROR >>>{$colorReset} Available memory is set too low: ".convert($memoryLimitMb).".\nThis is lower than ".convert($testMemoryMin).".\nAbort execution!" . PHP_EOL);
	if ($printJson) {
		print("\"messages_count\": {$messagesCnt},\n");
		print("\"end\":true\n}" . PHP_EOL);
	}
	exit(1);
}

// Run tests or not?
if (!$outputTestsList && !$showOnlySystemInfo) {

	// Adjust array tests limits
	if ($memoryLimit < $testMemoryFull) {

		print_pre("$line\n{$colorYellow}<<< WARNING >>>{$colorReset}\nAvailable memory " . $memoryLimitMb
			. " is less than required to run full set of tests: " . convert($testMemoryFull)
			. ".\n Recalculate tests parameters to fit in memory limits."
			. "\n$line" . PHP_EOL);
		if ($debugMode) {
			print_pre("{$colorGray}<<< DEBUG >>>{$colorReset} Original memory limit for php  : " . convert($originMemoryLimit) . PHP_EOL);
			print_pre("{$colorGray}<<< DEBUG >>>{$colorReset} Calculated memory limit for php: " . convert($defaultMemoryLimit) . PHP_EOL);
		}

		foreach ($testsMemoryLimits as $testName => $limitMb) {

			$limitBytes = $limitMb * 1024 * 1024;

			// Do we need to check and recalculate limits here?
			// Or we need to fit memory anyway?
			if ($limitBytes > $memoryLimit) {

				$factor = 1.0 * ($limitBytes - $memoryLimit) / $limitBytes;
				if ($debugMode) {
					print_pre("{$colorGray}<<< DEBUG >>>{$colorReset} Test: {$testName}, need memory: {$limitMb}, memory factor: " . number_format($factor, 6, '.', '') . PHP_EOL);
				}
			
				// Only if memory not enough, and memory available
				if ($factor > 0 && $factor < 1.0) {

					// Special hack for php-7.x
					// New string classes, new memory allocator
					// Consumes more, allocate huge blocks
					if ((int)$phpversion[0] >= 7) $factor *= 1.1;

					$diff = (int)($factor * $testsLoopLimits[ $testName ]);
					if (in_array($testName, array('13_array_loop', '14_array_loop'))) {
						$arrayDimensionLimit = (int)($factor * $arrayDimensionLimit);
						$diff = 0;
					}

					$testsLoopLimits[$testName] -= $diff;
				}

			}
		}

	}
	if ($debugMode) print_pre("recalculate limits: " . $recalculateLimits);
	if ($recalculateLimits) {

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
		} 
		if ($debugMode) {
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
			print_pre("$line\n{$colorYellow}<<< WARNING >>>{$colorReset}\nYour processor '{$cpuModel}' have too low performance!\n$line" . PHP_EOL);
			$factor = 1.0/3;
		}

		if ($factor < 1.0) {
			print_pre("$line\n{$colorYellow}<<< WARNING >>>{$colorReset}\nMax execution time is less than needed for tests!\nWill try to reduce tests time as much as possible.\nFactor is: '$factor'\n$line" . PHP_EOL);
			foreach ($testsLoopLimits as $tst => $loops) {
				$testsLoopLimits[$tst] = (int)($loops * $factor);
			}
		}

	} // recalculate time limits

} // Not only show tests names or system info. or not?

/** ---------------------------------- Common functions for tests -------------------------------------------- */

/**
 * @return array((int)seconds, (str)seconds, (str)operations/sec), (str)opterations/MHz)
 */
function format_result_test($diffSeconds, $opCount, $memory = 0)
{
	global $cpuInfo, $rawValues4json;
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

		if ($rawValues4json) {
			return array($diffSeconds, $diffSeconds,
				$ops, $opmhz, $memory
			);
		} 
		return array($diffSeconds, number_format($diffSeconds, 3, '.', ''),
			number_format($ops_v, 2, '.', '') . ' ' . $ops_u,
			$opmhz ? number_format($opmhz_v, 2, '.', '') . ' ' . $opmhz_u : '-.-- ' . $opmhz_u,
			convert($memory)
		);
	} else {
		if ($rawValues4json) {
			return array(0, 0, 0, 0, 0);
		}
		return array(0, '0.000', 'x.xx ', 'x.xx ', 0);
	}
}


/** ---------------------------------- Tests functions -------------------------------------------- */

if (is_file('common.inc')) {
	include_once 'common.inc';
} else {
	print_pre("$line\n{$colorRed}<<< ERROR >>>{$colorReset}\nMissing file 'common.inc' with common tests!\n$line");
	if ($printJson) {
		print("\"messages_count\": {$messagesCnt},\n");
		print("\"end\":true\n}" . PHP_EOL);
	}
	exit(1);
}
if ((int)$phpversion[0] >= 5) {
	if (is_file('php5.inc')) {
		include_once 'php5.inc';
	} else {
		print_pre("$line\n{$colorYellow}<<< WARNING >>>{$colorReset}\nMissing file 'php5.inc' with try/Exception/catch loop test!\n It matters only for php version 5+.\n$line");
	}
}

if ((int)$phpversion[0] >= 7) {
	if (is_file('php7.inc')) {
		include_once 'php7.inc';
	} else {
		print_pre("$line\n{$colorYellow}<<< WARNING >>>{$colorReset}\nMissing file 'php7.inc' with PHP 7 new features tests!\n It matters only for php version 7+.\n$line");
	}
}

if ((int)$phpversion[0] >= 8) {
	if (is_file('php8.inc')) {
		include_once 'php8.inc';
	} else {
		print_pre("$line\n{$colorYellow}<<< WARNING >>>{$colorReset}\nMissing file 'php8.inc' with PHP 8 new features tests!\n It matters only for php version 8+.\n$line");
	}
}

$functions = get_defined_functions();
$availableFunctions =$functions['user'];
sort($availableFunctions);

// fiter in tests
function filter_in_name_by_pattern($key)
{
    global $runTests, $debugMode, $availableFunctions;
    $var = $availableFunctions[$key];
    $ret = 0;
    foreach ($runTests as $pattern){
	// simple test - str in name
	$c=strpos($var,$pattern);
	if ($debugMode) {
		$d=var_export($c,true);
		print("Search '$pattern' inside '$var':$d\n");
	}
	if ($c!==false) {
		$ret = 0;
		break;
	};
    }
    //nothing found - skipping
    if ($debugMode) print("Will return $ret\n");
    if (!$ret) unset($availableFunctions[$key]);
    return $ret;
}
// fiter out tests
function filter_out_name_by_pattern($key)
{
    global $skipTests, $debugMode, $availableFunctions;
    $var = $availableFunctions[$key];
    $ret = 1;
    foreach ($skipTests as $pattern){
	// simple test - str in name
	$c=strpos($var,$pattern);
	if ($debugMode) {
		$d=var_export($c,true);
		print("Search '$pattern' inside '$var':$d\n");
	}
	if ($c!==false) {
		$ret = 0;
		break;
	};
    }
    //nothing found - not skipping
    if ($debugMode) print("Will return $ret\n");
    if (!$ret) unset($availableFunctions[$key]);
    return $ret;
}
if ($runTests) array_filter($availableFunctions, "filter_in_name_by_pattern",ARRAY_FILTER_USE_KEY);
if ($skipTests) array_filter($availableFunctions, "filter_out_name_by_pattern",ARRAY_FILTER_USE_KEY);
/** ------------------------------- Early checks ------------------------------- */

if ($outputTestsList) {
	if (!$printJson) {
		if (php_sapi_name() != 'cli') {
			print("<pre>");
		}
		print("\nAvailable tests:\n");
		foreach ($availableFunctions as $user) {
			if (strpos($user, 'test_') === 0) {
				$testName = str_replace('test_', '', $user);
				print($testName . PHP_EOL);
			}
		}
		if (php_sapi_name() != 'cli') {
			print("</pre>\n");
		}
	} else {
		print("tests: [".PHP_EOL);
		$a = array();
		foreach ($availableFunctions as $user) {
			if (strpos($user, 'test_') === 0) {
				$testName = str_replace('test_', '', $user);
				$a[] = $testName;
			}
		}
		print(join(',', $a));
		print("]".PHP_EOL);
	}
	
	if ($printJson) {
		print("\"messages_count\": {$messagesCnt},\n");
		print("\"end\":true\n}" . PHP_EOL);
	}
	exit(0);
}

/** ---------------------------------- Common code -------------------------------------------- */

$has_mbstring = "{$colorGreen}yes{$colorReset}";
if (!function_exists('mb_strlen')) {
	print_pre("{$colorYellow}<<< WARNING >>>{$colorReset} Extension 'mbstring' not loaded or not compiled! Multi-byte string tests will produce empty result!");
	$has_mbstring = "{$colorRed}no{$colorReset}";
}
$has_json = "{$colorGreen}yes{$colorReset}";
if (!function_exists('json_encode')) {
	print_pre("{$colorYellow}<<< WARNING >>>{$colorReset} Extension 'json' not loaded or not compiled! JSON tests will produce empty result!");
	$has_json = "{$colorRed}no{$colorReset}";
	if ($printJson) {
		print_pre("{$colorRed}<<< ERROR >>>{$colorReset} Extension 'json' is mandatory for JSON output!");
		print("\"messages_count\": {$messagesCnt},\n");
		print("\"end\":true\n}" . PHP_EOL);
		exit(-1);
	}
}
$has_pcre = "{$colorGreen}yes{$colorReset}";
if (!function_exists('preg_match')) {
	print_pre("{$colorYellow}<<< WARNING >>>{$colorReset} Extension 'pcre' not loaded or not compiled! Regex tests will procude empty result!");
	$has_pcre = "{$colorRed}no{$colorReset}";
}
$has_opcache = "{$colorGreen}no{$colorReset}";
if (extension_loaded('Zend OPcache')) {
	$has_opcache = "{$colorYellow}yes{$colorReset}";
}
$has_xcache = "{$colorGreen}no{$colorReset}";
if (extension_loaded('XCache')) {
	$has_xcache = "{$colorYellow}yes{$colorReset}";
}
$has_apc = "{$colorGreen}no{$colorReset}";
if (extension_loaded('apc')) {
	$has_apc = "{$colorYellow}yes{$colorReset}";
}
$has_eacc = "{$colorGreen}no{$colorReset}";
if (extension_loaded('eAccelerator')) {
	$has_eacc = "{$colorYellow}yes{$colorReset}";
}
$has_gd = "{$colorYellow}no{$colorReset}";
if (extension_loaded('gd')) {
	$has_gd = "{$colorGreen}yes{$colorReset}";
	$info = gd_info();
	if(!defined("GD_VERSION")) define("GD_VERSION",$info["GD Version"]);
} else {
	define("GD_VERSION","-.-.-");
}
$has_imagick = "{$colorYellow}no{$colorReset}";
if (extension_loaded('imagick')) {
	$has_imagick = "{$colorGreen}yes{$colorReset}";
	$imv = Imagick::getVersion();
	define("IMG_VERSION", $imv["versionString"]);
} else {
	define("IMG_VERSION", "-.-.-");
}
$has_xdebug = "{$colorGreen}no{$colorReset}";
if (extension_loaded('xdebug')) {
	print_pre("{$colorYellow}<<< WARNING >>>{$colorReset} Extension 'xdebug' loaded! It will affect results and slow things greatly! Even if not enabled!\n");
	print_pre("{$colorYellow}<<< WARNING >>>{$colorReset} Set xdebug.mode in php.ini / VHost or FPM config / php_admin_value or via cmd '-dxdebug.mode=off' option of PHP executable.\n");
	$has_xdebug = "{$colorRed}yes{$colorReset}";
	ini_set("xdebug.mode", "off");
	ini_set("xdebug.default_enable", 0);
	ini_set("xdebug.remote_autostart", 0);
	ini_set("xdebug.remote_enable", 0);
	ini_set("xdebug.profiler_enable", 0);
}
$xdbg_mode = ini_get("xdebug.mode");

$has_dom = "{$colorYellow}no{$colorReset}";
if (extension_loaded('dom')) {
	$has_dom = "{$colorGreen}yes{$colorReset}";
}
$has_simplexml = "{$colorYellow}no{$colorReset}";
if (extension_loaded('simplexml')) {
	$has_simplexml = "{$colorGreen}yes{$colorReset}";
}
$has_intl = "{$colorYellow}no{$colorReset}";
if (extension_loaded('intl')) {
	$has_intl = "{$colorGreen}yes{$colorReset}";
}
$has_zlib = "{$colorYellow}no{$colorReset}";
$has_gzip = "{$colorYellow}no{$colorReset}";
if (extension_loaded('zlib')) {
	$has_zlib = "{$colorGreen}yes{$colorReset}";
	if(function_exists('gzencode')) {
		$has_gzip = "{$colorGreen}yes{$colorReset}";
	}
}
$has_bz2 = "{$colorYellow}no{$colorReset}";
if (extension_loaded('bz2')) {
	$has_bz2 = "{$colorGreen}yes{$colorReset}";
}
$has_lz4 = "{$colorYellow}no{$colorReset}";
if (extension_loaded('lz4')) {
	$has_lz4 = "{$colorGreen}yes{$colorReset}";
}
$has_snappy = "{$colorYellow}no{$colorReset}";
if (extension_loaded('snappy')) {
	$has_snappy = "{$colorGreen}yes{$colorReset}";
}
$has_zstd = "{$colorYellow}no{$colorReset}";
if (extension_loaded('zstd')) {
	$has_zstd = "{$colorGreen}yes{$colorReset}";
}
$has_brotli = "{$colorYellow}no{$colorReset}";
if (extension_loaded('brotli')) {
	$has_brotli = "{$colorGreen}yes{$colorReset}";
}

$has_uuid = "{$colorYellow}no{$colorReset}";
if (extension_loaded('uuid')) {
	$has_uuid = "{$colorGreen}yes{$colorReset}";
}

$has_jsond = "{$colorYellow}no{$colorReset}";
$has_jsond_as_json = "{$colorYellow}no{$colorReset}";
if ($jsond = extension_loaded('jsond')) {
	$has_jsond = "{$colorGreen}yes{$colorReset}";
}
if ($jsond && !function_exists('jsond_encode')) {
	$has_jsond_as_json = "{$colorGreen}yes{$colorReset}";
}

if (!defined('PCRE_VERSION')) define('PCRE_VERSION', '-.--');
if (!defined('ZLIB_VERSION')) define('ZLIB_VERSION', '-.--');
if (!defined('LIBXML_DOTTED_VERSION')) define('LIBXML_DOTTED_VERSION', '-.-.-');
if (!defined('INTL_ICU_VERSION')) define('INTL_ICU_VERSION', '-.-');
if (!defined('LIBZSTD_VERSION_STRING')) define('LIBZSTD_VERSION_STRING', '-.-.-');

function print_results_common()
{
	$total = 0;

	global $availableFunctions;
	global $line, $padHeader, $cpuInfo, $padInfo, $scriptVersion, $maxTime, $originTimeLimit, $originMemoryLimit, $cryptAlgoName, $memoryLimitMb;
	global $flushStr, $has_apc, $has_pcre, $has_intl, $has_json, $has_simplexml, $has_dom, $has_mbstring, $has_opcache, $has_xcache;
	global $has_gd, $has_imagick, $has_igb, $has_msg, $has_jsond, $has_jsond_as_json;
	global $has_zlib, $has_uuid, $has_gzip, $has_bz2, $has_lz4, $has_snappy, $has_zstd, $has_brotli;
	global $opcache, $has_eacc, $has_xdebug, $xcache, $apcache, $eaccel, $xdebug, $xdbg_mode, $obd_set, $mbover;
	global $showOnlySystemInfo, $padLabel, $functions, $runOnlySelectedTests, $selectedTests, $totalOps;
	global $colorGreen, $colorReset, $colorRed;

	if (php_sapi_name() != 'cli') echo "<pre>";
	echo "\n$line\n|"
		. str_pad("PHP BENCHMARK SCRIPT", $padHeader, " ", STR_PAD_BOTH)
		. "|\n$line\n"
		. str_pad("Start", $padInfo) . " : " . date("Y-m-d H:i:s") . "\n"
		. str_pad("Server name", $padInfo) . " : " . gethostname() . "\n"
		. str_pad("Server system", $padInfo) . " : " . php_uname('s') . '/' . php_uname('r') . ' ' . php_uname('m') . "\n"
		. str_pad("Platform", $padInfo) . " : " . PHP_OS . "\n"
		. str_pad("System", $padInfo) . " : " . get_current_os() . "\n"
		. str_pad("CPU", $padInfo) . " :\n"
		. str_pad("model", $padInfo, ' ', STR_PAD_LEFT) . " : " . $cpuInfo['model'] . "\n"
		. str_pad("cores", $padInfo, ' ', STR_PAD_LEFT) . " : " . $cpuInfo['cores'] . "\n"
		. str_pad("available", $padInfo, ' ', STR_PAD_LEFT) . " : " . $cpuInfo['available'] . "\n"
		. str_pad("MHz", $padInfo, ' ', STR_PAD_LEFT) . " : " . $cpuInfo['mhz'] . ' MHz' . "\n"
		. str_pad("Benchmark version", $padInfo) . " : " . $scriptVersion . "\n"
		. str_pad("PHP version", $padInfo) . " : " . PHP_VERSION . "\n"
		. str_pad("PHP time limit", $padInfo) . " : " . $originTimeLimit . " sec\n"
		. str_pad("Setup time limit", $padInfo) . " : " . $maxTime . " sec\n"
		. str_pad("PHP memory limit", $padInfo) . " : " . $originMemoryLimit . "\n"
		. str_pad("Setup memory limit", $padInfo) . " : " . $memoryLimitMb . "\n"
		. str_pad("Crypt hash algo", $padInfo) . " : " . $cryptAlgoName . "\n"
		. str_pad("Loaded modules", $padInfo, ' ', STR_PAD_LEFT) . "\n"
		. str_pad("-useful->", $padInfo, ' ', STR_PAD_LEFT) . "\n"
		. str_pad("json", $padInfo, ' ', STR_PAD_LEFT) . " : $has_json\n"
		. str_pad("mbstring", $padInfo, ' ', STR_PAD_LEFT) . " : $has_mbstring\n"
		. str_pad("pcre", $padInfo, ' ', STR_PAD_LEFT) . " : $has_pcre" . ($has_pcre == "{$colorGreen}yes{$colorReset}" ? '; version: ' . PCRE_VERSION : '') . "\n"
		. str_pad("simplexml", $padInfo, ' ', STR_PAD_LEFT) . " : $has_simplexml; libxml version: ".LIBXML_DOTTED_VERSION."\n"
		. str_pad("dom", $padInfo, ' ', STR_PAD_LEFT) . " : $has_dom\n"
		. str_pad("intl", $padInfo, ' ', STR_PAD_LEFT) . " : $has_intl" . ($has_intl == "{$colorGreen}yes{$colorReset}" ? '; icu version: ' . INTL_ICU_VERSION : '')."\n"
		. str_pad("-optional->", $padInfo, ' ', STR_PAD_LEFT) . "\n"
		. str_pad("gd", $padInfo, ' ', STR_PAD_LEFT) . " : $has_gd: version: ". GD_VERSION."\n"
		. str_pad("imagick", $padInfo, ' ', STR_PAD_LEFT) . " : $has_imagick: version: ".IMG_VERSION."\n"
		. str_pad("-alternative->", $padInfo, ' ', STR_PAD_LEFT) . "\n"
		. str_pad("igbinary", $padInfo, ' ', STR_PAD_LEFT) . " : $has_igb\n"
		. str_pad("msgpack", $padInfo, ' ', STR_PAD_LEFT) . " : $has_msg\n"
		. str_pad("jsond", $padInfo, ' ', STR_PAD_LEFT) . " : $has_jsond\n"
		. str_pad("jsond as json >>", $padInfo, ' ', STR_PAD_LEFT) . " : $has_jsond_as_json\n"
		. str_pad("-compression->", $padInfo, ' ', STR_PAD_LEFT) . "\n"
		. str_pad("zlib", $padInfo, ' ', STR_PAD_LEFT) . " : $has_zlib, version: ".ZLIB_VERSION."\n"
		. str_pad("gzip", $padInfo, ' ', STR_PAD_LEFT) . " : $has_gzip\n"
		. str_pad("bz2", $padInfo, ' ', STR_PAD_LEFT) . " : $has_bz2\n"
		. str_pad("lz4", $padInfo, ' ', STR_PAD_LEFT) . " : $has_lz4\n"
		. str_pad("snappy", $padInfo, ' ', STR_PAD_LEFT) . " : $has_snappy\n"
		. str_pad("zstd", $padInfo, ' ', STR_PAD_LEFT) . " : $has_zstd, version:".LIBZSTD_VERSION_STRING."\n"
		. str_pad("brotli", $padInfo, ' ', STR_PAD_LEFT) . " : $has_brotli\n"
		. str_pad("uuid", $padInfo, ' ', STR_PAD_LEFT) . " : $has_uuid\n"
		. str_pad("-affecting->", $padInfo, ' ', STR_PAD_LEFT) . "\n"
		. str_pad("opcache", $padInfo, ' ', STR_PAD_LEFT) . " : $has_opcache; enabled: {$opcache}\n"
		. str_pad("xcache", $padInfo, ' ', STR_PAD_LEFT) . " : $has_xcache; enabled: {$xcache}\n"
		. str_pad("apc", $padInfo, ' ', STR_PAD_LEFT) . " : $has_apc; enabled: {$apcache}\n"
		. str_pad("eaccelerator", $padInfo, ' ', STR_PAD_LEFT) . " : $has_eacc; enabled: {$eaccel}\n"
		. str_pad("xdebug", $padInfo, ' ', STR_PAD_LEFT) . " : $has_xdebug, enabled: {$xdebug}, mode: '{$xdbg_mode}'\n"
		. str_pad("PHP parameters", $padInfo, ' ', STR_PAD_LEFT) . "\n"
		. str_pad("open_basedir", $padInfo, ' ', STR_PAD_LEFT) . " : is empty? ".(!$obd_set ? "{$colorGreen}yes{$colorReset}" : "{$colorRed}no{$colorReset}")."\n"
		. str_pad("mb.func_overload", $padInfo, ' ', STR_PAD_LEFT) . " : " . ($mbover ? "{$colorRed}{$mbover}{$colorReset}\n" : "{$colorGreen}{$mbover}{$colorReset}\n")
		. "$line\n" . $flushStr;
	flush();

	if (!$showOnlySystemInfo) {

		echo str_pad('TEST NAME', $padLabel) . " :"
			. str_pad('SECONDS', 9 + 4, ' ', STR_PAD_LEFT) . " |" . str_pad('OP/SEC', 9 + 4, ' ', STR_PAD_LEFT) . " |" . str_pad('OP/SEC/MHz', 9 + 7, ' ', STR_PAD_LEFT) . " |" . str_pad('MEMORY', 10, ' ', STR_PAD_LEFT) . "\n"
			. "$line\n" . $flushStr;
		flush();

		foreach ($availableFunctions as $user) {
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
			. str_pad("Total:", $padLabel) . " :";
		echo str_pad($resultSecFmt, 9, ' ', STR_PAD_LEFT) . " sec |" . str_pad($resultOps, 9, ' ', STR_PAD_LEFT) . "Op/s |" . str_pad($resultOpMhz, 9, ' ', STR_PAD_LEFT) . "Ops/MHz |" . "\n";
		echo str_pad("Current PHP memory usage:", $padLabel) . " :" . str_pad(convert(mymemory_usage()), 12, ' ', STR_PAD_LEFT) . "\n"
			// php-4 don't have peak_usage function
			. (function_exists('memory_get_peak_usage')
				? str_pad("Peak PHP memory usage:", $padLabel) . " :" . str_pad(convert(memory_get_peak_usage()), 12, ' ', STR_PAD_LEFT) . "\n"
				: ''
			);

		echo "$line\n";
		echo str_pad("End", $padLabel) . " : " . date("Y-m-d H:i:s") . "\n";

	} // show only system info?
	else {
		echo str_pad("End", $padInfo) . " : " . date("Y-m-d H:i:s") . "\n";
	}

	if (php_sapi_name() != 'cli')
		echo "</pre>\n";
	flush();
}

function print_results_machine()
{
	$total = 0;

	global $availableFunctions;
	global $scriptVersion, $showOnlySystemInfo, $rawValues4json;
	global $functions, $runOnlySelectedTests, $selectedTests, $totalOps;

	echo ""
		. "PHP_BENCHMARK_SCRIPT: $scriptVersion\n"
		. "START: " . date("Y-m-d H:i:s") . "\n"
		. "SERVER_name: " . gethostname() . "\n"
		. "SERVER_sys: " . php_uname('s') . '/' . php_uname('r') . ' ' . php_uname('m') . "\n"
		. "SYSTEM: " . get_current_os() . "\n"
		. "PHP_VERSION: " . PHP_VERSION . "\n"
		;
	flush();

	if (!$showOnlySystemInfo) {

		echo "TEST_NAME: SECONDS, OP/SEC, OP/SEC/MHz, MEMORY\n";
		flush();

		$rawValues4json = true;

		foreach ($availableFunctions as $user) {
			if (strpos($user, 'test_') === 0) {
				$testName = str_replace('test_', '', $user);
				if ($runOnlySelectedTests) {
					if (!in_array($testName, $selectedTests)) {
						continue;
					}
				}
				echo $testName . ": ";
				list($resultSec, $resultSecFmt, $resultOps, $resultOpMhz, $memory) = $user();
				$total += $resultSec;
				echo $resultSecFmt . ", ". $resultOps . ", " . $resultOpMhz . ", " . $memory . "\n";
				flush();
			}
		}

		list($resultSec, $resultSecFmt, $resultOps, $resultOpMhz, $memory) = format_result_test($total, $totalOps, 0);

		echo "TOTAL: " . $resultSecFmt . ", " . $resultOps . ", " . $resultOpMhz . "\n";
		flush();

	}

	echo "END: " . date("Y-m-d H:i:s") . "\n";
}

function print_results_json()
{
	$total = 0;

	global $availableFunctions;
	global $scriptVersion, $showOnlySystemInfo, $rawValues4json, $messagesCnt;
	global $functions, $runOnlySelectedTests, $selectedTests, $totalOps;

	echo ""
		. "\"php_benchmark_script\": \"$scriptVersion\",\n"
		. "\"start\": \"" . date("Y-m-d H:i:s") . "\",\n"
		. "\"server_name\": \"" . gethostname() . "\",\n"
		. "\"server_sys\": \"" . php_uname('s') . '/' . php_uname('r') . ' ' . php_uname('m') . "\",\n"
		. "\"system\": \"" . get_current_os() . "\",\n"
		. "\"php_version\": \"" . PHP_VERSION . "\",\n"
		;
	flush();

	if (!$showOnlySystemInfo) {

		echo "\"results\": {\n";
		echo "  \"columns\": [ \"test_name\", \"seconds\", \"op\/sec\", \"op\/sec\/MHz\", \"memory\" ],\n";
		flush();

		$rawValues4json = true;

		echo "  \"rows\": [\n";
		foreach ($availableFunctions as $user) {
			if (strpos($user, 'test_') === 0) {
				$testName = str_replace('test_', '', $user);
				if ($runOnlySelectedTests) {
					if (!in_array($testName, $selectedTests)) {
						continue;
					}
				}
				echo "    [ \"".$testName . "\", ";
				list($resultSec, $resultSecFmt, $resultOps, $resultOpMhz, $memory) = $user();
				$total += $resultSec;
				echo $resultSecFmt . ", ". $resultOps . ", " . $resultOpMhz . ", " . $memory . " ],\n";
				flush();
			}
		}
		echo "    null\n  ]\n";
		echo "},\n";
		flush();

		list($resultSec, $resultSecFmt, $resultOps, $resultOpMhz, $memory) = format_result_test($total, $totalOps, 0);

		echo "\"total\": { \"seconds\": ";
		echo $resultSecFmt . ", \"op\/sec\":" . $resultOps . ", \"op\/sec\/MHz\":" . $resultOpMhz . " },\n";
	}
	print("\"messages_count\": {$messagesCnt},\n");
	print("\"end\":\"".date("Y-m-d H:i:s")."\"\n}" . PHP_EOL);
	flush();
}

if ($printJson) {
	print_results_json();
} else if ($printMachine) {
	print_results_machine();
} else {
	print_results_common();
}
