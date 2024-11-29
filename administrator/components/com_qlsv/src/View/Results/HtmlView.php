<?php
namespace Hi3Phan\Component\QLSV\Administrator\View\Results;

defined('_JEXEC') or die();

use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;


class HtmlView extends BaseHtmlView {
    protected $items;
    protected $pagination;
    public $filterForm;

    function display($tpl = null)
    {
        $this->items      = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->filterForm = $this->get('FilterForm');

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

    protected function addToolBar()
    {
        ToolbarHelper::title("Danh sách Kết quả");
        ToolbarHelper::addNew('Result.add');
        ToolbarHelper::editList('Result.edit');
        ToolbarHelper::deleteList("Bạn có chắc chắn muôn xóa kết quả", 'Result.delete');
    }
}
