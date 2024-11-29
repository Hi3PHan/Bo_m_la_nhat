<?php

namespace Hi3PHan\Component\QLSV\Administrator\Field;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Database\DatabaseInterface;

class StudentField extends ListField
{
    public function getOptions()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
                ->select('Masv, Tensv')
                ->from('#__qlsv_sinhvien')
                ->order('Masv');

        $db->setQuery($query);

        $students = $db->loadAssocList('Masv' ,'Tensv');
        $options = parent::getOptions();
        foreach ($students as $id=>$code){
            $options[] = HTMLHelper::_('select.option', $id , $code);
        }
        return $options;
    }
}