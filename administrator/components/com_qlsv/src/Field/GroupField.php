<?php
namespace Actvn\Component\Tta\Administrator\Field;
defined('_JEXEC') or die();

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

class GroupField extends ListField
{
	public function getOptions()
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('id, code')
			->from('#__tta_groups')
			->order('code DESC');
		$db->setQuery($query);
		$groups = $db->loadAssocList('id','code');

		$options = parent::getOptions();
		foreach ($groups as $id=>$code)
		{
			$options[] = HTMLHelper::_('select.option', $id, $code);
		}
		return $options;
	}

}