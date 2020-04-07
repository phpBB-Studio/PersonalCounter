<?php
/**
 *
 * Personal counter. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\pc\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Personal counter Event listener.
 */
class listener implements EventSubscriberInterface
{
	/* @var \phpbb\auth\auth */
	protected $auth;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\language\language */
	protected $language;

	/* @var \phpbb\request\request */
	protected $request;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/**
	 * Constructor
	 *
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user
	)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->language		= $language;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
	}

	/**
	 * {@inheritdoc
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup_after'						=> 'pc_load_language_on_setup',
			'core.permissions'							=> 'pc_permissions',
			'core.ucp_profile_modify_profile_info'		=> 'pc_ucp_profile_modify_profile_info',
			'core.ucp_profile_info_modify_sql_ary'		=> 'pc_ucp_submit',
			'core.viewtopic_post_rowset_data'			=> 'pc_viewtopic_post_rowset_data',
			'core.viewtopic_cache_user_data'			=> 'pc_viewtopic_cache_user_data',
			'core.viewtopic_modify_post_row'			=> 'pc_viewtopic_modify_post_row',
		];
	}

	/**
	 * Load common language files after user setup
	 *
	 */
	public function pc_load_language_on_setup()
	{
		$this->language->add_lang('common', 'phpbbstudio/pc');
	}

	/**
	 * Adds PC permissions to my custom category
	 *
	 * @event core.permissions
	 * @param \phpbb\event\data	$event		The event object
	 * @return void
	 */
	public function pc_permissions($event)
	{
		$categories = $event['categories'];
		$permissions = $event['permissions'];

		if (empty($categories['phpbb_studio']))
		{
			/* Set up a custom cat. tab */
			$categories['phpbb_studio'] = 'ACL_CAT_PHPBB_STUDIO';

			$event['categories'] = $categories;
		}

		$perms = [
			'u_phpbbstudio_pc',
		];

		foreach ($perms as $permission)
		{
			$permissions[$permission] = ['lang' => 'ACL_' . utf8_strtoupper($permission), 'cat' => 'phpbb_studio'];
		}

		$event['permissions'] = $permissions;
	}

	/**
	 * UCP interface
	 *
	 * @event core.ucp_submit
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 */
	public function pc_ucp_profile_modify_profile_info(\phpbb\event\data $event)
	{
		/* Request the date */
		$pc_date = $this->request->variable('pc_date', '', true);

		/* Here we are storing the timestamp exactly the as per the time of submission */
		$event['data'] = array_merge($event['data'], [
			'user_pc'	=> (!empty($pc_date)) ? $this->user->get_timestamp_from_format('d-m-Y', $pc_date, new \DateTimeZone($this->config['board_timezone'])) : 0,
		]);

		$unix_timestamp = $this->user->data['user_pc'];

		list($diff_year, $diff_month, $diff_day, $pc_start_date, $pc_start_time) = $this->since_date($unix_timestamp);

		list($pc_year, $pc_month, $pc_day) = $this->elapsed_time($diff_year, $diff_month, $diff_day);

		$this->template->assign_vars([
			'PC_START_DATE'		=> $pc_start_date,
			'PC_START_TIME'		=> $pc_start_time,

			'L_PC_YEAR'			=> $pc_year,
			'L_PC_MONTH'		=> $pc_month,
			'L_PC_DAY'			=> $pc_day,

			'S_IS_PC'			=> (bool) $unix_timestamp,

			'PC_DATE'			=> $unix_timestamp ? $pc_start_date : false,
		]);
	}

	/**
	 * Store the timestamp in the users table
	 *
	 * @event core.ucp_submit
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 */
	public function pc_ucp_submit(\phpbb\event\data $event)
	{
		$sql_ary = $event['sql_ary'];

		$sql_ary['user_pc'] = $event['data']['user_pc'];

		$event['sql_ary'] = $sql_ary;
	}

	/**
	 * Set the user date after being retrieved from the database.
	 *
	 * @event  core.viewtopic_post_rowset_data
	 * @param  \phpbb\event\data	$event		The event object
	 * @return void
	 */
	public function pc_viewtopic_post_rowset_data(\phpbb\event\data $event)
	{
		$event['rowset_data'] = array_merge($event['rowset_data'], [
			'user_pc' => $event['row']['user_pc'],
		]);
	}

	/**
	 * Cache the user date when displaying a post.
	 *
	 * @event core.viewtopic_cache_user_data
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 */
	public function pc_viewtopic_cache_user_data(\phpbb\event\data $event)
	{
		$event['user_cache_data'] = array_merge($event['user_cache_data'], [
			'user_pc' => $event['row']['user_pc'],
		]);
	}

	/**
	 * Display the personal counter when displaying a post.
	 *
	 * @event core.viewtopic_modify_post_row
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 */
	public function pc_viewtopic_modify_post_row(\phpbb\event\data $event)
	{
		if ($this->auth->acl_get('u_phpbbstudio_pc'))
		{
			$unix_timestamp = $event['user_cache'][$event['poster_id']]['user_pc'];

			list($diff_year, $diff_month, $diff_day, $pc_start_date, $pc_start_time) = $this->since_date($unix_timestamp);

			list($pc_year, $pc_month, $pc_day) = $this->elapsed_time($diff_year, $diff_month, $diff_day);

			$event['post_row'] = array_merge($event['post_row'], [
				'PC_START_DATE'		=> $pc_start_date,
				'PC_START_TIME'		=> $pc_start_time,
				'L_PC_YEAR'			=> $diff_year ? $pc_year : '',
				'L_PC_MONTH'		=> ($diff_year || $diff_month) ? $pc_month : '',
				'L_PC_DAY'			=> $pc_day,

				'S_IS_PC'			=> (bool) $unix_timestamp,
			]);

			$this->template->assign_var('S_IS_AUTH', true);
		}
	}

	/**
	 * Calculates the time spent in years, months and days taking into account leap years.
	 *
	 * @param  int	$unix_timestamp		The date as unix timestamp
	 * @return array					[$diff_year, $diff_month, $diff_day, $pc_start_date, $pc_start_time]
	 * @access protected
	 */
	protected function since_date(int $unix_timestamp) : array
	{
		$days_of_month = [
			[0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
			[0, 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
		];

		$start_date = gmdate('Y-m-d', $unix_timestamp);
		$today_date = gmdate('Y-m-d', time());

		list($year1, $month1, $day1) = explode('-', $start_date);
		list($year2, $month2, $day2) = explode('-', $today_date);

		$diff_year = $year2 - $year1;
		$diff_month = $month2 - $month1;
		$diff_day = $day2 - $day1;

		/* Leap years have got to be calculated */
		$is_leap = (($year2 % 4) == 0 && ($year2 % 100) != 0 || ($year2 % 400) == 0) ? 1 : 0;

		/**
		 * Do obvious corrections (days before months!)
		 *
		 * This is a loop in case the previous month is
		 * February, and days < -28.
		 */
		$prev_month_days = $days_of_month[$is_leap][$month2 - 1];

		while ($diff_day < 0)
		{
			/* Borrow from the previous month */
			if ($prev_month_days == 0)
			{
				$prev_month_days = 31;
			}

			--$diff_month;

			$diff_day += $prev_month_days;
		}

		if ($diff_month < 0)
		{
			/* Borrow from the previous year */
			--$diff_year;

			$diff_month += 12;
		}

		/* Converts the user PC date into an human readable format */
		$pc_start_date = $this->user->format_date($unix_timestamp, 'd-m-Y');
		$pc_start_time = $this->user->format_date($unix_timestamp, '00:00');//	'H:i'

		return [$diff_year, $diff_month, $diff_day, $pc_start_date, $pc_start_time];
	}


	/**
	 * Returns the language vars for years, months and days.
	 *
	 * @param  int   $diff_year     The diff of years since
	 * @param  int   $diff_month    The diff of months since
	 * @param  int   $diff_day      The diff of days since
	 * @return array                [$pc_year, $pc_month, $pc_day]
	 * @access protected
	 */
	protected function elapsed_time(int $diff_year, int $diff_month, int $diff_day) : array
	{
		/* Plural Rules and cast to INT */
		$pc_year = $this->language->lang('PC_YEAR', $diff_year);
		$pc_month = $this->language->lang('PC_MONTH', $diff_month);
		$pc_day = $this->language->lang('PC_DAY', $diff_day);

		return [$pc_year, $pc_month, $pc_day];
	}
}
