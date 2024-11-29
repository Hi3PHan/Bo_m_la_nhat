<?php

namespace Joomla\Component\HelloWorld\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Model\FormModel;
use RuntimeException;


class CRUDStudentModel extends AdminModel
{

    protected $table_name = '#__students';

    /**
     * Lấy form dữ liệu từ file XML
     * @param array \$data Dữ liệu cần thiết lập trên form
     * @param bool \$loadData Chỉ định có tải dữ liệu hay không
     * @return Form|false Form hoặc false nếu không thể tạo
     */
    public function getForm($data = array(), $loadData = true)
{
//    // Sử dụng FormFactoryInterface để tạo form
//    $formFactory = Factory::getContainer()->get(FormFactoryInterface::class);
//
//    // Đảm bảo rằng tham số thứ hai là một mảng options
//    $options = array('control' => 'jform', 'load_data' => $loadData);
//    $form = $formFactory->createForm('com_helloworld.CRUD', 'C:\xampp\htdocs\joomla4.8\administrator\components\com_helloworld\Form\CRUD.xml', $options);
//
//    if (empty($form)) {
//        return false;
//    }
//
//    return $form;

        // Get the form.
        $form = $this->loadForm('com_helloworld', 'C:\xampp\htdocs\joomla4.8\administrator\components\com_helloworld\Form\CRUDStudent.xml', array('control' => 'jform', 'load_data' => $loadData));
    // cần phải viết lại đường dẫn suorce(tham số thứ 2)
        if (empty($form))
        {
            return false;
        }

        return $form;
}

    public function delete(&$primaryKeys)
    {
        if(empty($primaryKeys) )
        {
            Factory::getApplication()->enqueueMessage("kh cos sinh vien");
        }
        $table = $this->getTable('CRUDStudent', 'Table');
        foreach ($primaryKeys as $pk)
        {
            $table->delete($pk);
        }

        Factory::getApplication()->enqueueMessage(
//            'Có '.count($primaryKeys).
            ' sinh viên đã bị xóa!',
            'message'
        );
    }

    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $app = Factory::getApplication();
        $data = $app->getUserState('com_helloworld.edit.CRUDStudent.data', array());

        if (empty($data))
        {
            $data = $this->getItem();

            // Pre-select some filters (Status, Category, Language, Access) in edit form if those have been selected in Article Manager: Articles
        }

        $this->preprocessData('com_helloworld.CRUDStudent', $data);

        return $data;
    }

}