<?php
namespace Hi3PHan\Component\QLSV\Administrator\Field;
defined('_JEXEC') or die();

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

class SubjectField extends ListField
{
	public function getOptions()
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true)
			->select('Mamh, Tenmh')
			->from('#__qlsv_monhoc')
            ->order('Tenmh ASC');
		$db->setQuery($query);
		$groups = $db->loadAssocList('Mamh','Tenmh');

		$options = parent::getOptions();
		foreach ($groups as $id=>$code)
		{
			$options[] = HTMLHelper::_('select.option', $id, $code);
		}
		return $options;
	}

}