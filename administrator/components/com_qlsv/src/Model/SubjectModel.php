<?php

namespace Hi3PHan\Component\QLSV\Administrator\Model;
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\ListModel;

class SubjectModel extends AdminModel
{

    public function getForm($data = [], $loadData = true){

        $form = $this->loadForm('com_qlsv.subject','subject', array('control'=>'jform','load_data'=>$loadData));
        if (empty($form)) {
        Factory::getApplication()->enqueueMessage('Không thể tải form. Vui lòng kiểm tra file XML hoặc cách khai báo.', 'error');
        return false;
    }

        return $form;
    }
    public function loadFormData()
    {
        // ktra phiên
        $app = Factory::getApplication();
        $data = $app->getUserState('com_qlsv.edit.subject.data', array());

        if (empty($data))
        {
            $data = $this->getItem();

            // Pre-select some filters (Status, Category, Language, Access) in edit form if those have been selected in Article Manager: Articles
        }

        $this->preprocessData('com_qlsv.subject', $data);

        return $data;
    }
    public function getTable($name = 'Subjects', $prefix = 'Table', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

}