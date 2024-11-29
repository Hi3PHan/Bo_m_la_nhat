<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_examsurveys
 *
 * @copyright   Copyright (C) 2023 Academy of Cryptography Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Hi3PHan\Component\QLSV\Administrator\Extension\QlsvComponent;

return new class implements ServiceProviderInterface
{
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   __BUMP_VERSION__
     */
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new CategoryFactory('\\Hi3PHan\\Component\\QLSV'));
        $container->registerServiceProvider(new MVCFactory('\\Hi3PHan\\Component\\QLSV'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Hi3PHan\\Component\\QLSV'));

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new QlsvComponent($container->get(ComponentDispatcherFactoryInterface::class));

                $component->setRegistry($container->get(Registry::class));

                return $component;
            }
        );
    }
};
