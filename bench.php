<?php
/*
##########################################################################
#                      PHP Benchmark Performance Script                  #
#                         © 2010 Code24 BV                               #
#                         © 2015 Rusoft                                  #
#                                                                        #
#  Author      : Alessandro Torrisi                                      #
#  Company     : Code24 BV, The Netherlands                              #
#  Date        : July 31, 2010                                           #
#  version     : 1.0                                                     #
#  License     : Creative Commons CC-BY license                          #
#  Website     : http://www.php-benchmark-script.com                     #
#                                                                        #
##########################################################################
*/

function get_microtime()
{
    $time = microtime(true);
    if (is_string($time)) {
        list($f, $i) = explode(" ", $time);
        $time = intval($i) + floatval($f);
    }
    return $time;
}

	function test_Math($count = 1400000) {
		$time_start = get_microtime();
		$mathFunctions = array("abs", "acos", "asin", "atan", "decbin", "dechex", "decoct", "floor", "exp", "log1p", "sin", "tan", "pi", "is_finite", "is_nan", "sqrt", "rad2deg");
		foreach ($mathFunctions as $key => $function) {
			if (!function_exists($function)) unset($mathFunctions[$key]);
		}
		for ($i=0; $i < $count; $i++) {
			foreach ($mathFunctions as $function) {
				$r = call_user_func_array($function, array($i));
			}
		}
		return number_format(get_microtime() - $time_start, 3);
	}

	function test_StringManipulation($count = 1300000) {
		$time_start = get_microtime();
		$stringFunctions = array("addslashes", "chunk_split", "metaphone", "strip_tags", "crc32", "md5", "sha1", "strtoupper", "strtolower", "strrev", "strlen", "str_rot13", "soundex", "ord", "wordwrap");
		foreach ($stringFunctions as $key => $function) {
			if (!function_exists($function)) unset($stringFunctions[$key]);
		}
		$string = "the quick <b>brown</b> fox jumps <i>over</i> the lazy dog and eat lorem ipsum volar morgulis";
		for ($i=0; $i < $count; $i++) {
			foreach ($stringFunctions as $function) {
				$r = call_user_func_array($function, array($string));
			}
		}
		return number_format(get_microtime() - $time_start, 3);
	}


	function test_Loops($count = 190000000) {
		$time_start = get_microtime();
		for($i = 0; $i < $count; ++$i);
		$i = 0; while($i++ < $count);
		return number_format(get_microtime() - $time_start, 3);
	}

	
	function test_IfElse($count = 90000000) {
		$time_start = get_microtime();
		for ($i=0; $i < $count; $i++) {
			if ($i == -1) {
			} elseif ($i == -2) {
			} else if ($i == -3) {
			} else {
            }
		}
		return number_format(get_microtime() - $time_start, 3);
	}	
	
	
	$total = 0;
	$functions = get_defined_functions();
	$line = str_pad("-",38,"-");
	echo "<pre>\n$line\n|".str_pad("PHP BENCHMARK SCRIPT",36," ",STR_PAD_BOTH)."|\n$line\nStart : ".date("Y-m-d H:i:s")."\nServer : ".php_uname()."\nPHP version : ".PHP_VERSION."\nPlatform : ".PHP_OS. "\n$line\n";
	foreach ($functions['user'] as $user) {
		if (preg_match('/^test_/', $user)) {
			$result = $user();
			$total += $result;
            echo str_pad($user, 25) . " : " . $result ." sec.\n";
        }
	}
	echo str_pad("-", 38, "-") . "\n" . str_pad("Total time:", 25) . " : " . $total ." sec.\n</pre>\n";
	
?>
