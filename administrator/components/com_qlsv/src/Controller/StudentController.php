<?php
namespace Hi3PHan\Component\QLSV\Administrator\Controller;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use JText;

defined('_JEXEC') or die();

class StudentController extends  FormController {
//    protected $view_list = 'Students';

//    public function save($key = null, $urlVar = null)
//    {
////        $data = $this->input->post->get('jform',[],'array');
////        if(empty($data['id'])){
////            $this->setRedirect(Route::_('index.php?option=com_helloworld&view=students', false), JText::_('Lưu thành công'));
////        } else {
////            $this->setMessage(JText::_('Cập nhật bản ghi với ID: ' . $data['id']));
////        }
//
//
//        // Gọi phương thức lưu từ FormController
//        $result = parent::save($key, $urlVar);
//
//        // Nếu lưu thành công, điều hướng về danh sách sinh viên
//        if ($result) {
//            $this->setRedirect(Route::_('index.php?option=com_qlsv&view=students', false), JText::_('Lưu thành công ' )  );
//        } else {
//            // Nếu lưu thất bại, điều hướng về form chỉnh sửa
//            $this->setRedirect(Route::_('index.php?option=com_qlsv&view=student&layout=edit', false), JText::_('COM_STUDENTS_SAVE_FAIL'), 'error');
//        }
//
//        return $result;
//    }

    public function delete()
    {
        // Check for request forgeries
        $this->checkToken();

        // Get items to remove from the request.
        $cid = $this->input->get('cid', array(), 'array');

        // Get the model.
        $model = $this->getModel();

        // Remove the items.
        $model->delete($cid);
        $this->setRedirect(\JRoute::_('index.php?option=com_qlsv'));
    }

}

