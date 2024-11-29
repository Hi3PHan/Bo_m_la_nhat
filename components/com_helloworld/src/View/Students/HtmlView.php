<?php

namespace Joomla\Component\HelloWorld\Administrator\View\Students;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use JToolbarHelper;

/**
 * @package     Joomla.Administrator
 * @subpackage  com_helloworld
 *
 * @copyright   Copyright (C) 2020 John Smith. All rights reserved.
 * @license     GNU General Public License version 3; see LICENSE
 */

/**
 * Main "Hello World" Admin View
 */
class HtmlView extends BaseHtmlView {

    protected $items;
    protected $pagination;
    protected $students;
    protected $state;
    /**
     * Display the main "Hello World" view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     * @return  void
     */
    function display($tpl = null) {

//        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->students   = $this->get('Items');
        $this->state      = $this->get('State');


//        Đoạn mã này thường được sử dụng trong các view hoặc controller của Joomla
//        để kiểm tra xem có lỗi gì xảy ra trong quá trình lấy dữ liệu từ model hay không.
//        Nếu có lỗi, thay vì tiếp tục hiển thị trang với dữ liệu không đầy đủ hoặc không chính xác,
//        đoạn mã này sẽ dừng thực hiện và hiển thị thông báo lỗi.
        if (count($errors = $this->get('Errors')))
        {
            throw new GenericDataException(implode("\n", $errors), 500);
        }
        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        JToolbarHelper::title("Danh sách sinh viên");
        JToolbarHelper::addNew("CRUDStudent.add","Thêm một sinh viên");
        JToolbarHelper::editList("CRUDStudent.edit","Sửa một sinh viên");
        ToolbarHelper::deleteList("Bạn có chắc là muốn xóa các sinh viên đã chọn?",'CRUDStudent.delete','Xóa');
    }

}