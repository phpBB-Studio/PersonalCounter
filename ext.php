<?php
/**
 *
 * Personal counter. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2020, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\pc;

/**
 * Personal counter Extension base
 */
class ext extends \phpbb\extension\base
{
	/**
	 * {@inheritdoc
	 */
	public function is_enableable()
	{
		if (!(phpbb_version_compare(PHPBB_VERSION, '3.3.0', '>=') && phpbb_version_compare(PHPBB_VERSION, '4.0.0@dev', '<')))
		{
			$language= $this->container->get('language');
			$language->add_lang('pc_ext', 'phpbbstudio/pc');

			return $language->lang('PC_PHPBB_VERSION', '3.3.0', '4.0.0@dev');
		}

		return true;
	}
}
