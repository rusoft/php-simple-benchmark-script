<?php

function test_18_Loop_ExceptionTryCatch($count = 4000000)
{
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		try {
			throw new Exception($i);
		} catch (Exception $e) {
		}
	}
	return format_result_test(get_microtime() - $time_start, $count, memory_get_usage(true));
}
