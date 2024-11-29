<?php
namespace Hi3PHan\Component\QLSV\Administrator\Model;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

defined('_JEXEC') or die();

class StudentModel extends AdminModel {
	public function getForm($data = [], $loadData = true)
	{
		$form = $this->loadForm('com_qlsv.student','student', array('control'=>'jform','load_data'=>$loadData));
		return $form;
	}
	public function loadFormData()
	{
        // ktra phiên
        $app = Factory::getApplication();
        $data = $app->getUserState('com_qlsv.edit.student.data', array());

        if (empty($data))
        {
            $data = $this->getItem();

            // Pre-select some filters (Status, Category, Language, Access) in edit form if those have been selected in Article Manager: Articles
        }

        $this->preprocessData('com_qlsv.student', $data);

        return $data;
	}
    public function getTable($name = 'Students', $prefix = 'Table', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }
}
