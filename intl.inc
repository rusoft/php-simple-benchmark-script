<?php
/**
 * php safe options - only tests for module intl
 * Php 5.3+
 */

/** ---------------------------------- Tests functions -------------------------------------------- */

// ------------------------- INTL tests -----------------------

/**
 * @since 5.3.0
 */
function test_30_Intl_Number_Format()
{
	global $testsLoopLimits, $totalOps, $emptyResult;

	if (!class_exists('NumberFormatter', false)) {
		return $emptyResult;
	}
	if (!function_exists('numfmt_create')) {
		return $emptyResult;
	}

	$count = $testsLoopLimits['30_intl_number_format'];
	$time_start = get_microtime();

	$fmt = new NumberFormatter( 'ru_RU', NumberFormatter::DECIMAL );
	$fmtD = new NumberFormatter( 'ru_RU', NumberFormatter::DURATION );
	$fmtC = new NumberFormatter( 'ru_RU', NumberFormatter::CURRENCY );
	$fmtS = new NumberFormatter( 'en', NumberFormatter::SPELLOUT );

	for ($i = 0; $i < $count; $i++) {
		$num = $i / 100.;
		$fmt->format($num);

		$fmtC->formatCurrency($num, 'EUR');
		$fmtC->formatCurrency($num, 'RUR');

		$fmtD->format($num);
		$fmtD->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%in-numerals");
		$fmtD->format($num);
		$fmtD->setTextAttribute(NumberFormatter::DEFAULT_RULESET, "%with-words");
		$fmtD->format($num);

		$fmtS->format($num);
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

/**
 * @since: 5.5.0
 */
function test_32_Intl_Calendar()
{
	global $testsLoopLimits, $totalOps, $emptyResult;

	if (!class_exists('IntlCalendar', false)) {
		return $emptyResult;
	}
	if (!class_exists('IntlTimeZone', false)) {
		return $emptyResult;
	}

	$count = $testsLoopLimits['32_intl_calendar'];
	$time_start = get_microtime();

	$cal = IntlCalendar::createInstance(IntlTimeZone::getGMT());

	$a = 0;
	for ($i = 0; $i < $count; $i++) {
		$num = $i / 100.;
		$cal->clear();
		$cal->setTime($num);

		if ($cal->inDaylightTime()) $a++;
		if ($cal->isWeekend()) $a--;
		if ($cal->getMinimalDaysInFirstWeek()) $a++;

		$cal->add(IntlCalendar::FIELD_MONTH, 1);
		$cal->add(IntlCalendar::FIELD_DAY_OF_MONTH, 1);
		if ($cal->inDaylightTime()) $a--;
		if ($cal->isWeekend()) $a++;
		if ($cal->getMinimalDaysInFirstWeek()) $a--;
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}