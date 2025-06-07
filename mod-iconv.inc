<?php
/**
 * module iconv test functions
 * Php 4.0
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

function test_43_iconv_translit()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps;

	if (!function_exists('iconv')) {
		return $emptyResult;
	}

	$count = $testsLoopLimits['43_iconv_translit'];
	$time_start = get_microtime();
	$text = "This is the Euro symbol 'тм";
	$d=0;
	setlocale(LC_CTYPE, 'POSIX');
	for ($i = 0; $i < $count; $i++) {
		if (iconv("utf8", "ISO-8859-1//TRANSLIT", $text)) $d++;
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}
