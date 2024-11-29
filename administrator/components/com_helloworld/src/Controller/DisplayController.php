<?php
namespace Joomla\Component\HelloWorld\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

class DisplayController extends BaseController
{
    /**
     * The default view.
     *
     * @var    string
     * @since  1.6
     */
    protected $default_view = 'Students';

    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
     *
     * @return  static  This object to support chaining.
     *
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = [])
    {
        $view = $this->input->getCmd('view', 'students'); // 'students' là view mặc định
        //Dòng này dùng để lấy tên của view từ yêu cầu (request).
//        'view': Tên tham số cần lấy.
//          'students': Đây là giá trị mặc định được sử dụng nếu không có tham số view trong yêu cầu.
//              Điều này có nghĩa rằng nếu không có view cụ thể nào được yêu cầu, Joomla sẽ mặc định hiển thị view students.
        $this->input->set('view', $view);
//        Dòng này thiết lập lại tên view trong đầu vào (input) để đảm bảo view chính xác được sử dụng khi hiển thị.
//           Nó giúp đảm bảo rằng tên view sẽ được sử dụng bởi các thành phần khác trong quá trình xử lý.
        parent::display($cachable, $urlparams);
//        Gọi phương thức display() của lớp cha (BaseController).
//          Phương thức này thực hiện việc xử lý chính để hiển thị view, bao gồm tải và xuất ra HTML của view.
//              $cachable và $urlparams được truyền vào lớp cha để xử lý, giúp quản lý việc cache và truyền tham số cho URL.

    }


}
