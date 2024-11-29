<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_CRUDs
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\CRUDs\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Language\Multilanguage;

/**
 * CRUDs Component Route Helper
 *
 * @static
 * @package     Joomla.Site
 * @subpackage  com_CRUDs
 * @since       1.5
 */
abstract class Route
{
	/**
	 * Get the URL route for a CRUDs from a CRUD ID, CRUDs category ID and language
	 *
	 * @param   integer  $id        The id of the CRUDs
	 * @param   integer  $catid     The id of the CRUDs's category
	 * @param   mixed    $language  The id of the language being used.
	 *
	 * @return  string  The link to the CRUDs
	 *
	 * @since   1.5
	 */
	public static function getCRUDsRoute($id, $catid, $language = 0)
	{
		// Create the link
		$link = 'index.php?option=com_CRUDs&view=CRUD&id=' . $id;

		if ($catid > 1)
		{
			$link .= '&catid=' . $catid;
		}

		if ($language && $language !== '*' && Multilanguage::isEnabled())
		{
			$link .= '&lang=' . $language;
		}

		return $link;
	}

	/**
	 * Get the URL route for a CRUDs category from a CRUDs category ID and language
	 *
	 * @param   mixed  $catid     The id of the CRUDs's category either an integer id or an instance of CategoryNode
	 * @param   mixed  $language  The id of the language being used.
	 *
	 * @return  string  The link to the CRUDs
	 *
	 * @since   1.5
	 */
	public static function getCategoryRoute($catid, $language = 0)
	{
		if ($catid instanceof CategoryNode)
		{
			$id = $catid->id;
		}
		else
		{
			$id = (int) $catid;
		}

		if ($id < 1)
		{
			$link = '';
		}
		else
		{
			// Create the link
			$link = 'index.php?option=com_CRUDs&view=category&id=' . $id;

			if ($language && $language !== '*' && Multilanguage::isEnabled())
			{
				$link .= '&lang=' . $language;
			}
		}

		return $link;
	}
}
