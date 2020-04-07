<?php
/**
 *
 * Personal counter. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

/**
 * Some characters you may want to copy&paste: ’ » “ ” …
 */
$lang = array_merge($lang, [
	'PC_INFO'				=> 'Personal counter',
	'PC_INFO_UCP'			=> 'Start counting from',
	'PC_INFO_UCP_EXPLAIN'	=> 'Date format <samp>dd-mm-yyyy</samp> with hyphens<br>You may select a date with the built-in date-picker.<br>Use reset button to disable the counter.',
	'PC_SINCE'				=> 'Since',
	'PC_DATE_EXPLAIN'		=> 'You may select a date with the built-in date-picker',
	'PC_ELAPSED'			=> 'Elapsed time',
	'PC_AT'					=> 'at',

	'PC_YEAR'	=> [
		1	=> '<strong>%d</strong> Year',
		2	=> '<strong>%d</strong> Years',
	],
	'PC_MONTH'	=> [
		1	=> '<strong>%d</strong> Month',
		2	=> '<strong>%d</strong> Months',
	],
	'PC_DAY'	=> [
		1	=> '<strong>%d</strong> Day',
		2	=> '<strong>%d</strong> Days',
	]
]);
