<?php

/**
 * Special test only for php 5+
 * php 4.x cant compile try construction.
 */

/* ------------------------ Additional data ------------------------ */

class PublicProperties
{
	public $number = 0;
}

class GetterSetter
{
	private $number = 0;

	public function getNumber()
	{
		return $this->number;
	}

	public function setNumber($new)
	{
		$this->number = $new;
		return $this;
	}
}

class MagicMethods
{
	private $number = 0;

	public function __get($name)
	{
		if ($name === 'number') {
			return $this->number;
		}
		return null;
	}

	public function __set($name, $new)
	{
		if ($name === 'number') {
			$this->number = $new;
		}
	}
}

/* ------------------------ Tests ------------------------ */

function test_21_0_Loop_Exception_None()
{
	global $testsLoopLimits, $totalOps;

	$count = $testsLoopLimits['21_loop_except'];
	$time_start = get_microtime();
	$a = 0;
	for ($i = 0; $i < $count; $i++) {
		$a += $i;
		if ($i % 10000 == 1) $a = 0;
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_21_1_Loop_Exception_Try()
{
	global $testsLoopLimits, $totalOps;

	$count = $testsLoopLimits['21_loop_except'];
	$time_start = get_microtime();
	$a = 0;
	for ($i = 0; $i < $count; $i++) {
		try {
			$a += $i;
			if ($i % 10000 == 1) $a = 0;
		} catch (Exception $e) {
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_21_2_Loop_Exception_Catch()
{
	global $testsLoopLimits, $totalOps;

	$count = $testsLoopLimits['21_loop_except'];
	$time_start = get_microtime();
	$a = 0;
	for ($i = 0; $i < $count; $i++) {
		try {
			$a += $i;
			if ($i % 10000 == 1) $a = 0;
			throw new Exception($i);
		} catch (Exception $e) {
		}
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_26_1_Class_Public_Properties()
{
	global $testsLoopLimits, $totalOps;

	$c = new PublicProperties();
	$r = 0;

	$count = $testsLoopLimits['26_1_public'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		$r = $c->number;
		$c->number = $r + $i;
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_26_2_Class_Getter_Setter()
{
	global $testsLoopLimits, $totalOps;

	$c = new GetterSetter();
	$r = 0;

	$count = $testsLoopLimits['26_2_getset'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		$r = $c->getNumber();
		$c->setNumber($r + $i);
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_26_3_Class_Magic_Methods()
{
	global $testsLoopLimits, $totalOps;

	$c = new MagicMethods();
	$r = 0;

	$count = $testsLoopLimits['26_3_magic'];
	$time_start = get_microtime();
	for ($i = 0; $i < $count; $i++) {
		$r = $c->number;
		$c->number = $r + $i;
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

// ------------------------- xml -------------------------

function test_27_SimpleXml()
{
	global $testsLoopLimits, $totalOps, $emptyResult;

	$count = $testsLoopLimits['27_simplexml'];
	$time_start = get_microtime();

	if (!class_exists('SimpleXMLElement', false)) {
		return $emptyResult;
	}

	$file = 'test.xml';
	if (!@is_readable($file)) {
		return $emptyResult;
	}

	$xmlStr = file_get_contents($file);

	$a = 0;
	for ($i = 0; $i < $count; $i++) {
		$rss = new SimpleXMLElement($xmlStr);
		if ($rss->channel->title == 'PECL: Latest releases') $a++;
		if ($rss->item[1]->title == 'rdkafka 5.0.1') $a++;
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

function test_28_DomXml()
{
	global $testsLoopLimits, $totalOps, $emptyResult;

	$count = $testsLoopLimits['28_domxml'];
	$time_start = get_microtime();

	if (!class_exists('DOMDocument', false)) {
		return $emptyResult;
	}

	$file = 'test.xml';
	if (!@is_readable($file)) {
		return $emptyResult;
	}

	$xmlStr = file_get_contents($file);

	$a = 0;
	for ($i = 0; $i < $count; $i++) {
		$rss = new DOMDocument('1.0', 'utf-8');
		$rss->loadXML($xmlStr);

		$channels = $rss->getElementsByTagName('channel');
		/** @var \DOMNodeList $channels */
		$channel = $channels->item(0);
		/** @var \DOMElement $channel */
		$chTitle = $channel->getElementsByTagName('title');
		/** @var \DOMNodeList $chTitle */
		$chTitle = $chTitle->item(0);
		if ($chTitle->nodeValue == 'PECL: Latest releases') $a++;

		$items = $rss->getElementsByTagName('item');
		/** @var \DOMNodeList $items */
		$item = $items->item(1);
		/** @var \DOMElement $item */
		$iTitle = $item->getElementsByTagName('title');
		/** @var \DOMNodeList $iTitle */
		$iTitle = $iTitle->item(0);
		if ($iTitle->nodeValue == 'rdkafka 5.0.1') $a++;
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

// ------------------- DateTime classes ---------------------

function test_29_DateTime()
{
	global $testsLoopLimits, $totalOps, $emptyResult;

	if (!class_exists('DateTime', false)) {
		return $emptyResult;
	}
	if (!class_exists('DateInterval', false)) {
		return $emptyResult;
	}

	$count = $testsLoopLimits['29_datetime'];
	$time_start = get_microtime();

	$now = new DateTime();
	$day = new DateInterval("P1D");
	$year = new DateInterval("P1Y");

	$a = 0;
	for ($i = 0; $i < $count; $i++) {
		$unix = DateTime::createFromFormat("U", $i);
		$unix->sub($year);
		$unix->add($day);
		$diff = $now->diff($unix);
		$a += $diff->y % 2;
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}



/**
 * @since 5.3.0
 */
function test_31_Intl_Message_Format()
{
	global $testsLoopLimits, $totalOps, $emptyResult;

	if (!class_exists('MessageFormatter', false)) {
		return $emptyResult;
	}
	if (!function_exists('msgfmt_create')) {
		return $emptyResult;
	}

	$count = $testsLoopLimits['31_intl_message_format'];
	$time_start = get_microtime();

	$fmt = new MessageFormatter("en_US", "{0,number,integer} monkeys on {1,number,integer} trees make {2,number} monkeys per tree");
	$fmt2 = new MessageFormatter('en', 'Found {0, plural, =0 {no result} =1 {one result} other {# results}}');

	for ($i = 0; $i < $count; $i++) {
		$num = $i / 123.;
		$fmt->format(array($i, 123, $num));
		$fmt2->format(array($i));
	}
	$totalOps += $count;
	return format_result_test(get_microtime() - $time_start, $count, mymemory_usage());
}

