<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_CRUDs
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\CRUDs\Administrator\View\CRUDs;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\CRUDs\Administrator\Helper\CRUDHelper;
use Joomla\Component\CRUDs\Administrator\Model\CRUDsModel;

/**
 * View class for a list of CRUDs.
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of items
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected $items = [];

	/**
	 * The pagination object
	 *
	 * @var    Pagination
	 * @since  1.0.0
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var    CMSObject
	 * @since  1.0.0
	 */
	protected $state;

	/**
	 * Form object for search filters
	 *
	 * @var    Form
	 * @since  1.0.0
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	public $activeFilters = [];

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 * @throws  Exception
	 */
	public function display($tpl = null)
	{
//		/** @var CRUDsModel $model */
//		$model               = $this->getModel();
//		$this->items         = $model->getItems();
//		$this->pagination    = $model->getPagination();
//		$this->filterForm    = $model->getFilterForm();
//		$this->activeFilters = $model->getActiveFilters();
//		$this->state         = $model->getState();
//		$errors              = $this->getErrors();
//
//		if (count($errors))
//		{
//			throw new GenericDataException(implode("\n", $errors), 500);
//		}
//
//		// Preprocess the list of items to find ordering divisions.
//		// TODO: Complete the ordering stuff with nested sets
//		foreach ($this->items as &$item)
//		{
//			$item->order_up = true;
//			$item->order_dn = true;
//		}
//
//		// We don't need toolbar in the modal window.
//		if ($this->getLayout() !== 'modal')
//		{
//			$this->addToolbar();
//		}
//		else
//		{
//			// In article associations modal we need to remove language filter if forcing a language.
//			// We also need to change the category filter to show show categories with All or the forced language.
//			if ($forcedLanguage = Factory::getApplication()->input->get('forcedLanguage', ''))
//			{
//				// If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
//				$languageXml = new \SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage
//					. '" />');
//				$this->filterForm->setField($languageXml, 'filter', true);
//
//				// Also, unset the active language filter so the search tools is not open by default with this filter.
//				unset($this->activeFilters['language']);
//
//				// One last changes needed is to change the category filter to just show categories with All language or with the forced language.
//				$this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
//			}
//		}
//
//		parent::display($tpl);

        $this->items = $this->get('Items');
        JToolbarHelper::title("Danh sách sinh viên");
        return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 * @throws  Exception
	 */
	protected function addToolbar()
	{
		$canDo = ContentHelper::getActions('com_CRUDs',
			'category',
			$this->state->get('filter.category_id'));
		$user  = Factory::getApplication()->getIdentity();

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('COM_CRUDS_MANAGER_CRUDS'),
			'address CRUD');

		if ($canDo->get('core.create')
			|| count($user->getAuthorisedCategories('com_CRUDs',
				'core.create')) > 0)
		{
			$toolbar->addNew('CRUD.add');
		}

		if ($canDo->get('core.edit.state'))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fa fa-globe')
				->buttonClass('btn btn-info')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			$childBar->publish('CRUDs.publish')->listCheck(true);

			$childBar->unpublish('CRUDs.unpublish')->listCheck(true);

			$childBar->archive('CRUDs.archive')->listCheck(true);

			if ($user->authorise('core.admin'))
			{
				$childBar->checkin('CRUDs.checkin')->listCheck(true);
			}

			if ($this->state->get('filter.published') != -2)
			{
				$childBar->trash('CRUDs.trash')->listCheck(true);
			}
		}

		$toolbar->popupButton('batch')
			->text('JTOOLBAR_BATCH')
			->selector('collapseModal')
			->listCheck(true);

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			$toolbar->delete('CRUDs.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}

		if ($user->authorise('core.admin', 'com_CRUDs')
			|| $user->authorise('core.options',
				'com_CRUDs'))
		{
			$toolbar->preferences('com_CRUDs');
		}

		ToolbarHelper::divider();
		ToolbarHelper::help('', false, 'http://joomla.org');

		HTMLHelper::_('sidebar.setAction', 'index.php?option=com_CRUDs');
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   1.0.0
	 */
	protected function getSortFields()
	{
		return [
			'a.ordering'     => Text::_('JGRID_HEADING_ORDERING'),
			'a.published'    => Text::_('JSTATUS'),
			'a.name'         => Text::_('JGLOBAL_TITLE'),
			'category_title' => Text::_('JCATEGORY'),
			'a.access'       => Text::_('JGRID_HEADING_ACCESS'),
			'a.language'     => Text::_('JGRID_HEADING_LANGUAGE'),
			'a.id'           => Text::_('JGRID_HEADING_ID'),
		];
	}
}
