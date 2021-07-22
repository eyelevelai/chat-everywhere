<?php

/**
 * @link       https://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/includes
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Site_Options {

	/**
	 * @since    0.0.1
	 */
	public static function getTypes() {
		return array(
			'Site Wide',
			'Specific Posts',
			'Specific Pages',
			'Specific Categories',
			'Specific Post Types',
			'Specific Tags'
		);
	}

}
