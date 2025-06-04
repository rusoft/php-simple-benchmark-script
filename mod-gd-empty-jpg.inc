<?php
/**
 * module gd+jpg test functions
 * Php 5.2+
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

function test_40_03_gd_save_fill_empty_jpg()
{
	global $stringTest, $emptyResult, $testsLoopLimits, $totalOps;

	if (!function_exists('imagejpeg')) {
		return $emptyResult;
	}
	if (!function_exists('imagecreatetruecolor')) {
		return $emptyResult;
	}
	if (!function_exists('imagefilledrectangle')) {
		return $emptyResult;
	}

	// Create a new image instance
	$im = imagecreatetruecolor(1, 1);

	// Make the background white
	imagefilledrectangle($im, 0, 0, 1, 1, 0xFFFFFF);

	$count = $testsLoopLimits['40_03_gd_save_fill_empty_jpg'];
	$time_start = get_microtime();
	$nillpath='/dev/null';
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		$nillpath='\\.\NUL';
	}
	for ($i = 0; $i < $count; $i++) {
		imagejpeg($im, $nillpath);
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}
