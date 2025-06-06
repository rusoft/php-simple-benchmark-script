<?php
/**
 * php safe options - php8 only functions or syntax
 * Php 8.0+
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

function test_37_01_php8_Str_Contains()
{
	global $testsLoopLimits, $totalOps;

	$count = $testsLoopLimits['37_01_php8_str_contains'];
	$time_start = get_microtime();
  $s = "Foobar";
  $n = "Foo";
  $found=0;
		for ($i = 0; $i < $count; ++$i) {
			$f = str_contains($s, $n);
			if ($f) $found++;
			// Work-around for opCache 8.0+ code elimination, maybe
			//$s .= ($i % 111) ? PHP_EOL : '!';
		}
	$totalOps += $count;
	$memory = mymemory_usage();
	unset($s);
	return format_result_test(get_microtime() - $time_start, $count , $memory);
}

function test_37_02_php8_Str_Contains_emulate()
{
	global $testsLoopLimits, $totalOps;

	$count = $testsLoopLimits['37_02_php8_str_contains_emulate'];
	$time_start = get_microtime();
  $s = "Foobar";
  $n = "Foo";
  $found=0;
		for ($i = 0; $i < $count; ++$i) {
			$f = strpos($s, $n);
			if ($f !== false) $found++;
			// Work-around for opCache 8.0+ code elimination, maybe
			//$s .= ($i % 111) ? PHP_EOL : '!';
		}
	$totalOps += $count;
	$memory = mymemory_usage();
	unset($s);
	return format_result_test(get_microtime() - $time_start, $count , $memory);
}