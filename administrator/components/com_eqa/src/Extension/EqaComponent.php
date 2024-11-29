<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_eqa
 *
 * @copyright   Copyright (C) 2023 Academy of Crptography Techniques. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Kma\Component\Eqa\Administrator\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Categories\CategoryServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Kma\Component\Eqa\Administrator\Service\HTML\AdministratorService;
use Psr\Container\ContainerInterface;

/**
 * Component class for com_eqa
 *
 * @since  __BUMP_VERSION__
 */
class EqaComponent extends MVCComponent implements BootableExtensionInterface, CategoryServiceInterface
{
	use CategoryServiceTrait;
	use HTMLRegistryAwareTrait;

	/**
	 * Booting the extension. This is the function to set up the environment of the extension like
	 * registering new class loaders, etc.
	 *
	 * If required, some initial set up can be done from services of the container, eg.
	 * registering HTML services.
	 *
	 * @param   ContainerInterface  $container  The container
	 *
	 * @return  void
	 *
	 * @since   __BUMP_VERSION__
	 */
	public function boot(ContainerInterface $container)
	{
		$this->getRegistry()->register('eqaadministrator', new AdministratorService);
	}
}