<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_CRUDs
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\CRUDs\Site\View\CRUDs;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
/**
 * HTML CRUDs View class for the CRUD component
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The page parameters
     *
     * @var    \Joomla\Registry\Registry|null
     * @since  1.0.0
     */
    protected $pagination;

    /**
     * The page parameters
     *
     * @var    \Joomla\Registry\Registry|null
     * @since  4.0.0
     */
    protected $params = null;

    /**
     * The item model state
     *
     * @var    \Joomla\Registry\Registry
     * @since  1.0.0
     */
    protected $state;

    /**
     * The item object details
     *
     * @var    \JObject
     * @since  1.0.0
     */
    protected $item;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     */

    protected $reports;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     */
    public function display($tpl = null)
    {
        // Get data from the model.
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->pagination = $this->get('Pagination');
        // Flag indicates to not add limitstart=0 to URL
        $this->pagination->hideEmptyLimitstart = true;

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        return parent::display($tpl);

    }
}
